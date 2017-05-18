<?php

namespace Telegram\Bot\Objects;

/**
 * Class LabeledPrice.
 *
 *
 * @method string    getLabel()  	Portion label
 * @method integer   getAmount()   	Price of the product in the smallest units of the currency
 */
class LabeledPrice extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [];
    }
}
