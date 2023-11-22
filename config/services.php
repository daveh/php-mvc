<?php

$container = new Framework\Container;

// Database example
$container->set(App\Database::class, function() {

    return new App\Database($_ENV["DB_HOST"], $_ENV["DB_NAME"], $_ENV["DB_USER"], $_ENV["DB_PASSWORD"]);

});

// Viewer example
$container->set(Framework\TemplateViewerInterface::class, function() {

    return new Framework\MVCTemplateViewer;

});

return $container;