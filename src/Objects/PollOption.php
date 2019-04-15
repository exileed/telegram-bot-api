<?php

namespace Telegram\Bot\Objects;

/**
 * Class PollOption.
 *
 *
 * @method string  getText()        Option text, 1-100 characters
 * @method int     getVoterCount()  Number of users that voted for this option
 */
class PollOption extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [];
    }
}
