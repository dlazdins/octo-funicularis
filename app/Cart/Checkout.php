<?php

namespace App\Cart;

use App\Donations\Donation;
use App\Http\Requests\CreateOrderRequest;
use App\Orders\Order;
use App\Orders\OrderDetails;
use App\Orders\OrderStatus;
use App\Payment\PaymentType;
use App\Products\Product;
use Arbory\Merchant\Models\OrderLine;
use Cart as ShoppingCart;
use Gloudemans\Shoppingcart\CartItem;
use Session;

class Checkout
{
    /**
     * @var array
     */
    protected $formData;

    /**
     * @var OrderDetails;
     */
    protected $details;

    /**
     * @var Order
     */
    protected $order;

    /**
     * @var string
     */
    protected $paymentType;

    /**
     * @var array
     */
    protected $lines;

    /**
     * Checkout constructor.
     *
     * @param CreateOrderRequest $request
     */
    public function __construct(CreateOrderRequest $request)
    {
        $this->lines = [];
        $this->formData = $request->all();
        $this->paymentType = $request->get('payment_type');
        $this->order = $this->createOrder();
        $this->details = $this->createOrderDetails();
        $this->order->owner()->associate($this->details)->save();
    }

    /**
     * @return Order
     */
    protected function createOrder()
    {
        $status = $this->paymentType === PaymentType::TRANSFER ? OrderStatus::STATUS_PROCESSING : OrderStatus::STATUS_PAYING;

        $order = new Order([
            'status' => $status,
            'total' => 0,
            'payment_currency' => 'EUR',
            'payment_started_at' => date('Y-m-d H:i:s'),
            'session_id' => Session::getId(),
            'client_ip' => request()->ip(),
            'payment_type' => array_get($this->formData, 'payment_type'),
            'language' => app()->getLocale()
        ]);

        $order->payment_type = array_get($this->formData, 'payment_type');

        $order->save();

        return $order;
    }

    /**
     * @return OrderDetails
     */
    protected function createOrderDetails()
    {
        $formData = $this->formData;

        $details = new OrderDetails([
            'person_type' => array_get($formData, 'person_type'),
            'company_name' => array_get($formData, 'company_name'),
            'company_code' => array_get($formData, 'company_code'),
            'company_country' => array_get($formData, 'company_country'),
            'company_city' => array_get($formData, 'company_city'),
            'company_postal_code' => array_get($formData, 'company_postal_code'),
            'company_street' => array_get($formData, 'company_street'),
            'company_bank' => array_get($formData, 'company_bank'),
            'company_account' => array_get($formData, 'company_account'),
            'company_person' => array_get($formData, 'company_person'),
            'first_name' => array_get($formData, 'first_name'),
            'last_name' => array_get($formData, 'last_name'),
            'phone' => array_get($formData, 'phone'),
            'email' => array_get($formData, 'email'),
            'comments' => array_get($formData, 'comments'),
        ]);

        $details->order_id = $this->order->id;

        $details->save();

        return $details;
    }

    public function addCartLines()
    {
        $total = 0;
        $items = ShoppingCart::content();

        /** @var CartItem $item */
        foreach ($items as $item) {

            $lineTotal = $item->total * 100;
            $total += $lineTotal;

            $product = $this->parseProduct($item);

            $line = new OrderLine([
                'order_id' => $this->order->id,
                'object_id' => $product->id,
                'object_class' => get_class($product),
                'price' => $item->price * 100,
                'vat' => 0,
                'quantity' => $item->qty,
                'total' => $lineTotal,
                'summary' => $item->name
            ]);

            $line->save();
        }

        $this->order->total = $total;
        $this->order->save();
    }

    /**
     * @param CartItem $product
     * @return Donation|\Illuminate\Database\Eloquent\Model|null|object|static
     */
    public function parseProduct(CartItem $product)
    {
        if ($product->id === 'donation') {
            $donation = new Donation([
                'amount' => (float) $product->total(),
                'first_name' => $this->details->first_name,
                'last_name' => $this->details->last_name,
                'company_name' => $this->details->company_name,
                'company_code' => $this->details->company_code,
                'email' => $this->details->email,
                'person_type' => $this->details->person_type
            ]);

            $donation->purpose()->associate($this->order)->save();

            return $donation;
        }

        return Product::where('id', $product->id)->first();
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return string
     */
    public function getPaymentType()
    {
        return $this->order->payment_type;
    }


}