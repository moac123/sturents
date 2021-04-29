<?php
// DIC configuration

/** @var Pimple\Container $container */

use Slim\Middleware\JwtAuthentication;
use SturentsTest\Exceptions\ErrorHandler;
use SturentsTest\Middleware\OptionalAuth;
use League\Fractal\Manager;
use League\Fractal\Serializer\ArraySerializer;
use SturentsTest\Services\Auth\AuthServiceProvider;
use SturentsTest\Services\Database\EloquentServiceProvider;
use SturentsTest\Validation\Validator;

$container = $app->getContainer();


// Error Handler
$container['errorHandler'] = function ($c) {
    return new ErrorHandler($c['settings']['displayErrorDetails']);
};

// App Service Providers
$container->register(new EloquentServiceProvider());
$container->register(new AuthServiceProvider());

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];

    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));

    return $logger;
};

// Jwt Middleware
$container['jwt'] = function ($c) {

    $jws_settings = $c->get('settings')['jwt'];

    return new JwtAuthentication($jws_settings);
};

$container['optionalAuth'] = function ($c) {
  return new OptionalAuth($c);
};


// Request Validator
$container['validator'] = function ($c) {
    \Respect\Validation\Validator::with('\\SturentsTest\\Validation\\Rules');

    return new Validator();
};

// Fractal
$container['fractal'] = function ($c) {
    $manager = new Manager();
    $manager->setSerializer(new ArraySerializer());

    return $manager;
};
