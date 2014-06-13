<?php

/**
 * PHP I/O streams
 */

$input = fopen('php://stdin', 'r');
$output = fopen('php://stdout', 'r+');
$err = fopen('php://stderr', 'r+');

while ($data = fread($input, 1024)) {
    fwrite($output, $data);
    fwrite($err, strrev($data));
}

fclose ($input);
fclose ($output);
fclose ($err);

echo "\n";
