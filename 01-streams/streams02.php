<?php

/**
 * Streams have multiple wrappers
 */

require __DIR__.'/../vendor/autoload.php';

$stream = fopen('http://download.geonames.org/export/dump/allCountries.zip', 'r');

debug(stream_get_meta_data($stream));

echo fread($stream, 512)."\n";
fclose($stream);

 