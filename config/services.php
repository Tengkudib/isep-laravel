<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Chatbot iSEP Tutor (Google Gemini API)
    'gemini' => [
        'key' => env('GEMINI_API_KEY', ''),
        'model' => env('GEMINI_MODEL', 'gemini-3.5-flash-lite'),
    ],

    // Pelaksana kod Java & PHP untuk "Cuba Sendiri" (API Judge0). Instans awam percuma secara lalai;
    // tukar ke instans sendiri / RapidAPI untuk kegunaan besar (CODE_RUNNER_KEY dihantar sebagai X-Auth-Token).
    'code_runner' => [
        'url' => env('CODE_RUNNER_URL', 'https://ce.judge0.com'),
        'key' => env('CODE_RUNNER_KEY', ''),
    ],

];
