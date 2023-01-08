<?php

namespace App\Commands;

use ZipArchive;

class GitChangesCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'git:changes
                            {path : The path to the git repository}
                            {base : The base commit}
                            {head=HEAD : The head commit}
                            {--z|zip : Create a zip file of the changes}
                            {--o|output= : The output file name}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Get the changes between two commits, optionally create a zip file';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /* Check if git is installed */
        if (!$this->process('git --version')->isSuccessful()) {
            $this->fail('Git is not installed');
        }

        /* Check if the path is a git repository */
        $path = realpath($this->argument('path'));
        if (!is_dir($path) || !is_dir($path . '/.git')) {
            $this->fail('Path is not a git repository');
        }

        /* Get the diff changes between base and head */
        $base = $this->validateCommit($path, $this->argument('base'));
        $head = $this->validateCommit($path, $this->argument('head'));
        $diff = $this->process("git diff --name-only $base $head", $path);

        if (!$diff->isSuccessful()) {
            $this->fail('Failed to get changes');
        }

        /* Print the changes */
        $files = array_filter(explode(PHP_EOL, $diff->getOutput()));
        $this->table(['Files'], array_map(fn ($file) => [$file], $files));

        if (!$this->option('zip')) {
            return Command::SUCCESS; // Exit if zip option is not set
        }

        return $this->zip($path, $files);
    }

    /**
     * Validate the given commit.
     *
     * @param  string  $path
     * @param  string  $commit
     * @return string
     */
    protected function validateCommit($path, $commit)
    {
        if (!$this->process("git rev-parse --verify $commit", $path)->isSuccessful()) {
            $this->fail("Commit '$commit' does not exist");
        }

        return $commit;
    }

    /**
     * Create a zip file of the changes.
     *
     * @param  string  $path
     * @param  array  $files
     * @return int
     */
    protected function zip($path, $files)
    {
        $output = basename($this->option('output'), '.zip') ?: 'changes';
        $output = getcwd() . '/' . $output . '.zip';

        if (file_exists($output) && !$this->confirm("File '$output' already exists, overwrite?")) {
            $this->fail('User aborted, output file already exists');
        }

        $zip = new ZipArchive();
        if (!$zip->open($output, ZipArchive::CREATE)) {
            $this->fail("Failed to create zip file '$output'");
        }

        foreach ($files as $file) {
            if (file_exists($path . '/' . $file)) {
                $zip->addFile($path . '/' . $file, $file);
            }
        }

        $zip->close();

        $this->info("Created zip file '$output'");
        return Command::SUCCESS;
    }
}
