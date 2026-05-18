<?php

namespace App\Services\Notification;

use App\Models\Cleaner;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    /**
     * Send push notification to a cleaner
     */
    public function sendToCleaner(int $cleanerId, string $title, string $body, array $data = []): bool
    {
        try {
            $cleaner = Cleaner::with('user')->find($cleanerId);
            
            if (!$cleaner) {
                Log::warning('Cleaner not found for notification', ['cleaner_id' => $cleanerId]);
                return false;
            }

            return $this->sendToDevice(
                $cleaner->user->fcm_token ?? $cleaner->user->device_token,
                $title,
                $body,
                $data
            );
        } catch (\Exception $e) {
            Log::error('Failed to send cleaner notification', [
                'cleaner_id' => $cleanerId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send push notification to a homeowner
     */
    public function sendToHomeowner(int $homeownerId, string $title, string $body, array $data = []): bool
    {
        try {
            $homeowner = User::whereHas('homeowner', function ($q) use ($homeownerId) {
                $q->where('id', $homeownerId);
            })->first();

            if (!$homeowner) {
                Log::warning('Homeowner not found for notification', ['homeowner_id' => $homeownerId]);
                return false;
            }

            return $this->sendToDevice(
                $homeowner->fcm_token ?? $homeowner->device_token,
                $title,
                $body,
                $data
            );
        } catch (\Exception $e) {
            Log::error('Failed to send homeowner notification', [
                'homeowner_id' => $homeownerId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send notification to a specific device
     */
    private function sendToDevice(?string $token, string $title, string $body, array $data = []): bool
    {
        if (empty($token)) {
            Log::debug('No device token available, skipping push notification');
            return false;
        }

        try {
            // Firebase Cloud Messaging implementation
            if (config('services.firebase.credentials')) {
                $messaging = app('firebase.messaging');
                
                $message = $messaging->createCloudMessage()
                    ->withNotification([
                        'title' => $title,
                        'body' => $body,
                    ])
                    ->withData($data)
                    ->toToken($token);

                $messaging->send($message);
                
                Log::debug('Push notification sent', [
                    'title' => $title,
                    'data' => $data,
                ]);

                return true;
            }

            Log::debug('Firebase not configured, logging notification instead', [
                'title' => $title,
                'body' => $body,
                'data' => $data,
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('FCM send failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send bulk notification to multiple cleaners
     */
    public function sendBulkToCleaners(array $cleanerIds, string $title, string $body, array $data = []): void
    {
        $cleaners = Cleaner::with('user')
            ->whereIn('id', $cleanerIds)
            ->whereHas('user', function ($q) {
                $q->whereNotNull('fcm_token')
                  ->orWhereNotNull('device_token');
            })
            ->get();

        foreach ($cleaners as $cleaner) {
            $this->sendToCleaner($cleaner->id, $title, $body, $data);
        }
    }
}