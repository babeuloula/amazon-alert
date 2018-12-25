<?php

require_once 'vendor/autoload.php';

$app = new Silly\Application();

$app->command('', function () {

});

$app->run();