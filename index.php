<?php 
require_once __DIR__.'/../vendor/autoload.php'; 

$app = new Silex\Application(); 

$app->register(new Predis\Silex\ClientServiceProvider(), [
    'predis.parameters' => 'tcp://redis:6379',
    'predis.options'    => [
        'prefix'  => 'silex:',
        'profile' => '3.0',
    ],
]);

$app->get('/', function() use($app) { 
    return 'Hello World'; 
}); 

$app->get('/redis', function() use($app) { 
    return var_export($app['predis']->info(), true);
}); 

$app->run(); 

