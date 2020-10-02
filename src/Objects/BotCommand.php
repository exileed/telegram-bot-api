<?php

namespace Telegram\Bot\Objects;

/**
 * Class Dice.
 *
 *
 * @method string getCommand() Text of the command, 1-32 characters. Can contain only lowercase English letters, digits and underscores.
 * @method string getDescription() Description of the command, 3-256 characters.
 */
class BotCommand extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [];
    }
}
