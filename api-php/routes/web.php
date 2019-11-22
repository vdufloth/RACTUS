<?php

    use Illuminate\Container\Container;
    use Illuminate\Events\Dispatcher;
    use Illuminate\Http\Request;
    use Illuminate\Routing\Redirector;
    use Illuminate\Routing\Router;
    use Illuminate\Routing\UrlGenerator;

    $container = new Container;

    $request = Request::capture();
    $container->instance('Illuminate\Http\Request', $request);
    $events = new Dispatcher($container);
    $router = new Router($events, $container);

    include 'api/routes.php';

    $redirect = new Redirector(new UrlGenerator($router->getRoutes(), $request));
    $response = $router->dispatch($request);
    $response->send();