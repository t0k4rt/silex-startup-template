<?php 
require_once __DIR__.'/vendor/autoload.php'; 

use Monolog\Handler\StreamHandler;

$app = new Silex\Application(); 
$app['debug'] = true;

$app->register(new Predis\Silex\ClientServiceProvider(), [
    'predis.parameters' => 'tcp://redis:6379',
    'predis.options'    => [
        'prefix'  => 'silex:',
        'profile' => '3.0',
    ],
]);

// default monolog
$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => 'php://stdout',
));

// Add another handler to deal with errors >= WARNING
$app['monolog'] = $app->share($app->extend('monolog', function($monolog, $app) {
    $handler = new StreamHandler('php://stderr', Monolog\Logger::WARNING);
    $monolog->pushHandler($handler);
    return $monolog;
}));

$app->get('/', function() use($app) {
    return 'Hello World'; 
}); 

$app->get('/redis', function() use($app) { 
    return var_export($app['predis']->info(), true);
}); 

$app->get('/log/{type}', function($type) use($app) {
    switch ($type) {
        case "info":
            $app['monolog']->addInfo("This is an info");
            break;
        case "debug":
            $app['monolog']->addDebug("This is a debug");
            break;
        case "warning":
            $app['monolog']->addWarning("This is a warning");
            break;
        case "error":
            $app['monolog']->addError("This is an error");
            break;
    }
    return 'Sent a log to std';
}); 
$app->run(); 

