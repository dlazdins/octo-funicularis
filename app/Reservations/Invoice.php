<?php

namespace App\Reservations;

use App\Payment\PaymentType;
use PDF;
use Settings;

class Invoice
{
    /**
     * @var PDF
     */
    protected $pdf;

    /**
     * @var Reservation
     */
    protected $order;

    /**
     * Invoice constructor
     *
     * @param Reservation $reservation
     */
    public function __construct(Reservation $reservation)
    {
        $this->order = $reservation;
        $this->pdf = PDF::loadView('public.controllers.shop.invoice', [
            'locale' => app()->getLocale(),
            'date' => $reservation->created_at->format('d.m.Y'),
            'reservation' => $reservation,
            'buyer' => $reservation->owner,
            'seller' => self::getSellerData(),
            'paymentType' => PaymentType::getLabel($reservation->payment_type),
            'sumInWords' => $this->getSumInWords()
        ]);

        $this->pdf->setOptions([
            'defaultFont' => 'sans-serif'
        ]);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getFileName();
    }

    /**
     * @return string
     */
    protected function getFileName()
    {
        return $this->reservation->getIdentifier() . '.pdf';
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function download()
    {
        return $this->pdf->download($this->getFileName());
    }

    /**
     * @return mixed
     */
    public function open()
    {
        return $this->pdf->stream($this->getFileName());
    }

    /**
     * @return array
     */
    public static function getSellerData()
    {
        return [
            'name' => Settings::get('invoice.seller_name'),
            'address' => Settings::get('invoice.seller_address'),
            'code' => Settings::get('invoice.seller_code'),
            'bank' => Settings::get('invoice.seller_bank'),
            'account' => Settings::get('invoice.seller_account'),
            'iban' => Settings::get('invoice.seller_iban'),
        ];
    }

    /**
     * @return string
     */
    public function getSumInWords()
    {
        $number = new NumbersToWords();
        $total = $this->reservation->total / 100;
        $euro = floor($total);
        $cents = ($total - $euro) * 100;

        if ($this->reservation->language != 'lv') {
            return null;
        }

        return trim($number->convert(
            sprintf("%.2f", $total),
            trans('shop.invoice.currency_large'),
            trans_choice('shop.invoice.currency_small', round($cents))
        ));
    }

    public function output()
    {
        return $this->pdf->output();
    }
}