<?php

/**
 * Class ErrorController
 *
 * Please note:
 * Don't use the same name for class and method, as this might trigger an (unintended) __construct of the class.
 * This is really weird behaviour, but documented here: http://php.net/manual/en/language.oop5.decon.php
 *
 */

namespace Canciones\Controller;

class ErrorController
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
    }

    /**
     * PAGE: index
     * This method handles the error page that will be shown when a page is not found
     */
    public function index()
    {
        if (isset($_SESSION['errors'])) {
            $this->twig->addGlobal('pagetitle', 'Error');
            echo $this->twig->render('error/index.twig', ['errors' => $_SESSION['errors']]);
        } else {
            $this->twig->addGlobal('pagetitle', 'Error');
            echo $this->twig->render('error/index.twig');
        }
        $_SESSION['errors'] = array();
    }
}
