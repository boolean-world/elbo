<?php

use Elbo\Library\Configuration;

$request_uri = rawurldecode(strtok($_SERVER['REQUEST_URI'], '?'));

if (substr($request_uri, 0, 8) === '/assets/') {
	return false;
}

if ($request_uri !== '/' && $request_uri[-1] === '/') {
	$request_uri = substr($request_uri, 0, -1);
}

require_once __DIR__.'/../vendor/autoload.php';

$container = require __DIR__.'/../app/services.php';
$routes = require __DIR__.'/../app/routes.php';

$config = $container->get(Configuration::class);

$dispatcher = FastRoute\cachedDispatcher($routes, [
	'cacheFile' => __DIR__.'/../data/cache/routes',
	'cacheDisabled' => ($config->get('environment.phase') !== 'production')
]);

$routeinfo = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], $request_uri);
if ($routeinfo[0] !== FastRoute\Dispatcher::FOUND) {
	$routeinfo[1] = 'NotFoundController';
	$routeinfo[2] = [];
}

$request = Symfony\Component\HttpFoundation\Request::createFromGlobals();
$controller_name = 'Elbo\\Controllers\\'.$routeinfo[1];
$controller = new $controller_name($request, $routeinfo[2], $container);

bootstrap_eloquent($config);

$controller->start()->send();
