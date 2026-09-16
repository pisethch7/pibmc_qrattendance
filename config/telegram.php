<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Telegram Bot Configuration
    |--------------------------------------------------------------------------
    |
    | Set TELEGRAM_BOT_TOKEN and TELEGRAM_CHAT_ID in your .env file.
    |
    | TELEGRAM_BOT_TOKEN — obtained from @BotFather on Telegram.
    | TELEGRAM_CHAT_ID   — the chat/group ID where notifications are sent.
    |                      Use a negative number for groups (e.g. -1001234567890).
    |
    */

    'bot_token' => env('TELEGRAM_BOT_TOKEN', ''),

    'chat_id' => env('TELEGRAM_CHAT_ID', ''),

    'enabled' => env('TELEGRAM_ENABLED', true),

    /*
    | Base URL for the Telegram Bot API.
    */
    'api_url' => 'https://api.telegram.org/bot',
];

