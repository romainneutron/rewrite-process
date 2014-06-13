<?php

/**
 * proc_open works with streams
 */

require __DIR__.'/../vendor/autoload.php';

$cmd = 'php -r "echo \'Hello Romain !\'; sleep(1); echo \'Goodbye Romain !\';"';

$descriptorspec = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
];

$process = proc_open($cmd, $descriptorspec, $pipes);

stream_set_blocking($pipes[0], 0);
stream_set_blocking($pipes[1], 0);
stream_set_blocking($pipes[2], 0);

for (;;) {
    $status = proc_get_status($process);

    debug ($status);

    if (!$status['running']) {
        foreach ($pipes as $pipe) {
            fclose($pipe);
        }
        $pipes = [];

        proc_close($process);

        debug($status['exitcode']);

        break;
    }
    usleep(500000);
}



