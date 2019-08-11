<?php

namespace Telegram\Bot\Objects\InlineKeyboard;

use Telegram\Bot\Objects\BaseObject;

/**
 * Class InlineKeyboardMarkup
 *
 * @method InlineKeyboardButton[]   getInlineKeyboard() Button rows.
 */
class InlineKeyboardMarkup extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [
            'inline_keyboard' => InlineKeyboardButton::class,
        ];
    }
}
