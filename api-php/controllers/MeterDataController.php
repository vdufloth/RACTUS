<?php


    class MeterDataController{

        public function all_data($db_control) {
            $client = $db_control->getBestClient();

            if(!$client) {
                header('Content-Type: application/json');
                echo json_encode(array("success" => false, "data" => []));
                die();
            }

            Database::setHost($client->host);
            Database::setUsername($client->user);
            Database::setPassword($client->password);
            Database::setDatabaseName($client->db_name);
            Database::connect();

            $data = MeterData::orderBy("read_at", "DESC")->get();
            $data = json_decode(json_encode($data));

            $timestamp_fields = ["read_at", "created_at", "updated_at"];
            foreach($data as $d) {
                foreach($timestamp_fields as $tf) {
                    $original = $d->{$tf};
                    $formatted = DateTime::createFromFormat("Y-m-d H:i:s", $d->{$tf})->format("d/m/Y H:i:s");
                    unset($d->{$tf});
                    $d->{$tf} = array(
                        "original" => $original,
                        "formatted" => $formatted
                    );
                }
            }

            header('Content-Type: application/json');
            echo json_encode(array("success" => true, "data" => $data));
            die();
        }

        public function receive_data($db_control) {
            $db_control->getBiggestClientsAI();

            $server_data = file_get_contents("php://input");
            $server_data = json_decode($server_data);

            // Tenta gravar os dados em todos os bancos
            $clients = $db_control->getClients();

            foreach ($clients as $key => $client) {
                $mysqli = new mysqli($client->host, $client->user, $client->password, $client->db_name);
                $sql = "INSERT INTO tb_meter_data (temperature, humidity, read_at) VALUES (" . $server_data->temperature . ", " . $server_data->humidity . ", '" . $server_data->readtime . "')";
                if(mysqli_connect_error() == null) {
                    $mysqli->query($sql);
                    $mysqli->close();
                }
            }

            header('Content-Type: application/json');
            echo json_encode(array("success" => true, "message" => "Dados inseridos com sucesso."));
            die();
        }

        public function last_data($db_control) {
            $client = $db_control->getBestClient();

            if(!$client) {
                header('Content-Type: application/json');
                echo json_encode(array("success" => false, "data" => []));
                die();
            }

            $limit = 1;
            if(isset($_GET["limit"])) {
                $limit = $_GET["limit"];
            }

            Database::setHost($client->host);
            Database::setUsername($client->user);
            Database::setPassword($client->password);
            Database::setDatabaseName($client->db_name);
            Database::connect();

            $data = MeterData::orderBy("read_at", "DESC")->limit($limit)->get();
            $data = json_decode(json_encode($data));

            $timestamp_fields = ["read_at", "created_at", "updated_at"];
            foreach($data as $d) {
                foreach($timestamp_fields as $tf) {
                    $original = $d->{$tf};
                    $formatted = DateTime::createFromFormat("Y-m-d H:i:s", $d->{$tf})->format("d/m/Y H:i:s");
                    unset($d->{$tf});
                    $d->{$tf} = array(
                        "original" => $original,
                        "formatted" => $formatted
                    );
                }
            }

            if($limit == 1) $data = $data[0];

            header('Content-Type: application/json');
            echo json_encode(array("data" => $data));
            die();
        }

        public function day_average($db_control) {
            $client = $db_control->getBestClient();

            if(!$client) {
                header('Content-Type: application/json');
                echo json_encode(array("success" => false, "data" => []));
                die();
            }

            $month = date("m");
            if(isset($_GET["month"])) {
                $month = $_GET["month"];
            }

            Database::setHost($client->host);
            Database::setUsername($client->user);
            Database::setPassword($client->password);
            Database::setDatabaseName($client->db_name);
            Database::connect();

            $data = MeterData::orderBy("read_at", "DESC")->whereMonth('read_at', $month)->get();
            $data = json_decode(json_encode($data));


            $february = (date('L')? 29: 28);
            $months_days = [31, $february, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

            
            $return = [];
            for($i=1; $i<=$months_days[$month-1]; $i++) {
                $return[$i] = [
                    "temperature" => [],
                    "humidity" => []
                ];
            }

            foreach ($data as $key => $d) {
                $day = DateTime::createFromFormat("Y-m-d H:i:s", $d->read_at)->format("d");
                $day = ltrim($day, "0");

                $return[$day]["temperature"][] = $d->temperature;
                $return[$day]["humidity"][] = $d->humidity;
            }

            for($i=1; $i<=$months_days[$month-1]; $i++) {
                if(count($return[$i]["temperature"]) > 0 && count($return[$i]["humidity"]) > 0) {
                    $return[$i]["temperature"] = max($return[$i]["temperature"]);
                    $return[$i]["humidity"] = max($return[$i]["humidity"]);
                } else {
                    $return[$i]["temperature"] = 0;
                    $return[$i]["humidity"] = 0;
                }
            }

            header('Content-Type: application/json');
            echo json_encode(array("data" => $return));
            die();
        }

        public function hour_average($db_control) {
            $client = $db_control->getBestClient();

            if(!$client) {
                header('Content-Type: application/json');
                echo json_encode(array("success" => false, "data" => []));
                die();
            }

            $date = date("Y-m-d");
            if(isset($_GET["date"])) {
                $date = $_GET["date"];
            }

            Database::setHost($client->host);
            Database::setUsername($client->user);
            Database::setPassword($client->password);
            Database::setDatabaseName($client->db_name);
            Database::connect();

            $data = MeterData::orderBy("read_at", "DESC")->whereDate('read_at', $date)->get();
            $data = json_decode(json_encode($data));

            $hour_range = [];
            for($i=0; $i<24; $i++) {
                $start = $i;
                $end = ($i + 1 == 24)? 0: $i + 1;

                $hour_range[] = [
                    "start" => $start,
                    "end" => $end,
                    "values" => [
                        "sum_temp" => 0,
                        "sum_humidity" => 0
                    ],
                    "counter" => 0
                ];
            }


            $average_hour = [];
            foreach ($data as $i => $d) {
                $hour = DateTime::createFromFormat("Y-m-d H:i:s", $d->read_at)->format("H");

                foreach ($hour_range as $j => &$hr) {
                    if($hour >= $hr["start"] && $hour < $hr["end"]) {
                        $hr["values"]["sum_temp"] += $d->temperature;
                        $hr["values"]["sum_humidity"] += $d->humidity;
                        $hr["counter"]++;
                    }
                }

                foreach ($hour_range as $key => &$_hr) {
                    if($_hr["counter"] > 0) {
                        $_hr["avg_temperature"] = round($_hr["values"]["sum_temp"] / $_hr["counter"], 2);
                        $_hr["avg_humidity"] = round($_hr["values"]["sum_humidity"] / $_hr["counter"], 2);
                    } else {
                        $_hr["avg_temperature"] = 0;
                        $_hr["avg_humidity"] = 0;
                    }
                }
            }

            foreach ($hour_range as $key => &$value) {
                unset($value["values"]);
                unset($value["counter"]);
            }

            header('Content-Type: application/json');
            echo json_encode(array("data" => $hour_range));
            die();
        }

    }