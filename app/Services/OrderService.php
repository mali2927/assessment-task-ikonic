<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class OrderService
{
    public function __construct(
        protected AffiliateService $affiliateService
    ) {}

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        // Check if an order with the same order_id already exists
        $existingOrder = Order::where('order_id', $data['order_id'])->first();
        if ($existingOrder) {
            // Ignore duplicates
            return;
        }

        // Find the merchant based on the domain
        $merchant = Merchant::where('domain', $data['merchant_domain'])->first();

        // Check if an affiliate exists for the customer_email
        $affiliate = Affiliate::whereHas('user', function ($query) use ($data) {
            $query->where('email', $data['customer_email']);
        })->first();

        // If no affiliate exists, create a new one
        if (!$affiliate) {
            $affiliate = $this->affiliateService->register($merchant, $data['customer_email'], $data['customer_name'], 0.1);
        }

        // Create a new order
        $order = Order::create([
            'order_id' => $data['order_id'],
            'merchant_id' => $merchant->id,
            'affiliate_id' => $affiliate->id,
            'subtotal' => $data['subtotal_price'],
            'discount_code' => $data['discount_code'],
            'payout_status' => Order::STATUS_UNPAID,
        ]);

    }
}