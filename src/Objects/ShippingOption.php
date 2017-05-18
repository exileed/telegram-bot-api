<?php

namespace Telegram\Bot\Objects;

/**
 * Class ShippingOption.
 *
 *
 * @method string        getId()     (Optional) User name
 * @method string        getTitle()  Optional)  User's phone number
 * @method LabeledPrice  getPrice()  List of price portions
 */
class ShippingOption extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [
            'prices'             => LabeledPrice::class,
        ];
    }
}
