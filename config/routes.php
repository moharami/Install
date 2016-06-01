<?php
use Cake\Routing\Router;

//Router::connect('/install', ['plugin' => 'Install', 'controller' => 'Install', 'action' => 'index']);
Router::connect('/database', ['plugin' => 'Install', 'controller' => 'Install', 'action' => 'database']);
Router::connect('/data', ['plugin' => 'Install', 'controller' => 'Install', 'action' => 'data']);
Router::connect('/adminuser', ['plugin' => 'Install', 'controller' => 'Install', 'action' => 'adminuser']);
Router::connect('/finish', ['plugin' => 'Install', 'controller' => 'Install', 'action' => 'finish']);

Router::plugin('Install', function ($routes) {
	$routes->fallbacks('InflectedRoute');
});

$request = Router::getRequest(true);
if (strpos($request->url, 'install') === false) {
	$url = ['plugin' => 'install', 'controller' => 'install'];
	Router::redirect('/*', $url, ['status' => 307]);
}

