<?php

/**
 * Class AuthController
 *
 * Please note:
 * Don't use the same name for class and method, as this might trigger an (unintended) __construct of the class.
 * This is really weird behaviour, but documented here: http://php.net/manual/en/language.oop5.decon.php
 *
 */

namespace Canciones\Controller;

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

class AuthController
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

        if (!isset($_SESSION['errors'])) {
            $_SESSION['errors'] = array();
        }
    }

    /**
     * PAGE: loginform
     */
    public function loginpage()
    {
        if (!isset($_SESSION['logged_in'])) {
            $_SESSION['logged_in'] = FALSE;
        }
        if ($_SESSION['logged_in'] === FALSE) {
            // load views
            $this->twig->addGlobal('pagetitle', 'Log in');
            echo $this->twig->render('auth/login.twig', ['errors' => $_SESSION['errors']]);
            $_SESSION['errors'] = array();
        }
        if ($_SESSION['logged_in'] === TRUE) {
            header('location: ' . URL . '/');
        }
    }

    /**
     * ACTION: login
     */
    public function login()
    {
        if (@$_POST['user'] === ADMIN_USERNAME && @$_POST['pass'] === ADMIN_PASSWORD) {
            $_SESSION['logged_in'] = TRUE;
            header('location: ' . URL);
        } else if (!empty($_POST)) {
            $_SESSION['errors'][] = 'Wrong username or password.';
            header('location: ' . URL . 'auth/loginpage');
        } else {
            header('location: ' . URL . 'auth/loginpage');
        }
    }

    /**
     * ACTION: logout
     */
    public function logout()
    {
        if (session_destroy()) {
            session_start();
            $_SESSION['errors'][] = 'User successfully logged out.';
            header('location: ' . URL . 'auth/loginpage');
        }
    }

    /**
     * ACTION: createCustomToken
     */
    public function getCustomToken($uid)
    {
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === TRUE) {
            $serviceAccount = ServiceAccount::fromJsonFile(APP . 'config/' . FIREBASE_AUTH_JSON);
            $firebase = (new Factory)->withServiceAccount($serviceAccount)->create();
            $customToken = $firebase->getAuth()->createCustomToken($uid);

            echo $customToken;
        }
    }
}
