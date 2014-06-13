<?php

/**
 * Let's factor this in an object
 */

require __DIR__.'/../vendor/autoload.php';

class Process
{
    private $command;
    private $process;
    private $pipes = [];
    private $exitcode;

    public function __construct($command)
    {
        $this->command = $command;
    }

    public function run()
    {
        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $this->process = proc_open($this->command, $descriptorspec, $this->pipes);

        stream_set_blocking($this->pipes[0], 0);
        stream_set_blocking($this->pipes[1], 0);
        stream_set_blocking($this->pipes[2], 0);

        for (;;) {
            $status = proc_get_status($this->process);

            if ('-1' !== $status['exitcode']) {
                $this->exitcode = $status['exitcode'];
            }

            if (!$status['running']) {
                foreach ($this->pipes as $pipe) {
                    fclose($pipe);
                }
                $this->pipes = [];

                proc_close($this->process);

                break;
            }
            usleep(500000);
        }

        return $this->exitcode;
    }
}

//$process = new Process('php -r "syntax error"');
$process = new Process('php -r "echo \'Hello Romain !\'; sleep(1); echo \'Goodbye Romain !\';"');
debug($process->run());

