<?php

    class DatabaseControl{

        public function __construct() {
            $this->auto_increment = 1;
            $this->clients = [];
            $this->clients_auto_increments = null;
        }

        public function init() {
            if(count($this->clients) > 0) {
                foreach ($this->clients as $key => $client) {
                    $host = $client->host;
                    $user = $client->user;
                    $pass = $client->password;
                    $database_name = $client->db_name;

                    $mysqli = mysqli_init();
                    $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 1);
                    @$mysqli->real_connect($host, $user, $pass);
                    if(mysqli_connect_error() == null) {
                        $sql_db = "CREATE DATABASE IF NOT EXISTS $database_name;";
                        $mysqli->query($sql_db);
                        $mysqli->select_db($database_name);

                        $sql_table = "CREATE TABLE IF NOT EXISTS tb_meter_data (
                            id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                            temperature DOUBLE(16,2) NOT NULL,
                            humidity DOUBLE(16,2) NOT NULL,
                            read_at DATETIME,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                            )";
                        $mysqli->query($sql_table);
                        $mysqli->close();
                    }
                }
            }
        }

        public function addClient($data) {
            $this->clients[] = $data;
            return $this;
        }

        public function getClients() {
            return $this->clients;
        }

        public function setAutoIncrement($ai) {
            $this->auto_increment = $ai;
            return $this;
        }

        public function getAutoIncrement() {
            return $this->auto_increment;
        }

        public function incrementAI() {
            $this->setAutoIncrement($this->getAutoIncrement() + 1);
            return $this;
        }

        public function setClientsAutoIncrement($ais) {
            $this->clients_auto_increments = $ais;
            return $this;
        }

        public function getClientsAutoIncrement() {
            return $this->clients_auto_increments;
        }

        public function checkEquility($array, $key) {
            $unique_array = [];
            foreach($array as $element) {
                $hash = $element[$key];
                $unique_array[$hash] = $element;
            }
            return count($unique_array) === 1;
        }

        public function getBiggestClientsAI() {
            $ais = [];
            
            $clients = $this->getClients();
            foreach ($clients as $key => $client) {
                $host = $client->host;
                $user = $client->user;
                $pass = $client->password;
                $database_name = $client->db_name;

                $mysqli = mysqli_init();
                $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 1);
                @$mysqli->real_connect($host, $user, $pass, $client->db_name);

                if(mysqli_connect_error() == null) {
                    $sql_ai = "SELECT MAX(id) as ai FROM tb_meter_data";
                    $handle = $mysqli->query($sql_ai);
                    $result = $handle->fetch_assoc();

                    $ais[$key] = [
                        "auto_increment" => ($result["ai"] != ""? $result["ai"]: 0),
                        "diff_to_bigger" => 0
                    ];

                    $mysqli->close();
                }
            }

            if(count($ais) > 0 && !$this->checkEquility($ais, "auto_increment")) {
                uasort($ais, function($a, $b) {
                    return $a["auto_increment"] < $b["auto_increment"];
                });

                $biggest = $ais[key($ais)]["auto_increment"];
                $this->setAutoIncrement($biggest);
                foreach ($ais as $key => &$value) {
                    $value["diff_to_bigger"] = $biggest - $value["auto_increment"];
                }

                $this->setClientsAutoIncrement($ais);
                $this->makeEquality();
            }
        }

        public function makeEquality() {
            $clients = $this->getClients();
            $auto_increments = $this->getClientsAutoIncrement();

            $biggest_client = $clients[key($auto_increments)];

            foreach ($auto_increments as $key => $ai) {
                if($ai["diff_to_bigger"] > 0) {
                    
                    $sql_last_rows = "select * from tb_meter_data order by id desc limit {$ai["diff_to_bigger"]};";
                    
                    $client = $clients[$key];

                    Database::setHost($biggest_client->host);
                    Database::setUsername($biggest_client->user);
                    Database::setPassword($biggest_client->password);
                    Database::setDatabaseName($biggest_client->db_name);
                    Database::connect();

                    $data = MeterData::orderBy("id", "DESC")->limit($ai["diff_to_bigger"])->get()->toArray();
                    $data = array_reverse($data);

                    $mysqli = mysqli_init();
                    $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 1);
                    @$mysqli->real_connect($client->host, $client->user, $client->password, $client->db_name);

                    foreach ($data as $key => $register) {
                        $sql = "INSERT INTO tb_meter_data (id, temperature, humidity, read_at, created_at, updated_at) VALUES (" . $register["id"] . ", " . $register["temperature"] . ", " . $register["humidity"] . ", '" . $register["read_at"] . "', '" . $register["created_at"] . "', '" . $register["updated_at"] . "')";
                        $mysqli->query($sql);
                    }
                    $mysqli->close();
                }
            }
        }

        public function getBestClient() {
            $clients = $this->getClients();
            $reacheble_clients = [];
            
            foreach($clients as $client) {
                @$conn = new mysqli($client->host, $client->user, $client->password, $client->db_name);
                $check = (mysqli_connect_error() === null);
                if($check) {
                    $reacheble_clients[] = $client;
                }
            }

            if(count($reacheble_clients) == 0) {
                return null;
            } else {
                $biggest_client = null;
                $biggest_ai = -1;
                foreach($reacheble_clients as $rc) {
                    $mysqli = new mysqli($rc->host, $rc->user, $rc->password, $rc->db_name);

                    $sql_ai = "SELECT MAX(id) as ai FROM tb_meter_data";
                    $handle = $mysqli->query($sql_ai);
                    $result = $handle->fetch_assoc();

                    if($result["ai"] > $biggest_ai) {
                        $biggest_ai = $result["ai"];
                        $biggest_client = $rc;
                    }

                    $mysqli->close();
                }

                return $biggest_client;
            }
        }
        
    }