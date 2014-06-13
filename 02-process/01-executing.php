<?php

/**
 * PHP functions that execute commands
 */

$command = 'ls';


$output = `$command`;

$output = shell_exec($command);


$lastLine = exec($command, $output, $exitcode);



passthru($command, $exitcode);

$lastLine = system($command, $exitcode);

 