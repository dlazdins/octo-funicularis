<?php

namespace App\Payment;

class PaymentType
{
    const CARD     = 'card';
    const SEB      = 'seb';
    const NORDEA   = 'nordea';
    const SWEDBANK = 'swedbank';
    const TRANSFER = 'transfer';

    /**
     * @return array
     */
    public static function getArray()
    {
        return [
            self::SWEDBANK,
            self::NORDEA,
            self::SEB,
            self::CARD,
            self::TRANSFER,
        ];
    }

    /**
     * @return array
     */
    public static function getLabels()
    {
        return [
            self::SWEDBANK => trans('payment.type.swedbank'),
            self::NORDEA   => trans('payment.type.nordea'),
            self::SEB      => trans('payment.type.seb'),
            self::CARD     => trans('payment.type.card'),
            self::TRANSFER => trans('payment.type.transfer'),
        ];
    }

    /**
     * @param string $type
     * @return string
     */
    public static function getLabel($type)
    {
        if (in_array($type, self::getArray())) {
            return self::getLabels()[$type];
        }
        return null;
    }

    /**
     * @return array
     */
    static public function getGateways()
    {
        return [
            self::SWEDBANK => 'swedbank-banklink',
            self::NORDEA   => 'nordea-link',
            self::SEB      => 'seb-link',
            self::CARD     => 'firstdata'
        ];
    }

    /**
     * @param string $type
     * @return mixed
     */
    public static function getGatewayByType($type)
    {
        return array_get(static::getGateways(), $type);
    }

    /**
     * @return array
     */
    public static function getCheckoutOptions()
    {
        return self::getLabels();
    }
}