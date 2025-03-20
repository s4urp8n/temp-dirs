<?php

$composerDirectory = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor') . DIRECTORY_SEPARATOR;
$composerFile = $composerDirectory . 'autoload.php';
require_once($composerFile);
