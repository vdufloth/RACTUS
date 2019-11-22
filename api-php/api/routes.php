<?php

    use Illuminate\Routing\Router;

    require_once dirname(__FILE__) . '/../vendor/autoload.php';

    $controller = new MeterDataController();

    /** @var $router Router */
    $router->group(['prefix' => '/api'], function (Router $router) use($db_control, $controller) {
        $router->get('/data', function() use ($db_control, $controller) {
            $controller->all_data($db_control);
        });

        $router->post('/data', function() use ($db_control, $controller) {
            $controller->receive_data($db_control);
        });

        $router->get('/data/last', function() use ($db_control, $controller) {
            $controller->last_data($db_control);
        });

        $router->get('/data/day_average', function() use ($db_control, $controller) {
            $controller->day_average($db_control);
        });

        $router->get('/data/hour_average', function() use ($db_control, $controller) {
            $controller->hour_average($db_control);
        });
    });