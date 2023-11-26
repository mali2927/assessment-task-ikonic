<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MerchantController extends Controller
{
    protected $merchantService;

    public function __construct(
        MerchantService $merchantService
    ) {
        $this->merchantService = $merchantService;
    }

    /**
     * Useful order statistics for the merchant API.
     * 
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {
        // Validate the request parameters
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);

        $fromDate = Carbon::parse($request->input('from'));
        $toDate = Carbon::parse($request->input('to'))->endOfDay();

        // Retrieve order statistics using the MerchantService
        $orderStats = $this->merchantService->getOrderStatistics($fromDate, $toDate);

        return response()->json($orderStats, 200);
    }
}