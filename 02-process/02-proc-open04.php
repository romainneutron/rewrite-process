<?php

/**
 * Let's get some pseudo async, real shit!
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

    public function start()
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
    }

    public function wait()
    {
        while ($this->isRunning()) {

        }

        foreach ($this->pipes as $pipe) {
            fclose($pipe);
        }
        $this->pipes = [];

        proc_close($this->process);

        return $this->exitcode;
    }

    public function isRunning()
    {
        $status = proc_get_status($this->process);

        if ('-1' !== $status['exitcode']) {
            $this->exitcode = $status['exitcode'];
        }

        $this->pollPipes();

        return $status['running'];
    }

    public function getOutput()
    {
        $this->pollPipes();

        return $this->output;
    }

    private function pollPipes()
    {
        $read = [$this->pipes[1], $this->pipes[2]];
        $write = null;
        $except = null;

        stream_select($read, $write, $except, 0, 50000);

        if (count($read) > 0) {
            foreach ($read as $r) {
                while ($data = fread($r, 1024)) {
                    $this->output .= $data;
                }
            }
        }
    }
}

$process = new Process('php -r "syntax error"');
$process = new Process('php -r "echo \'Hello Romain !\'; sleep(1); echo \'Goodbye Romain !\';"');

debug($process->start());

while ($process->isRunning()) {
    debug($process->getOutput());
}

