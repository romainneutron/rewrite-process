<?php

while (!feof(STDIN)) {
    fwrite(STDOUT, strtoupper(fread(STDIN, 1024)));
}
