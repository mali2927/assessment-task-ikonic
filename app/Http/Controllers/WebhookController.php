<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Pass the necessary data to the process order method
     * 
     * @param  Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        // Get the necessary data from the webhook request
        $data = $request->only([
            'order_id',
            'subtotal_price',
            'merchant_domain',
            'discount_code',
            'customer_email',
            'customer_name',
        ]);

        try {
            // Process the order using the OrderService
            $this->orderService->processOrder($data);

            return response()->json(['message' => 'Order processed successfully'], 200);
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during order processing
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}