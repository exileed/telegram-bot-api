<?php

namespace Telegram\Bot\Objects\InlineKeyboard;

use Telegram\Bot\Objects\BaseObject;

/**
 * Class CallbackGame.
 *
 *
 * @method int    getUserId()             User identifier.
 * @method int    getScore()              New score, must be non-negative.
 * @method bool   getForce()              Pass True, if the high score is allowed to decrease.
 * @method bool   getDisableEditMessage() Pass True, if the game message should not be automatically edited to
 * include the current scoreboard.
 * @method int    getChatId()             Unique identifier for the target chat.
 * @method int    getMessageId()          Identifier of the sent message.
 * @method string getInlineMessageId()    Identifier of the inline message.
 */
class CallbackGame extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [];
    }
}
