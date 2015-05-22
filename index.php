<?php 
require_once __DIR__.'/vendor/autoload.php'; 

$app = new Silex\Application(); 
$app['debug'] = true;

$app->register(new Predis\Silex\ClientServiceProvider(), [
    'predis.parameters' => 'tcp://redis:6379',
    'predis.options'    => [
        'prefix'  => 'silex:',
        'profile' => '3.0',
    ],
]);

$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => 'php://stdout',
));

$app->get('/', function() use($app) {
    return 'Hello World'; 
}); 

$app->get('/redis', function() use($app) { 
    return var_export($app['predis']->info(), true);
}); 

$app->get('/log/{type}', function($type) use($app) {
    switch ($type) {
        case "info":
            $app['monolog']->addInfo("Test is an info");
            break;
        case "debug":
            $app['monolog']->addDebug("Test is a debug");
            break;
        case "warning":
            $app['monolog']->addWarning("Test is a warning");
            break;
        case "error":
            $app['monolog']->addInfo("Test is an error");
            break;
            
    }
    
}); 
$app->run(); 

