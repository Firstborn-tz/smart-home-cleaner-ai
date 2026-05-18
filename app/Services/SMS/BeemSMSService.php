<?php

namespace App\Services\SMS;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BeemSMSService
{
    private string $apiKey;
    private string $secretKey;
    private string $baseUrl;
    private string $senderId;

    public function __construct()
    {
        $this->apiKey = '451c29dccbe9972b';
        $this->secretKey = 'NjY5ZGQ1MTFhMDFmY2VmMzY0OTljMDQ4NGZkM2JkOGUzYzg5ZjYyNWYxODQ2Nzk4MDM5ZmIxZTRhNjk1NGFkYw==';
        $this->baseUrl = 'https://apisms.beem.africa/v1';
        $this->senderId = 'SmartClean';
    }

    /**
     * Send SMS to a single recipient
     */
    public function sendSMS(string $phone, string $message): bool
    {
        try {
            // Clean phone number - ensure it starts with 255
            $phone = $this->formatPhoneNumber($phone);

            $response = Http::withBasicAuth($this->apiKey, $this->secretKey)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($this->baseUrl . '/send', [
                    'source_addr' => $this->senderId,
                    'schedule_time' => '',
                    'encoding' => 0,
                    'message' => $message,
                    'recipients' => [
                        [
                            'recipient_id' => 1,
                            'dest_addr' => $phone,
                        ]
                    ],
                ]);

            $data = $response->json();

            if ($response->successful() && ($data['code'] ?? 0) === 100) {
                Log::info('SMS sent successfully', [
                    'phone' => $phone,
                    'message_id' => $data['request_id'] ?? null,
                ]);
                return true;
            }

            Log::warning('Beem SMS failed', [
                'phone' => $phone,
                'response' => $data,
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('Beem SMS error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send SMS to multiple recipients
     */
    public function sendBulkSMS(array $phones, string $message): bool
    {
        $recipients = [];
        foreach ($phones as $index => $phone) {
            $recipients[] = [
                'recipient_id' => $index + 1,
                'dest_addr' => $this->formatPhoneNumber($phone),
            ];
        }

        try {
            $response = Http::withBasicAuth($this->apiKey, $this->secretKey)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->baseUrl . '/send', [
                    'source_addr' => $this->senderId,
                    'schedule_time' => '',
                    'encoding' => 0,
                    'message' => $message,
                    'recipients' => $recipients,
                ]);

            $data = $response->json();

            if ($response->successful() && ($data['code'] ?? 0) === 100) {
                Log::info('Bulk SMS sent', ['count' => count($phones)]);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Beem Bulk SMS error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check account balance
     */
    public function getBalance(): ?array
    {
        try {
            $response = Http::withBasicAuth($this->apiKey, $this->secretKey)
                ->get($this->baseUrl . '/vendors/balance');

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('Beem balance check error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Format phone number to international format (255XXXXXXXXX)
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // If starts with 0, replace with 255
        if (strlen($phone) === 10 && str_starts_with($phone, '0')) {
            $phone = '255' . substr($phone, 1);
        }

        // If starts with 255, keep as is
        if (strlen($phone) === 12 && str_starts_with($phone, '255')) {
            return $phone;
        }

        // If 9 digits, add 255
        if (strlen($phone) === 9) {
            return '255' . $phone;
        }

        return $phone;
    }
}