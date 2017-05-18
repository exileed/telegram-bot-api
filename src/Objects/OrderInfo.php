<?php

namespace Telegram\Bot\Objects;

/**
 * Class OrderInfo.
 *
 *
 * @method string           getName()             (Optional) User name
 * @method string           getPhoneNumber()   	  (Optional)  User's phone number
 * @method string           getEmail()            (Optional) User email
 * @method ShippingAddress  getShippingAddress()  (Optional) User shipping address
 */
class OrderInfo extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [
            'shipping_address'             => ShippingAddress::class,
        ];
    }
}
