<?php

namespace App\Helpers;

use App\Services\SMS\BeemSMSService;

class SMSHelper
{
    /**
     * Send registration confirmation to cleaner
     */
    public static function sendRegistrationConfirmation(string $phone, string $name): void
    {
        $sms = new BeemSMSService();
        $message = "Hello {$name}, your SmartClean AI cleaner registration has been received! We will review your application within 24-48 hours. You will be notified once approved. Thank you!";
        $sms->sendSMS($phone, $message);
    }

    /**
     * Send approval notification to cleaner
     */
    public static function sendApprovalNotification(string $phone, string $name): void
    {
        $sms = new BeemSMSService();
        $message = "Congratulations {$name}! Your SmartClean AI cleaner registration has been APPROVED. You can now login and start receiving bookings. Welcome aboard!";
        $sms->sendSMS($phone, $message);
    }

    /**
     * Send rejection notification to cleaner
     */
    public static function sendRejectionNotification(string $phone, string $name, string $reason): void
    {
        $sms = new BeemSMSService();
        $message = "Hello {$name}, your SmartClean AI registration was not approved. Reason: {$reason}. You may reapply with updated information. Thank you.";
        $sms->sendSMS($phone, $message);
    }

    /**
     * Send booking confirmation to homeowner
     */
    public static function sendBookingConfirmation(string $phone, string $name, string $bookingNumber): void
    {
        $sms = new BeemSMSService();
        $message = "Hello {$name}, your cleaning service has been booked! Booking #{$bookingNumber}. Your cleaner will arrive soon. Track your booking on SmartClean AI.";
        $sms->sendSMS($phone, $message);
    }

    /**
     * Send booking alert to cleaner
     */
    public static function sendNewBookingAlert(string $phone, string $name, string $amount): void
    {
        $sms = new BeemSMSService();
        $message = "New booking alert! You have a new cleaning request worth TZS {$amount}. Login to SmartClean AI to accept or decline. Quick response recommended!";
        $sms->sendSMS($phone, $message);
    }
}