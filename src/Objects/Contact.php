<?php

namespace Telegram\Bot\Objects;

/**
 * Class Contact.
 *
 *
 * @method string   getPhoneNumber()    Contact's phone number.
 * @method string   getFirstName()      Contact's first name.
 * @method string   getLastName()       (Optional). Contact's last name.
 * @method int      getUserId()         (Optional). Contact's user identifier in Telegram.
 * @method string   getVcard()          (Optional). Additional data about the contact in the form of a vCard.
 */
class Contact extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [];
    }
}
