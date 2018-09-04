<?php

namespace App\Reservations;

class ReservationStatus
{
    const STATUS_CART = 0; //reservation created from cart and is waiting for checkout submit
    const STATUS_PAYING = 1; //reservation is being processed by payment systems, on errors this status will be the same (check transaction status for more info.)
    const STATUS_PENDING = 2; //waiting for administrators for payment processing in bank
    const STATUS_PROCESSING = 3; //reservation is being processed by system administrators
    const STATUS_COMPLETE = 4; //reservation is completed, nothing to do
    const STATUS_CANCELED = 5; //canceled by system administrators
    const STATUS_FAILED = 6; //payment was unsuccesfull

    /**
     * @return array
     */
    public static function getArray()
    {
        return [
            static::STATUS_CART,
            static::STATUS_PAYING,
            static::STATUS_PENDING,
            static::STATUS_PROCESSING,
            static::STATUS_COMPLETE,
            static::STATUS_CANCELED,
            static::STATUS_FAILED,
        ];
    }

    /**
     * @return array
     */
    public static function getLabels()
    {
        return [
            static::STATUS_CART => trans('reservation.status.' . static::STATUS_CART),
            static::STATUS_PAYING => trans('reservation.status.' . static::STATUS_PAYING),
            static::STATUS_PENDING => trans('reservation.status.' . static::STATUS_PENDING),
            static::STATUS_PROCESSING => trans('reservation.status.' . static::STATUS_PROCESSING),
            static::STATUS_COMPLETE => trans('reservation.status.' . static::STATUS_COMPLETE),
            static::STATUS_CANCELED => trans('reservation.status.' . static::STATUS_CANCELED),
            static::STATUS_FAILED => trans('reservation.status.' . static::STATUS_FAILED),
        ];
    }

    /**
     * @return array
     */
    public static function getColors()
    {
        return [
            static::STATUS_PAYING => 'Tomato',
            static::STATUS_PROCESSING => 'DeepSkyBlue',
            static::STATUS_COMPLETE => 'LimeGreen',
            static::STATUS_CANCELED => 'Red',
            static::STATUS_FAILED => 'Red',
        ];
    }

    /**
     * @param $status
     * @return mixed
     */
    public static function getStatusColor($status)
    {
        if (!empty(self::getColors()[$status])) {
            return self::getColors()[$status];
        }

        return 'transparent';
    }

    /**
     * @param int $type
     * @return string
     */
    public static function getLabel($type)
    {
        return array_get(static::getLabels(), $type);
    }
}