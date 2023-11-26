<?php

namespace App\Services;

use App\Jobs\PayoutOrderJob;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class MerchantService
{
    /**
     * Register a new user and associated merchant.
     * Hint: Use the password field to store the API key.
     * Hint: Be sure to set the correct user type according to the constants in the User model.
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return Merchant
     */
    public function register(array $data): Merchant
    {
        // Create a new user
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'type' => User::TYPE_MERCHANT, // Assuming 'merchant' is a valid user type
            'password' => bcrypt($data['api_key']), // Storing API key as the password
        ]);

        // Create a new merchant
        $merchant = Merchant::create([
            'user_id' => $user->id,
            'domain' => $data['domain'],
        ]);

        return $merchant;
    }

    /**
     * Update the user and associated merchant.
     *
     * @param User $user
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return void
     */
    public function updateMerchant(User $user, array $data)
    {
        // Update user details
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['api_key']),
        ]);

        // Update associated merchant details
        $user->merchant->update([
            'domain' => $data['domain'],
        ]);
    }

    /**
     * Find a merhcnat by their email.
     *
     * @param string $email
     * @return Merchant|null
     */
    public function findMerchantByEmail(string $email): ?Merchant
    {
        // Find the user by email
        $user = User::where('email', $email)->first();

        // If the user is found, return the associated merchant
        return $user ? $user->merchant : null;
    }

    /**
     * Pay out all of an affiliate's orders.
     *
     * @param Affiliate $affiliate
     * @return void
     */
    public function payout(Affiliate $affiliate)
    {
        // Get the unpaid orders for the affiliate
        $unpaidOrders = Order::where('affiliate_id', $affiliate->id)
            ->where('payout_status', Order::STATUS_UNPAID)
            ->get();

        // Dispatch a PayoutOrdreJob for each unpaid order
        foreach ($unpaidOrders as $order) {
            dispatch(new PayoutOrderJob($order));
        }
    }
    /**
     * Get order statistics for a merchant within a date range.
     *
     * @param Carbon $fromDate
     * @param Carbon $toDate
     * @return array
     */
    public function getOrderStatistics(Carbon $fromDate, Carbon $toDate): array
    {
        $orderStats = [];

        // Total number of orders in range
        $orderStats['count'] = DB::table('orders')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->count();

        // Amount of unpaid commissions for orders with an affiliate
        $orderStats['commission_owed'] = DB::table('orders')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->where('payout_status', '=', 'unpaid')
            ->sum('commission_owed');

        // Sum of order subtotals
        $orderStats['revenue'] = DB::table('orders')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->sum('subtotal');

        return $orderStats;
    }
}