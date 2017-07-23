<?php

namespace Laracasts\Generators\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MakeTelegramCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:telegram:command ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Telegram Command';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Create a new command instance.
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();
        $this->filesystem = $filesystem;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = $this->argument('name');
        //$pathToBotsDir = trim($this->ask('Path to your bot?', '/Bots'), '/');
        $pathToBot = '/bots'.'/'.$name;
        $pathToBotSrc = $pathToBot.'/src';
        if (!$this->filesystem->exists(base_path(trim($pathToBot, '/')))) {
            $this->filesystem->makeDirectory(base_path($pathToBotSrc), 0777, true);
        } else {
            $this->error('Ошибка. Путь занят.');
            exit;
        }
        $maker = new Maker($pathToBotSrc, $name);
        $maker->makeServiceProvider();
        $config = $this->ask('Введите имя для файла конфигурации (Если файл существует, он будет перезаписан.):', strtolower($name));
        $token = $this->ask('Введите токен данного бота:', 'SomeString');
        $maker->makeConfig($config, $token);
        $maker->makeTelegramController();
        $maker->makeBaseCommands($config);
        $maker->makeRoutes($config);
        $this->successInstall($maker->getNamespace(), $pathToBotSrc);
    }

    /**
     * Parse the name and format according to the root namespace.
     *
     * @param string $name
     *
     * @return string
     */
    protected function parseName($name)
    {
        return ucwords(camel_case($name)).'TableSeeder';
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/../stubs/command.stub';
    }

    /**
     * Build the class with the given name.
     *
     * @param string $name
     *
     * @return string
     */
    protected function buildClass($name = null)
    {
        $stub = $this->files->get($this->getStub());

        return $this->replaceClass($stub, $name);
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param string $stub
     * @param string $name
     *
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $stub = str_replace('{{class}}', $name, $stub);

        return $stub;
    }

    /**
     * Get the destination class path.
     *
     * @param string $name
     *
     * @return string
     */
    protected function getPath($name)
    {
        return base_path().'/database/seeds/'.str_replace('\\', '/', $name).'.php';
    }
}
