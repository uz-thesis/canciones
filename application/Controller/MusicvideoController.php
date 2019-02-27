<?php

/**
 * Class MusicvideoController
 *
 * If you want, you can use multiple Models or Controllers.
 *
 * Please note:
 * Don't use the same name for class and method, as this might trigger an (unintended) __construct of the class.
 * This is really weird behaviour, but documented here: http://php.net/manual/en/language.oop5.decon.php
 *
 */

namespace Canciones\Controller;

use Canciones\Model\Musicvideo;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

class MusicvideoController
{
    /**
     * Constructor function which initiates the Twig templating engine
     */
    public function __construct()
    {
        @session_start();

        $loader = new \Twig\Loader\FilesystemLoader(APP . 'view');
        $this->twig = new \Twig\Environment($loader);
        $this->twig->addGlobal('session', $_SESSION);

        if(!isset($_SESSION['errors'])) {
           $_SESSION['errors'] = array();
        }

        if(!isset($_SESSION['logged_in'])) {
           $_SESSION['logged_in'] = FALSE;
        }

        if ($_SESSION['logged_in'] === FALSE) {
            header('location: ' . URL . 'auth/loginpage');
        }
    }

    /**
     * PAGE: index
     * This method handles what happens when you move to http://yourproject/musicvideo/index
     */
    public function index()
    {
        // Instance new Model (MusicVideo)
        $MusicvideoModel = new Musicvideo();
        // getting all music videos and amount of music videos
        $musicvideos = $MusicvideoModel->getAllMusicVideos();

        // Connect to Firebase storage to get URL
        $serviceAccount = ServiceAccount::fromJsonFile(APP . 'config/' . FIREBASE_AUTH_JSON);
        $firebase = (new Factory)->withServiceAccount($serviceAccount)->create();
        $storage = $firebase->getStorage();
        $bucket = $storage->getBucket();

        if (!empty($musicvideos)) {
            $protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';
            foreach ($musicvideos as $key => &$musicvideo) {
                $object = $bucket->object($musicvideo['musicVideoSrc']);
                $expiresAt = new \DateTime('tomorrow');
                $musicvideo['webpath'] = $object->signedUrl($expiresAt);
           }
        }

        $amount = $MusicvideoModel->getAmountOfMusicVideos();

       // load views. within the views we can echo out $musicvideos easily
       $this->twig->addGlobal('pagetitle', 'Music videos');
       $customhead = '<script src="https://www.gstatic.com/firebasejs/5.8.4/firebase.js"></script>';
       echo $this->twig->render('musicvideo/index.twig', ['customhead' => $customhead, 'musicvideos' => $musicvideos, 'amount' => $amount]);
    }

    /**
     * PAGE: getoneasjson
     * This method handles what happens when you move to http://yourproject/musicvideo/getoneasjson
     */
     public function getOneAsJSON($id)
     {
         if (isset($id)) {
             // Instance new Model (MusicVideo)
             $MusicvideoModel = new Musicvideo();
             // getting all music videos and amount of music videos

             if (!$musicvideo = $MusicvideoModel->getMusicVideo($id)) {
                 header("HTTP/1.0 500 Internal Server Error");
                 die('API Error: Invalid music video ID.');
             }

             $protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';
             $musicvideo['webpath'] = $protocol.$_SERVER['HTTP_HOST'].URL_SUB_FOLDER.$musicvideo['musicVideoSrc'];

            echo json_encode($musicvideo);
        }
     }

    /**
     * PAGE: getallasjson
     * This method handles what happens when you move to http://yourproject/musicvideo/getallasjson
     */
    public function getAllAsJSON()
    {
        // Instance new Model (MusicVideo)
        $MusicvideoModel = new Musicvideo();
        // getting all music videos and amount of music videos
        $musicvideos = $MusicvideoModel->getAllMusicVideos();
        $protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';
        foreach ($musicvideos as &$musicvideo) {
            $musicvideo['webpath'] = $protocol.$_SERVER['HTTP_HOST'].URL_SUB_FOLDER.$musicvideo['musicVideoSrc'];
        }

       echo json_encode($musicvideos);
    }

    /**
     * ACTION: addMusicVideo
     * This method handles what happens when you move to http://yourproject/musicvideo/addmusicvideo
     * IMPORTANT: This is not a normal page, it's an ACTION. This is where the "add a music video" form on musicvideo/index
     * directs the user after the form submit. This method handles all the POST data from the form and then redirects
     * the user back to musicvideo/index via the last line: header(...)
     * This is an example of how to handle a POST request.
     */
    public function addMusicVideo()
    {
        // if we have POST data to create a new music video entry
        if (isset($_POST["name"])) {
            // Instance new Model (MusicVideo)
            $MusicvideoModel = new Musicvideo();

            // ID of the new music video (since Firebase doesn't auto-assign one)
            $id = bin2hex(openssl_random_pseudo_bytes(15));

            if (!empty($this->errors)) {
                $page = new \Canciones\Controller\ErrorController();
                $page->index();
            }

            // Process checkbox to boolean
            $is_live = isset($_POST["is_live"]) ? 1 : 0;

            // do addMusicVideo() in model/model.php
            $MusicvideoModel->addMusicVideo($id, $_POST["name"], $_POST["author"], $_POST["desc"], $_POST['src'], $is_live);
        }

        // where to go after music video has been added
        header('location: ' . URL . 'musicvideo/index');
    }

    /**
     * ACTION: deleteMusicVideo
     * This method handles what happens when you move to http://yourproject/musicvideo/deletemusicvideo
     * IMPORTANT: This is not a normal page, it's an ACTION. This is where the "delete a music video" button on musicvideo/index
     * directs the user after the click. This method handles all the data from the GET request (in the URL!) and then
     * redirects the user back to musicvideo/index via the last line: header(...)
     * This is an example of how to handle a GET request.
     * @param int $id ID of the to-delete music video
     */
    public function deleteMusicVideo($id)
    {
        // if we have an id of a music-video that should be deleted
        if (isset($id)) {
            // Instance new Model (MusicVideo)
            $MusicvideoModel = new Musicvideo();

            // Get name of Firebase video object to be deleted
            $src = $MusicvideoModel->getMusicVideo($id)['musicVideoSrc'];

            // Delete video object from bucket
            $serviceAccount = ServiceAccount::fromJsonFile(APP . 'config/' . FIREBASE_AUTH_JSON);
            $firebase = (new Factory)->withServiceAccount($serviceAccount)->create();
            $storage = $firebase->getStorage();
            $bucket = $storage->getBucket();

            $object = $bucket->object($src);
            $object->delete();

            // do deleteMusicVideo() in model/model.php
            $MusicvideoModel->deleteMusicVideo($id);
        }

        // where to go after music video has been deleted
        header('location: ' . URL . 'musicvideo/index');
    }

     /**
     * ACTION: editMusicVideo
     * This method handles what happens when you move to http://yourproject/musicvideo/editmusicvideo
     * @param int $id ID of the to-edit music video
     */
    public function editMusicVideo($id)
    {
        // if we have an id of a music video that should be edited
        if (isset($id)) {
            // Instance new Model (MusicVideo)
            $MusicvideoModel = new Musicvideo();
            // do getMusicVideo() in model/model.php
            $musicvideo = $MusicvideoModel->getMusicVideo($id);
            // Process live checkbox
            $is_live_checked = $musicvideo['musicVideoIsLive'] ? 'checked' : '';

            // If the music video wasn't found, then it would have returned false, and we need to display the error page
            if (!$musicvideo) {
                $page = new \Canciones\Controller\ErrorController();
                $_SESSION['errors'][] = 'Music video not found.';
                $page->index();
            } else {
                $musicvideo['musicVideoId'] = $id;
                $this->twig->addGlobal('pagetitle', 'Edit music video info (' . $musicvideo['musicVideoName'] . ')');
                echo $this->twig->render('musicvideo/edit.twig', ['musicvideo' => $musicvideo]);
            }
        } else {
            // redirect user to music videos index page (as we don't have an ID)
            header('location: ' . URL . 'musicvideo/index');
        }
    }

    /**
     * ACTION: updateMusicVideo
     * This method handles what happens when you move to http://yourproject/musicvideo/updatemusicvideo
     * IMPORTANT: This is not a normal page, it's an ACTION. This is where the "update a music video" form on musicvideo/edit
     * directs the user after the form submit. This method handles all the POST data from the form and then redirects
     * the user back to musicvideo/index via the last line: header(...)
     * This is an example of how to handle a POST request.
     */
    public function updateMusicVideo()
    {
        // if we have POST data to create a new music video entry
        if (isset($_POST["submit_update_musicvideo"])) {
            // Instance new Model (MusicVideo)
            $MusicvideoModel = new Musicvideo();

            // Process checkbox to boolean
            $is_live = isset($_POST["is_live"]) ? 1 : 0;

            // do updateMusicVideo() from model/model.php
            $MusicvideoModel->updateMusicVideo($_POST["id"], $_POST["name"], $_POST["author"], $_POST["desc"], $is_live);
        }

        // where to go after music video has been added
        header('location: ' . URL . 'musicvideo/index');
    }

    /**
     * AJAX-ACTION: ajaxGetStats
     * TODO documentation
     */
    public function ajaxGetStats()
    {
        // Instance new Model (MusicVideo)
        $MusicvideoModel = new Musicvideo();
        $amount = $MusicvideoModel->getAmountOfMusicVideos();

        // simply echo out something. A supersimple API would be possible by echoing JSON here
        echo $amount;
    }

}
