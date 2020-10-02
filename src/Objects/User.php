<?php

namespace Telegram\Bot\Objects;

/**
 * Class User.
 *
 *
 * @method int      getId()            Unique identifier for this user or bot.
 * @method bool     getIsBot()         True, if this user is a bot
 * @method string   getFirstName()     User's or bot's first name.
 * @method string   getLastName()      (Optional). User's or bot's last name.
 * @method string   getUsername()      (Optional). User's or bot's username.
 * @method string   getLanguageCode()  (Optional). IETF language tag of the user's language.
 * @method bool     getCanJoinGroups() True, if the bot can be invited to groups. Returned only in getMe.
 * @method bool     getCanReadAllGroupMessages() True, if privacy mode is disabled for the bot. Returned only in getMe.
 * @method bool     getSupportsInlineQueries() True, if the bot supports inline queries. Returned only in getMe.
 */
class User extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [];
    }
}
