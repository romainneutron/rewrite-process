<?php

$input = fread(STDIN, 1024);

echo sprintf("Hello %s !\n", $input);

 