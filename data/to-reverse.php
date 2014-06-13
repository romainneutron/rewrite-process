<?php

while (!feof(STDIN)) {
    fwrite(STDOUT, strrev(fread(STDIN, 1024)));
}