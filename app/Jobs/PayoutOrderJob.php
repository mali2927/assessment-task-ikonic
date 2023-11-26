<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\ApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayoutOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public Order $order
    ) {}

    /**
     * Use the API service to send a payout of the correct amount.
     * Note: The order status must be paid if the payout is successful, or remain unpaid in the event of an exception.
     *
     * @return void
     */
    public function handle(ApiService $apiService)
    {
        try {
            // Get the payout amount from the order's subtotal
            $payoutAmount = $order->subtotal;

            // Use the API service to send the payout
            $apiService->sendPayout($order->affiliate->user->email, $payoutAmount);

            // If the payout is successful, update the order status to "paid"
            $order->update([
                'payout_status' => Order::STATUS_PAID,
            ]);
        } catch (\Exception $e) {
            // Log the exception
            Log::error("Payout failed for order {$order->order_id}: {$e->getMessage()}");

            // If an exception occurs, keep the order status as "unpaid"
        }
    }
}