<?php

namespace Telegram\Bot;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Arr;
use InvalidArgumentException;

/**
 * Class BotsManager.
 *
 */
class BotsManager
{
    /** @var array The config instance. */
    protected $config;

    /** @var Container The container instance. */
    protected $container;

    /** @var Api[] The active bot instances. */
    protected $bots = [];

    /**
     * TelegramManager constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Set the IoC Container.
     *
     * @param $container Container instance
     *
     * @return $this
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Reconnect to the given bot.
     *
     * @param string $name
     *
     * @return Api
     */
    public function reconnect($name = null)
    {
        $name = $name ?: $this->getDefaultBot();
        $this->disconnect($name);

        return $this->bot($name);
    }

    /**
     * Disconnect from the given bot.
     *
     * @param string $name
     *
     * @return void
     */
    public function disconnect($name = null)
    {
        $name = $name ?: $this->getDefaultBot();
        unset($this->bots[$name]);
    }

    /**
     * Get a bot instance.
     *
     * @param string $name
     *
     * @return Api
     */
    public function bot($name = null)
    {
        $name = $name ?: $this->getDefaultBot();

        if (!isset($this->bots[$name])) {
            $this->bots[$name] = $this->makeBot($name);
        }

        return $this->bots[$name];
    }

    /**
     * Make the bot instance.
     *
     * @param string $name
     *
     * @return Api
     */
    protected function makeBot($name)
    {
        $config = $this->getBotConfig($name);

        $token = Arr::get($config, 'token');
        $commands = Arr::get($config, 'commands', []);
        $callbacks = Arr::get($config, 'callbacks', []);

        $telegram = new Api(
            $token,
            $this->getConfig('async_requests', false),
            $this->getConfig('http_client_handler', null)
        );

        // Check if DI needs to be enabled for Commands
        if ($this->getConfig('resolve_command_dependencies', false) && isset($this->container)) {
            $telegram->setContainer($this->container);
        }

        $commands = $this->parseBotCommands($commands);
        $callbacks = $this->parseCallbacks($callbacks);

        // Register Commands
        $telegram->addCommands($commands);
        $telegram->getCallbackBus()->addCallbackCommands($callbacks);

        return $telegram;
    }

    /**
     * Get the configuration for a bot.
     *
     * @param string|null $name
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public function getBotConfig($name = null)
    {
        $name = $name ?: $this->getDefaultBot();

        $bots = $this->getConfig('bots');
        if (!is_array($config = Arr::get($bots, $name)) && !$config) {
            throw new InvalidArgumentException("Bot [$name] not configured.");
        }

        $config['bot'] = $name;

        return $config;
    }

    /**
     * Get the default bot name.
     *
     * @return string
     */
    public function getDefaultBot()
    {
        return $this->getConfig('default');
    }

    /**
     * Get the specified configuration value for Telegram.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getConfig($key, $default = null)
    {
        return Arr::get($this->config, $key, $default);
    }

    /**
     * Builds the list of commands for the given commands array.
     *
     * @param array $commands
     *
     * @return array An array of commands which includes global and bot specific commands.
     */
    protected function parseBotCommands(array $commands)
    {
        $globalCommands = $this->getConfig('commands', []);
        $parsedCommands = $this->parseCommands($commands);

        return $this->deduplicateArray(array_merge($globalCommands, $parsedCommands));
    }

    /**
     * Parse an array of commands and build a list.
     *
     * @param array $commands
     *
     * @return array
     */
    protected function parseCommands(array $commands)
    {
        if (!is_array($commands)) {
            return $commands;
        }

        $commandGroups = $this->getConfig('command_groups');
        $sharedCommands = $this->getConfig('shared_commands');

        $results = [];
        foreach ($commands as $command) {
            // If the command is a group, we'll parse through the group of commands
            // and resolve the full class name.
            if (isset($commandGroups[$command])) {
                $results = array_merge(
                    $results,
                    $this->parseCommands($commandGroups[$command])
                );

                continue;
            }

            // If this command is actually a shared command, we'll extract the full
            // class name out of the command list now.
            if (isset($sharedCommands[$command])) {
                $command = $sharedCommands[$command];
            }

            if (!in_array($command, $results)) {
                $results[] = $command;
            }
        }

        return $results;
    }

    /**
     * De-duplicate an array.
     *
     * @param array $array
     *
     * @return array
     */
    protected function deduplicateArray(array $array)
    {
        return array_values(array_unique($array));
    }

    /**
     * Parse an array of commands and build a list.
     *
     * @param array $commands
     *
     * @return array
     */
    protected function parseCallbacks(array $commands)
    {
        if (!is_array($commands)) {
            return $commands;
        }

        $commandGroups = $this->getConfig('callback_groups');
        $sharedCommands = $this->getConfig('shared_callbacks');

        $results = [];
        foreach ($commands as $command) {
            // If the command is a group, we'll parse through the group of commands
            // and resolve the full class name.
            if (isset($commandGroups[$command])) {
                $results = array_merge(
                    $results,
                    $this->parseCallbacks($commandGroups[$command])
                );

                continue;
            }

            // If this command is actually a shared command, we'll extract the full
            // class name out of the command list now.
            if (isset($sharedCommands[$command])) {
                $command = $sharedCommands[$command];
            }

            if (!in_array($command, $results)) {
                $results[] = $command;
            }
        }

        return $results;
    }

    /**
     * Set the default bot name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setDefaultBot($name)
    {
        array_set($this->config, 'default', $name);

        return $this;
    }

    /**
     * Return all of the created bots.
     *
     * @return Api[]
     */
    public function getBots()
    {
        return $this->bots;
    }

    /**
     * Magically pass methods to the default bot.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->bot(), $method], $parameters);
    }
}
