<?php

namespace App\Payment;

trait Payable
{

    /**
     * @return array
     */
    public function availablePaymentMethods()
    {
        $available = [];
        $methods = PaymentType::getArray();

        foreach ($methods as $method) {
            if (in_array($method, $this->paymentMethods)) {
                $available[] = $method;
            }
        }

        return $available;
    }

    /**
     * @return array
     */
    public function getAvailablePaymentMethodLabels()
    {
        $return = [];
        foreach ($this->availablePaymentMethods() as $method) {
            $return[$method] = PaymentType::getLabel($method);
        }

        return $return;
    }

}