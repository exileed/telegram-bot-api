<?php

/**
 * @author  Dmitriy Kuts <me@exileed.com>
 *
 * @link    http://exileed.com
 */

namespace Telegram\Bot\Callbacks;

use Telegram\Bot\Answers\Answerable;
use Telegram\Bot\Api as Telegram;
use Telegram\Bot\Objects\CallbackQuery;
use Telegram\Bot\Objects\Update;

/**
 * Class CallbackCommand.
 */
abstract class CallbackCommand implements CallbackCommandInterface
{
    use Answerable;

    /**
     * The name of the Telegram callback query command.
     *
     * @var string
     */
    protected $name;
/**
     * Unique identifier for the query to be answered.
     *
     * @var int
     */
    protected $callbackQueryId;
/**
     * Arguments passed to the command.
     *
     * @var array
     */
    protected $arguments = [];
/**
     * This object represents an incoming callback query from a callback button in an inline keyboard.
     *
     * @var CallbackQuery
     */
    protected $callbackQuery;
/**
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param array $arguments
     *
     * @return $this
     */
    public function setArguments($arguments)
    {
        $this->arguments = $arguments;
        return $this;
    }

    /**
     * Get Callback Query Command Name.
     *
     * The name of the Telegram callback query command.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set Callback Query Command Name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Unique identifier for the query to be answered.
     *
     * @return int
     */
    public function getCallbackQueryId()
    {
        return $this->callbackQueryId;
    }

    /**
     * Unique identifier for the query to be answered.
     *
     * @param int $callbackQueryId
     *
     * @return $this
     */
    public function setCallbackQueryId($callbackQueryId)
    {
        $this->callbackQueryId = $callbackQueryId;
        return $this;
    }

    /**
     * @param Telegram      $telegram
     * @param array         $arguments
     * @param Update        $update
     * @param CallbackQuery $callbackQuery
     */
    public function make(Telegram $telegram, $arguments, Update $update, CallbackQuery $callbackQuery)
    {
        $this->telegram = $telegram;
        $this->arguments = $arguments;
        $this->update = $update;
        $this->callbackQuery = $callbackQuery;
        $this->callbackQueryId = $callbackQuery->getId();
        $this->handle($arguments);
    }

    /**
     * {@inheritdoc}
     */
    abstract public function handle($arguments);
}
