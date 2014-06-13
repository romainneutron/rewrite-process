<?php

$started = true;

pcntl_signal(SIGTERM, function () use (&$started) {
    $started = false;
});

while ($started) {
    usleep(50000);
    pcntl_signal_dispatch();
    echo "looping\n";
}
