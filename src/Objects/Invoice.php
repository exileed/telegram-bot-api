<?php

namespace Telegram\Bot\Objects;

/**
 * Class Invoice.
 *
 *
 * @method string    getTitle()  	    Product name
 * @method string    getDescription()   	Product description
 * @method string    getStartParameter() Unique bot deep-linking parameter that can be used to generate this invoice
 * @method string    getCurrency()   	Three-letter ISO 4217 currency code
 * @method int   getTotalAmount()   	Total price in the smallest units of the currency
 */
class Invoice extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [];
    }
}
