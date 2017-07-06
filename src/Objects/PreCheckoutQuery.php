<?php

namespace Telegram\Bot\Objects;

/**
 * Class PreCheckoutQuery.
 *
 *
 * @method string      getId()   	          Unique query identifier
 * @method User        getFrom()        	  User who sent the query
 * @method string      getCurrency()          Three-letter ISO 4217 currency code
 * @method int     getTotalAmount()       Total price in the smallest units of the currency
 * @method string      getInvoicePayload()    Bot specified invoice payload
 * @method string      getShippingOptionId()  (Optional) Identifier of the shipping option chosen by the user
 * @method OrderInfo   getOrderInfo()         (Optional) Order info provided by the user
 */
class PreCheckoutQuery extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [
            'from'        => User::class,
            'order_info'  => OrderInfo::class,
        ];
    }
}
