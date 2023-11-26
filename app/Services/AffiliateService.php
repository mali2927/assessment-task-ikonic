<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    ) {}

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        // Create a new user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'type' => 'affiliate', 

            'password' => bcrypt('password'), // Change 'password' to the desired initial password
        ]);

        // Associate the user with the given merchant
        $user->merchants()->attach($merchant);

        // Create a new afiliate recodr
        $affiliate = Affiliate::create([
            'user_id' => $user->id,
            'merchant_id' => $merchant->id,
            'commission_rate' => $commissionRate,
            'discount_code' => $this->generateDiscountCode(),
        ]);


        return $affiliate;
    }

    /**
     * Generate a unique discount code for the affiliate.
     *
     * @return string
     */
    protected function generateDiscountCode(): string
    {
        // Implement your logic to generate a unique discount code
        // You can use a combination of letters, numbers, etc.
        return uniqid('affiliate_', true);
    }

    /**
     * Send an email to the newly created affiliate.
     *
     * @param  User $user
     * @param  float $commissionRate
     * @return void
     */

}