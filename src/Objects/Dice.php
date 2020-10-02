<?php

namespace Telegram\Bot\Objects;

/**
 * Class Dice.
 *
 *
 * @method string getEmoji() Emoji on which the dice throw animation is based
 * @method int getValue() Value of the dice, 1-6 for “🎲” and “🎯” base emoji, 1-5 for “🏀” base emoji
 */
class Dice extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [];
    }
}
