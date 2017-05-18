<?php

namespace Telegram\Bot\Objects;

/**
 * Class ShippingQuery.
 *
 *
 * @method string            getId()   	           Unique query identifier
 * @method User              getFrom()        	   User who sent the query
 * @method string            getInvoicePayload()   Bot specified invoice payload
 * @method ShippingAddress   getShippingAddress()  User specified shipping address
 */
class ShippingQuery extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [
            'from'              => User::class,
            'shipping_address'  => ShippingAddress::class,
        ];
    }
}
