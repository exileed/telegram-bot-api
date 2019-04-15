<?php

namespace Telegram\Bot\Objects;

/**
 * Class Poll.
 *
 *
 * @method string        getId()        Unique poll identifier
 * @method string        getQuestion()  Poll question
 * @method PollOption[]  getOptions()   List of poll options
 * @method bool          getIsClosed()  True, if the poll is closed
 */
class Poll extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [
            'options' => PollOption::class,
        ];
    }
}
