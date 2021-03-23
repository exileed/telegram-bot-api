<?php

namespace Telegram\Bot\Objects;

/**
 * Class ShippingAddress.
 *
 *
 * @method string    getCountryCode()  ISO 3166-1 alpha-2 country code
 * @method string    getState()       State, if applicable
 * @method string    getCity()         City
 * @method string    getStreetLine1()  First line for the address
 * @method string    getStreetLine2()  Second line for the address
 * @method string    getPostCode()     Address post code
 */
class ShippingAddress extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [];
    }
}
