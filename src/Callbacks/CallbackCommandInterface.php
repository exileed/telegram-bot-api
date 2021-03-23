<?php

/**
 * @author  Dmitriy Kuts <me@exileed.com>
 *
 * @link    http://exileed.com
 */

namespace Telegram\Bot\Callbacks;

use Telegram\Bot\Api as Telegram;
use Telegram\Bot\Objects\CallbackQuery;
use Telegram\Bot\Objects\Update;

/**
 * Interface CallbackQueryInterface.
 */
interface CallbackCommandInterface
{
    /**
     * @return array
     */
    public function getArguments();
/**
     * @param array $arguments
     *
     * @return CallbackCommand
     */
    public function setArguments($arguments);
/**
     * Get Callback Query Command Name.
     *
     * The name of the Telegram callback query command.
     *
     * @return string
     */
    public function getName();
/**
     * Set Callback Query Command Name.
     *
     * @param string $name
     *
     * @return CallbackCommand
     */
    public function setName($name);
/**
     * Unique identifier for the query to be answered.
     *
     * @return int
     */
    public function getCallbackQueryId();
/**
     * Unique identifier for the query to be answered.
     *
     * @param int $callbackQueryId
     *
     * @return CallbackCommand
     */
    public function setCallbackQueryId($callbackQueryId);
/**
     * Make command.
     *
     * @param Telegram      $telegram
     * @param array         $arguments
     * @param Update        $update
     * @param CallbackQuery $callbackQuery
     *
     * @return
     */
    public function make(Telegram $telegram, $arguments, Update $update, CallbackQuery $callbackQuery);
}
