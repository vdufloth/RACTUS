<?php

    // ini_set('display_errors', 1);
    // ini_set('display_startup_errors', 1);
    // error_reporting(E_ALL);

    require_once 'vendor/autoload.php';


    // Database control
    $client1 = new stdClass();
    $client1->host = "127.0.0.1";
    $client1->user = "root";
    $client1->password = "Gu01sa10ma06";
    $client1->db_name = "temperature_umidity_1";

    $client2 = new stdClass();
    $client2->host = "127.0.0.1";
    $client2->user = "root";
    $client2->password = "Gu01sa10ma06";
    $client2->db_name = "temperature_umidity_2";

    $client3 = new stdClass();
    $client3->host = "127.0.0.1";
    $client3->user = "root";
    $client3->password = "Gu01sa10ma06";
    $client3->db_name = "temperature_umidity_3";

    $client4 = new stdClass();
    $client4->host = "127.0.0.1";
    $client4->user = "root";
    $client4->password = "Gu01sa10ma06";
    $client4->db_name = "temperature_umidity_4";

    $client5 = new stdClass();
    $client5->host = "127.0.0.1";
    $client5->user = "root";
    $client5->password = "Gu01sa10ma06";
    $client5->db_name = "temperature_umidity_5";

    // $client6 = new stdClass();
    // $client6->host = "192.168.2.14";
    // $client6->user = "root";
    // $client6->password = "";
    // $client6->db_name = "temperature_umidity_6";

    // $client7 = new stdClass();
    // $client7->host = "192.168.1.164";
    // $client7->user = "root";
    // $client7->password = "";
    // $client7->db_name = "temperature_umidity_7";

    $db_control = new DatabaseControl();
    $db_control->addClient($client1);
    $db_control->addClient($client2);
    $db_control->addClient($client3);
    $db_control->addClient($client4);
    $db_control->addClient($client5);
    // $db_control->addClient($client6);
    // $db_control->addClient($client7);

    $db_control->init();


    include 'routes/web.php';

