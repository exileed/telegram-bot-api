<?php

namespace Telegram\Bot\Objects;

/**
 * Class PollAnswer.
 *
 *
 * @method string getPollId() Unique poll identifier
 * @method User getUser() The user, who changed the answer to the poll
 * @method int[] getOptionIds() 0-based identifiers of answer options, chosen by the user. May be empty if the user retracted their vote.
 *
 */
class PollAnswer extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [
            'user' => User::class,
        ];
    }
}
