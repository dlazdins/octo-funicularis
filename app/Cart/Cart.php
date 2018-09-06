<?php

namespace App\Cart;

use App\Products\Product;
use Illuminate\Http\Request;
use Cart as ShoppingCart;
use Illuminate\Support\Collection;
use Validator;

class Cart
{
    public function __construct()
    {

    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function execute(Request $request)
    {
        $method = $request->get('action');
        if (method_exists(self::class, $method)) {
            return $this->$method($request);
        }
        return false;
    }

    /**
     * @param Request $request
     * @return bool|\Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function add(Request $request)
    {
        Validator::make([
            'productId' => $request->get('productId'),
            'quantity' => $request->get('quantity')
        ], [
            'productId' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer',
        ])->validate();

        $product = Product::find($request->get('productId'));
        $quantity = intval($request->get('quantity'));


        if ($product->amount == 0) {
            return response()->json([
                'count' => $this->getCountString()
            ]);
        } elseif ($quantity >= $product->amount) {
            $item = ShoppingCart::add($product, $quantity);
            $item->setQuantity($product->amount);
        } else {
            $item = ShoppingCart::add($product, $quantity);
        }

        $item->setTaxRate(0);

        return response()->json([
            'count' => $this->getCountString()
        ]);
    }

    /**
     * @return string|null
     */
    public function getDonationRowId()
    {
        $donation = ShoppingCart::search(function ($item){
            return $item->id == 'donation';
        })->first();

        return !empty($donation->rowId) ? $donation->rowId : null;
    }

    /**
     * @return \Gloudemans\Shoppingcart\CartItem|null
     */
    public function getDonation()
    {
        if (!empty($rowId = $this->getDonationRowId())) {
            return ShoppingCart::get($rowId);
        }
        return null;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function addDonation(Request $request)
    {
        $amount = $request->get('donation');
        $this->removeExistingDonation();

        if (!empty($amount)) {
            Validator::make([
                'donation' => $amount,
            ], [
                'donation' => 'integer|min:1',
            ])->validate();

            $donation = ShoppingCart::add('donation', trans('order.description.shopDonation'), 1, $amount);
            $donation->setTaxRate(0);
        }

        return response()->json([
            'count' => $this->getCountString(),
            'total' => $this->getTotalString()
        ]);
    }

    /**
     * @return void
     */
    public function removeExistingDonation()
    {
        if (!empty($rowId = $this->getDonationRowId())) {
            ShoppingCart::remove($rowId);
        }
    }

    /**
     * @return float|null
     */
    public function getDonationAmount()
    {
        if (!empty($donation = $this->getDonation())) {
            return $donation->price;
        }
        return null;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function remove(Request $request)
    {
        Validator::make([
            'rowId' => $request->get('rowId'),
        ], [
            'rowId' => 'required|alpha_num',
        ])->validate();

        ShoppingCart::remove($request->get('rowId'));

        return response()->json([
            'count' => $this->getCountString(),
            'total' => $this->getTotalString(),
            'empty' => ShoppingCart::count() == 0
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request)
    {
        Validator::make([
            'rowId' => $request->get('rowId'),
            'quantity' => $request->get('quantity')
        ], [
            'rowId' => 'required|alpha_num',
            'quantity' => 'required|numeric|min:1',
        ])->validate();

        ShoppingCart::update($request->get('rowId'), $request->get('quantity'));

        return response()->json([
            'count' => $this->getCountString(),
            'total' => $this->getTotalString(),
        ]);
    }

    /**
     * @return array|\Illuminate\Contracts\Translation\Translator|null|string
     */
    public function getCountString()
    {
        return trans_choice('shop.products_in_cart', $this->getProducts()->sum('qty'));
    }

    /**
     * @return string
     */
    public function getTotalString()
    {
        return '€' . ShoppingCart::total(2, '.', '');
    }

    /**
     * @return Collection
     */
    public function getContent()
    {
        return ShoppingCart::content();
    }

    /**
     * @return Collection
     */
    public function getProducts()
    {
        return ShoppingCart::content()->filter(function ($item) {
            return $item->id != 'donation';
        });
    }

    /**
     * @param integer|float $price
     * @param bool $divide
     * @return string
     */
    public static function pf($price, $divide = false)
    {
        if ($divide) $price = $price / 100;
        return '€' . number_format($price, 2, '.', ' ');
    }
}