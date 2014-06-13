<?php

/**
 * Streams have filters
 */

require __DIR__.'/../vendor/autoload.php';

$stream = fopen(__DIR__.'/../data/vegetables.txt', 'r');

debug($stream);

$stdout = fopen('php://stdout', 'w');
stream_filter_append($stream, 'string.tolower');

echo fread($stream, 8)."\n";

fclose($stream);

 