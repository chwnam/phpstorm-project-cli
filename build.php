<?php
$pharFile = 'app.phar';

// clean up
if (file_exists($pharFile)) {
    unlink($pharFile);
}

$phar = new Phar($pharFile);
$phar->startBuffering();

$defaultStub = $phar->createDefaultStub('index.php');

$phar->buildFromDirectory(__DIR__ . '/src/');
$phar->buildFromDirectory(__DIR__ . '/vendor/');

// Create a custom stub to add the shebang
$stub = "#!/usr/bin/env php\n".$defaultStub;

// Add the stub
$phar->setStub($stub);
$phar->stopBuffering();
