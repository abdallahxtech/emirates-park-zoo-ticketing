<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CyberSourceService
{
    protected string $accessKey;
    protected string $profileId;
    protected string $secretKey;
    protected string $merchantId;
    protected string $apiUrl;

    public function __construct()
    {
        $this->accessKey = config('services.cybersource.access_key');
        $this->profileId = config('services.cybersource.profile_id');
        $this->secretKey = config('services.cybersource.secret_key');
        $this->merchantId = config('services.cybersource.merchant_id');
        $this->apiUrl = config('services.cybersource.api_url', 'https://testsecureacceptance.cybersource.com/pay');
    }

    /**
     * Generate the form fields for Secure Acceptance Hosted Checkout
     */
    public function getPaymentFields(Booking $booking): array
    {
        $params = [
            'access_key' => $this->accessKey,
            'profile_id' => $this->profileId,
            'transaction_uuid' => uniqid(),
            'signed_field_names' => 'access_key,profile_id,transaction_uuid,signed_field_names,unsigned_field_names,signed_date_time,locale,transaction_type,reference_number,amount,currency,bill_to_forename,bill_to_surname,bill_to_email,bill_to_phone,bill_to_address_line1,bill_to_address_city,bill_to_address_country',
            'unsigned_field_names' => '',
            'signed_date_time' => gmdate("Y-m-d\TH:i:s\Z"),
            'locale' => 'en',
            'transaction_type' => 'sale',
            'reference_number' => $booking->booking_id,
            'amount' => number_format($booking->total_amount, 2, '.', ''),
            'currency' => $booking->currency ?? 'AED',
            'bill_to_forename' => $this->splitName($booking->customer_name)[0],
            'bill_to_surname' => $this->splitName($booking->customer_name)[1],
            'bill_to_email' => $booking->customer_email,
            'bill_to_phone' => $booking->customer_phone,
            'bill_to_address_line1' => 'Emirates Park Zoo', // Default or from booking if captured
            'bill_to_address_city' => 'Abu Dhabi',
            'bill_to_address_country' => 'AE',
        ];

        $params['signature'] = $this->sign($params);

        return $params;
    }

    /**
     * Verify the signature from a CyberSource callback/webhook
     */
    public function verifySignature(array $data): bool
    {
        if (!isset($data['signed_field_names']) || !isset($data['signature'])) {
            Log::warning('CyberSource verification failed: Missing fields', $data);
            return false;
        }

        $calculatedSignature = $this->sign($data);
        
        $isValid = hash_equals($calculatedSignature, $data['signature']);

        if (!$isValid) {
            Log::warning('CyberSource verification failed: Invalid signature', [
                'received' => $data['signature'],
                'calculated' => $calculatedSignature
            ]);
        }

        return $isValid;
    }

    /**
     * Sign the parameters using HMAC-SHA256
     */
    protected function sign(array $params): string
    {
        $signedFieldNames = explode(',', $params['signed_field_names']);
        $dataToSign = [];

        foreach ($signedFieldNames as $field) {
            $dataToSign[] = $field . "=" . ($params[$field] ?? '');
        }

        $stringToSign = implode(',', $dataToSign);

        return base64_encode(hash_hmac('sha256', $stringToSign, $this->secretKey, true));
    }

    /**
     * Helper to split full name into first and last
     */
    protected function splitName(string $fullName): array
    {
        $parts = explode(' ', trim($fullName), 2);
        return [
            $parts[0] ?? 'Guest',
            $parts[1] ?? 'User'
        ];
    }
}
