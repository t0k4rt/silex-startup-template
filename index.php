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
    $app['monolog']->addInfo("log test");
    return 'Hello World'; 
}); 

$app->get('/redis', function() use($app) { 
    return var_export($app['predis']->info(), true);
}); 

$app->get('/err', function() use($app) { 
    return $coucou;
}); 
$app->run(); 

