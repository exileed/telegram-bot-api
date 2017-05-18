<?php

namespace Telegram\Bot\Objects;

/**
 * Class ShippingOption.
 *
 *
 * @method string     getCurrency()                 Three-letter ISO 4217 currency code
 * @method int    getTotalAmount()              Total price in the smallest units of the currency
 * @method string     getInvoicePayload()           Bot specified invoice payload
 * @method string     getShippingOptionId()         (Optional) Identifier of the shipping option chosen by the user
 * @method OrderInfo  getOrderInfo()                (Optional) Order info provided by the user
 * @method string     getTelegramPaymentChargeId()  Telegram payment identifier
 * @method string     getProviderPaymentChargeId()  Provider payment identifier
 */
class SuccessfulPayment extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [
            'order_info'             => OrderInfo::class,
        ];
    }
}
