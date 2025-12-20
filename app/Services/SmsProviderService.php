<?php

namespace App\Services;

use App\Models\SmsToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;

class SmsProviderService
{
    protected function tokenRecord()
    {
        return SmsToken::orderBy('id', 'desc')->first();
    }

    public function getValidToken(): string
    {
        $record = $this->tokenRecord();

        if ($record && $record->expires_at && Carbon::now()->lt($record->expires_at)) {
            return $record->token;
        }

        if ($record) {
            return $this->refreshToken();
        }

        return $this->fetchNewToken();
    }

    protected function fetchNewToken(): string
    {
        $url = env('SMS_URL') . '/auth/login';

        $response = Http::post($url, [
            'email' => env('SMS_EMAIL'),
            'password' => env('SMS_PASSWORD'),
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('Eskiz login failed: ' . $response->body());
        }

        $data = $response->json();
        $token = $data['data']['token'] ?? null;

        if (!$token) {
            throw new \RuntimeException('Eskiz token missing in response: ' . json_encode($data));
        }

        $expiresAt = now()->addDays((int) env('SMS_TOKEN_LIFETIME_DAYS', 20));


        SmsToken::create([
            'token' => $token,
            'expires_at' => $expiresAt,
        ]);

        return $token;
    }

    public function refreshToken(): string
    {
        $url = env('SMS_URL') . '/auth/refresh';
        $record = $this->tokenRecord();

        if (!$record || !$record->token) {
            return $this->fetchNewToken();
        }

        if ($record->expires_at && now()->lt($record->expires_at)) {
            return $record->token;
        }

        $response = Http::withToken($record->token)->post($url);

        if ($response->failed()) {
            return $this->fetchNewToken();
        }

        $data = $response->json();
        $token = $data['data']['token'] ?? null;

        if (!$token) {
            return $this->fetchNewToken();
        }

        $expiresAt = now()->addDays((int) env('SMS_TOKEN_LIFETIME_DAYS', 20));


        SmsToken::create([
            'token' => $token,
            'expires_at' => $expiresAt,
        ]);

        return $token;
    }

    public function sendSms(string $phoneNumber, string $text)
    {
        $token = $this->getValidToken();
        $url = env('SMS_URL') . '/message/sms/send';

        $payload = [
            'mobile_phone' => $phoneNumber,
            'message' => $text,
            'from' => env('SMS_ORIGINATOR', '4546'),
        ];

        $response = Http::withToken($token)->post($url, $payload);

        // If token expired → refresh and retry once
        if ($response->status() === 401) {
            $token = $this->refreshToken();
            $response = Http::withToken($token)->post($url, $payload);
        }

        return $response;
    }
}
