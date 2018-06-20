<?php

namespace Chadanuk\DuskWordpressTests\Console;

use Dotenv\Dotenv;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class DuskCommand extends SymfonyCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'dusk';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the Dusk tests for the application';

    /**
     * Indicates if the project has its own PHPUnit configuration.
     *
     * @var boolean
     */
    protected $hasPhpUnitConfiguration = false;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct($basePath = null)
    {
        parent::__construct();
        $this->basePath = isset($basePath) ? $basePath.'/' : dirname(__DIR__);
    }


    /**
     * In this method setup command, description and its parameters
     */
    protected function configure()
    {
        $this->setName($this->name);
        $this->setDescription($this->description);
        $this->addArgument('-without-tty', InputArgument::OPTIONAL, 'TTY?.');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->purgeScreenshots();

        $this->purgeConsoleLogs();

        $options = array_slice($_SERVER['argv'], 2);

        return $this->withDuskEnvironment(function () use ($options, $output) {

        });
    }

    /**
     * Get the PHP binary to execute.
     *
     * @return array
     */
    protected function binary()
    {
        return [PHP_BINARY, 'vendor/phpunit/phpunit/phpunit'];
    }

    /**
     * Get the array of arguments for running PHPUnit.
     *
     * @param  array  $options
     * @return array
     */
    protected function phpunitArguments($options)
    {
        return array_merge(['-c', $this->basePath.'phpunit.dusk.xml'], $options);
    }

    /**
     * Purge the failure screenshots
     *
     * @return void
     */
    protected function purgeScreenshots()
    {
        $files = Finder::create()->files()
                        ->in($this->basePath.'/tests/Browser/screenshots')
                        ->name('failure-*');

        foreach ($files as $file) {
            @unlink($file->getRealPath());
        }
    }

    /**
     * Purge the console logs.
     *
     * @return void
     */
    protected function purgeConsoleLogs()
    {
        $files = Finder::create()->files()
            ->in($this->basePath.'/tests/Browser/console')
            ->name('*.log');

        foreach ($files as $file) {
            @unlink($file->getRealPath());
        }
    }

    /**
     * Run the given callback with the Dusk configuration files.
     *
     * @param  \Closure  $callback
     * @return mixed
     */
    protected function withDuskEnvironment($callback)
    {
        if (file_exists($this->basePath.$this->duskFile())) {
            if (file_get_contents($this->basePath.'.env') !== file_get_contents($this->basePath.$this->duskFile())) {
                $this->backupEnvironment();
            }

            $this->refreshEnvironment();
        }

        $this->writeConfiguration();


    }

    public function cleanUp()
    {
        $this->removeConfiguration();

        if (file_exists($this->basePath.$this->duskFile()) && file_exists($this->basePath.'.env.backup')) {
            $this->restoreEnvironment();
        }
    }

    /**
     * Backup the current environment file.
     *
     * @return void
     */
    protected function backupEnvironment()
    {
        copy($this->basePath.'.env', $this->basePath.'.env.backup');

        copy($this->basePath.$this->duskFile(), $this->basePath.'.env');
    }

    /**
     * Restore the backed-up environment file.
     *
     * @return void
     */
    protected function restoreEnvironment()
    {
        copy($this->basePath.'.env.backup', $this->basePath.'.env');

        unlink($this->basePath.'.env.backup');
    }

    /**
     * Refresh the current environment variables.
     *
     * @return void
     */
    protected function refreshEnvironment()
    {
        (new Dotenv($this->basePath))->overload();
    }

    /**
     * Write the Dusk PHPUnit configuration.
     *
     * @return void
     */
    protected function writeConfiguration()
    {
        if (! file_exists($file = $this->basePath.'phpunit.dusk.xml')) {
            copy(realpath(__DIR__.'/../../stubs/phpunit.xml'), $file);
        } else {
            $this->hasPhpUnitConfiguration = true;
        }
    }

    /**
     * Remove the Dusk PHPUnit configuration.
     *
     * @return void
     */
    protected function removeConfiguration()
    {
        if (! $this->hasPhpUnitConfiguration) {
            unlink($this->basePath.'phpunit.dusk.xml');
        }
    }

    /**
     * Get the name of the Dusk file for the environment.
     *
     * @return string
     */
    protected function duskFile()
    {
        if (file_exists($this->basePath.$file = '.env.dusk.'.env('WP_ENV'))) {
            return $file;
        }

        return '.env.dusk';
    }
}
