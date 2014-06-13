<?php

/**
 * I wanna write in this process
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
    private $pid;
    private $input;

    public function __construct($command, $input)
    {
        $this->input = $input;
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

        if (!$this->isRunning()) {
            throw new \RuntimeException('Unable to start process');
        }

        $inputOffset = 0;

        while ($inputOffset < strlen($this->input)) {
            $inputOffset += fwrite($this->pipes[0], substr($this->input, $inputOffset));
        }

        fclose($this->pipes[0]);
        unset($this->pipes[0]);
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

        if (null === $this->pid) {
            $this->pid = $status['pid'];
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

    public function getPid()
    {
        return $this->pid;
    }

    public function signal($signal)
    {
        proc_terminate($this->process, $signal);
    }

    private function pollPipes()
    {
        if (count($this->pipes) === 0) {
            return;
        }

        $read = $this->pipes;
        $write = null;
        $except = null;

        stream_select($read, $write, $except, 0, 50000);

        if (count($read) > 0) {
            foreach ($read as $r) {
                $type = array_search($r, $this->pipes);
                $prefix = 1 === $type ? 'OUT' : 'ERR';

                while ($data = fread($r, 1024)) {
                    call_user_func($this->callback, $prefix, $data);
                    if (1 === $type) {
                        $this->output .= $data;
                    } else {
                        $this->errorOutput .= $data;
                    }
                }
                if (feof($r)) {
                    fclose($r);
                    unset($this->pipes[$type]);
                }
            }
        }
    }
}

$process = new Process('php '.__DIR__.'/../data/say-hello.php', 'Romain');

$process->run(function ($type, $data) { echo "$type : $data\n"; });
