<?php

namespace Canciones\Core;

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

class Model
{
    /**
     * @var null Firebase Real-Time Database Connection
     */
    public $rtdb = null;

    /**
     * Whenever model is created, open a database connection.
     */
    function __construct()
    {
        try {
            self::openFirebaseRTDBConnection();
        } catch (\PDOException $e) {
            exit('Database connection could not be established.');
        }
    }

    /**
     * Open the database connection with the credentials from application/config/config.php
     */
    private function openFirebaseRTDBConnection()
    {
        // Initiate a Firebase RTDB connection
        $serviceAccount = ServiceAccount::fromJsonFile(APP . 'config/' . FIREBASE_AUTH_JSON);
        $firebase = (new Factory)->withServiceAccount($serviceAccount)->create();
        $this->rtdb = $firebase->getDatabase();
    }
}
