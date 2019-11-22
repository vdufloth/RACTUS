<?php

    use Illuminate\Database\Capsule\Manager as Capsule;

    class Database {
        
        private static $host = null;
        private static $user = null;
        private static $password = null;
        private static $db_name = null;

        public static function setHost($host) {
            self::$host = $host;
        }

        public static function setUsername($user) {
            self::$user = $user;
        }

        public static function setPassword($password) {
            self::$password = $password;
        }

        public static function setDatabaseName($db_name) {
            self::$db_name = $db_name;
        }

        public static function connect() {
            $capsule = new Capsule;
            $capsule->addConnection([
                "driver" => "mysql",
                "host" => self::$host,
                "database" => self::$db_name,
                "username" => self::$user,
                "password" => self::$password,
                "charset" => "utf8",
            ]);
            $capsule->setAsGlobal();
            $capsule->bootEloquent();
        }

    }