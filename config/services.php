<?php

return [

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    | Stripe (test mode)
    */
    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'currency' => env('STRIPE_CURRENCY', 'rsd'),
    ],

    /*
    | Groq AI (besplatno) - https://console.groq.com/keys
    | model        = tekstualni model (jači, bolji srpski). ~1000 poziva/dan.
    |                Brža/lakša alternativa: llama-3.3-70b-versatile (~14400/dan).
    | vision_model = model koji "gleda" sliku. Alternativa: qwen/qwen3.6-27b
    */
    'groq' => [
        'key' => env('GROQ_API_KEY'),
        'model' => env('GROQ_MODEL', 'openai/gpt-oss-120b'),
        'vision_model' => env('GROQ_VISION_MODEL', 'meta-llama/llama-4-scout-17b-16e-instruct'),
    ],

];
