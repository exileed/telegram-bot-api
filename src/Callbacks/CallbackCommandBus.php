<?php

namespace Telegram\Bot\Callbacks;

use Telegram\Bot\Answers\AnswerBus;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Objects\Update;

/**
 * Class CommandBus.
 */
class CallbackCommandBus extends AnswerBus
{
    /**
     * @var CallbackCommand[] Holds all commands.
     */
    protected $commands = [];

    /**
     * @var CallbackCommand[] Holds all commands' aliases.
     */
    protected $commandAliases = [];

    /**
     * Instantiate Command Bus.
     *
     * @param Api $telegram
     *
     * @throws TelegramSDKException
     */
    public function __construct(Api $telegram)
    {
        $this->telegram = $telegram;
    }

    /**
     * Returns the list of commands.
     *
     * @return array
     */
    public function getCommands()
    {
        return $this->commands;
    }

    /**
     * Add a list of commands.
     *
     * @param array $commands
     *
     * @return CallbackCommandBus
     */
    public function addCallbackCommands(array $commands)
    {
        foreach ($commands as $command) {
            $this->addCallbackCommand($command);
        }

        return $this;
    }

    /**
     * Add a command to the commands list.
     *
     * @param CallbackCommandInterface|string $command Either an object or full path to the command class.
     *
     * @throws TelegramSDKException
     *
     * @return CallbackCommandBus
     */
    public function addCallbackCommand($command)
    {
        if (!is_object($command)) {
            if (!class_exists($command)) {
                throw new TelegramSDKException(
                    sprintf(
                        'Command class "%s" not found! Please make sure the class exists.',
                        $command
                    )
                );
            }

            if ($this->telegram->hasContainer()) {
                $command = $this->buildDependencyInjectedAnswer($command);
            } else {
                $command = new $command();
            }
        }

        if ($command instanceof CallbackCommandInterface) {

            /*
             * At this stage we definitely have a proper command to use.
             *
             * @var CallbackCommand $command
             */
            $this->commands[$command->getName()] = $command;

            return $this;
        }

        throw new TelegramSDKException(
            sprintf(
                'Command class "%s" should be an instance of "Telegram\Bot\Commands\CommandInterface"',
                get_class($command)
            )
        );
    }

    /**
     * Removes a list of commands.
     *
     * @param array $names
     *
     * @return CallbackCommandBus
     */
    public function removeCommands(array $names)
    {
        foreach ($names as $name) {
            $this->removeCommand($name);
        }

        return $this;
    }

    /**
     * Remove a command from the list.
     *
     * @param $name
     *
     * @return CallbackCommandBus
     */
    public function removeCommand($name)
    {
        unset($this->commands[$name]);

        return $this;
    }

    /**
     * Handles Inbound Messages and Executes Appropriate Command.
     *
     * @param string $message
     * @param Update $update
     *
     * @throws TelegramSDKException
     *
     * @return Update
     */
    protected function handler($message, Update $update)
    {
        $match = $this->parseArguments($message);

        $command = strtolower($match[0]); //All commands must be lowercase.
        array_shift($match);

        $this->execute($command, $match, $update, $update->getCallbackQuery());

        return $update;
    }

    private function parseArguments($message)
    {
        return explode(' ', $message);
    }

    /**
     * Execute the command.
     *
     * @param $name
     * @param $arguments
     * @param $message
     * @param $callback
     *
     * @return mixed
     */
    protected function execute($name, $arguments, $message, $callback)
    {
        if (array_key_exists($name, $this->commands)) {
            return $this->commands[$name]->make($this->telegram, $arguments, $message, $callback);
        }

        return 'ok';
    }
}
