<?php

/**
 * Let's get the benefit of stream to read the output
 */

require __DIR__.'/../vendor/autoload.php';

class Process
{
    private $command;
    private $process;
    private $pipes = [];
    private $exitcode;
    private $output;

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

            $read = [$this->pipes[1], $this->pipes[2]];
            $write = null;
            $except = null;

            stream_select($read, $write, $except, 0, 50000);

            if (count($read) > 0) {
                foreach ($read as $r) {
                    if ($data = fread($r, 1024)) {
                        $this->output .= $data;
                    }
                }
            }

            if (!$status['running']) {
                foreach ($this->pipes as $pipe) {
                    fclose($pipe);
                }
                $this->pipes = [];

                proc_close($this->process);

                break;
            }
        }

        return $this->exitcode;
    }

    public function getOutput()
    {
        return $this->output;
    }
}

$process = new Process('php -r "syntax error"');
$process = new Process('php -r "echo \'Hello Romain !\'; sleep(1); echo \'Goodbye Romain !\';"');
debug($process->run());
debug($process->getOutput());

