<?php

/**
 * Streams save memory
 */

require __DIR__.'/../vendor/autoload.php';

$memory = memory_get_usage();
echo file_get_contents(__DIR__.'/../data/vegetables.txt')."\n";
echo "memory used : ".((memory_get_peak_usage() - $memory)>>10)." kb \n";


//
//$stream = fopen(__DIR__.'/../data/vegetables.txt', 'r');
//$memory = memory_get_usage();
//
//while ($data = fread($stream, 512)) {
//    echo $data;
//}
//
//fclose($stream);
//
//echo "memory used : ".(memory_get_peak_usage() - $memory)." b\n";



//$stream = fopen(__DIR__.'/../data/vegetables.txt', 'r');
//
//debug($stream);
//
//$memory = memory_get_usage();
//
//fseek($stream, 2<<20);
//
//echo fread($stream, 2048)."\n";
//
//fclose($stream);
//
//echo "memory used : ".(memory_get_peak_usage() - $memory)." b\n";


