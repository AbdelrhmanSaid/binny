<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command as ZeroCommand;
use Symfony\Component\Process\Process;

abstract class Command extends ZeroCommand
{
    /**
     * Create a new process instance.
     *
     * @param  string|array  $command
     * @param  string|null  $path
     * @return Process
     */
    public function process($command, $path = null)
    {
        $command = is_array($command) ? $command : explode(' ', $command);

        return tap(new Process($command, $path), function ($process) {
            $process->setTimeout(null);
            $process->run();
        });
    }

    /**
     * Fail the command with the given error message.
     *
     * @param  string  $message
     * @return void
     */
    protected function fail($message)
    {
        $this->error($message);

        exit(Command::FAILURE);
    }
}
