<?php

/**
 * What about error output and callbacks?
 */

require __DIR__.'/../vendor/autoload.php';

class Process
{
    private $command;
    private $process;
    private $pipes = [];
    private $exitcode;
    private $output;
    private $errorOutput;
    private $callback;

    public function __construct($command)
    {
        $this->command = $command;
    }

    public function run($callback = null)
    {
        $this->start($callback);

        return $this->wait();
    }

    public function start($callback = null)
    {
        if (null === $callback) {
            $callback = function () {};
        }

        $this->callback = $callback;

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
        while ($this->isRunning()) {}

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

    public function getErrorOutput()
    {
        $this->pollPipes();

        return $this->errorOutput;
    }

    private function pollPipes()
    {
        $read = [$this->pipes[1], $this->pipes[2]];
        $write = null;
        $except = null;

        stream_select($read, $write, $except, 0, 50000);

        if (count($read) > 0) {
            foreach ($read as $r) {
                if ($data = fread($r, 1024)) {
                    $type = 1 === array_search($r, $this->pipes) ? 'OUT' : 'ERR';

                    call_user_func($this->callback, $type, $data);

                    if ('OUT' === $type) {
                        $this->output .= $data;
                    } else {
                        $this->errorOutput .= $data;
                    }
                }
            }
        }
    }
}

$process = new Process('php -r "fwrite(STDOUT, \'Hello Romain !\'); sleep(1); fwrite(STDERR, \'Goodbye Romain !\');"');

$process->run(function ($type, $data) { echo "$type : $data\n"; });
