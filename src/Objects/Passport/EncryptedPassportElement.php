<?php

namespace Telegram\Bot\Objects\Passport;

use Telegram\Bot\Objects\BaseObject;

/**
 * Class EncryptedPassportElement.
 *
 * @method string              getType()           Element type. One of “personal_details”, “passport”, “driver_license”, “identity_card”, “internal_passport”, “address”, “utility_bill”, “bank_statement”, “rental_agreement”, “passport_registration”, “temporary_registration”, “phone_number”, “email”.
 * @method string              getData()          (Optional). Base64-encoded encrypted Telegram Passport element data.
 * @method string              getPhoneNumber()   (Optional). User's verified phone number, available only for “phone_number” type.
 * @method string              getEmail()         (Optional). User's verified email address, available only for “email” type.
 * @method PassportFile[]      getFiles()         (Optional). Array of encrypted files with documents.
 * @method PassportFile        getFrontSide()     (Optional). Encrypted file with the front side of the document.
 * @method PassportFile        getReverseSide()   (Optional). Encrypted file with the reverse side of the document.
 * @method PassportFile[]      getTranslation()   (Optional). Array of encrypted files with translated
 * versions of documents provided by the user. Available if requested for “passport”, “driver_license”, “identity_card”,
 * “internal_passport”, “utility_bill”, “bank_statement”,
 * “rental_agreement”, “passport_registration” and “temporary_registration” types.
 * @method PassportFile        getSelfie()        (Optional). Array of encrypted files with documents.
 */
class EncryptedPassportElement extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [
            'front_side'   => PassportFile::class,
            'reverse_side' => PassportFile::class,
            'selfie'       => PassportFile::class,
        ];
    }
}
