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
$app->register(new Silex\Provider\MonologServiceProvider(), 
    array(
        'monolog.logfile' => 'php://stdout',
    )
);

// Add another handler to deal with errors >= WARNING
$app['monolog'] = $app->share($app->extend('monolog', function($monolog, $app) {
    // remove default handler
    $monolog->popHandler();
    
    // create filterhandler and redirect log message to either stderr or stdout
    $errHandler = new StreamHandler('php://stderr', Monolog\Logger::WARNING);
    $filterErrHandler = new FilterHandler($errHandler, Monolog\Logger::WARNING, Monolog\Logger::EMERGENCY);
    $monolog->pushHandler($filterErrHandler);
    
    $infoHandler = new StreamHandler('php://stdout');
    $filterInfoHandler = new FilterHandler($infoHandler, Monolog\Logger::DEBUG, Monolog\Logger::NOTICE);
    $monolog->pushHandler($filterInfoHandler);
    
    return $monolog;
}));

$app->get('/', function() use($app) {
    return 'Hello World'; 
}); 

$app->get('/redis', function() use($app) { 
    return var_export($app['predis']->info(), true);
}); 


$app->get('/postgres', function() use($app) { 
    $dbconn = pg_connect("host=pgsql dbname=silex user=silex password=silex");
    $app['monolog']->addInfo(pg_connection_status($dbconn));
    return var_export(pg_version($dbconn), true); 
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

