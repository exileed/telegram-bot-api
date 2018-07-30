<?php

namespace Telegram\Bot\Objects\Passport;

use Telegram\Bot\Objects\BaseObject;

/**
 * Class PassportData.
 *
 *
 * @method EncryptedPassportElement   getData()         Array with information about documents and other Telegram Passport elements that was shared with the bot.
 * @method EncryptedCredentials       getCredentials()  Encrypted credentials required to decrypt the data.
 *
 */
class PassportData extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [
            'data'        => EncryptedPassportElement::class,
            'credentials' => EncryptedCredentials::class,
        ];
    }
}
