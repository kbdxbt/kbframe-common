<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Supported channels.
    |--------------------------------------------------------------------------
    |
    | Here you may specify a list of the supported channels.
    |
    */
    'channels' => [
        // Bark
        'bark' => [
            'driver' => 'bark',
            'config' => [
                'base_uri' => env('NOTIFY_BARK_BASE_URI'),
                'token' => env('NOTIFY_BARK_TOKEN'),
                'group' => env('NOTIFY_BARK_GROUP', config('app.name')),
            ],
        ],

        // Chanify
        'chanify' => [
            'driver' => 'chanify',
            'config' => [
                'base_uri' => env('NOTIFY_CHANIFY_BASE_URI'),
                'token' => env('NOTIFY_CHANIFY_TOKEN'),
            ],
        ],

        // 钉钉群机器人
        'dingTalk' => [
            'driver' => 'dingTalk',
            'config' => [
                'token' => env('NOTIFY_DINGTALK_TOKEN'),
                'secret' => env('NOTIFY_DINGTALK_SECRET'),
                'keyword' => env('NOTIFY_DINGTALK_KEYWORD'),
            ],
        ],

        // Discord
        'discord' => [
            'driver' => 'discord',
            'config' => [
                'webhook_url' => env('NOTIFY_DISCORD_WEBHOOK_URL'),
            ],
        ],

        // 飞书群机器人
        'feiShu' => [
            'driver' => 'feiShu',
            'config' => [
                'token' => env('NOTIFY_FEISHU_TOKEN'),
                'secret' => env('NOTIFY_FEISHU_SECRET'),
                'keyword' => env('NOTIFY_FEISHU_KEYWORD'),
            ],
        ],

        // Log
        'log' => [
            'driver' => 'log',
            'config' => [
                'channel' => env('NOTIFY_LOG_CHANNEL', config('logging.default', 'stack')),
                'level' => env('NOTIFY_LOG_LEVEL', 'error'),
            ],
        ],

        // 邮件
        // 安装依赖 composer require symfony/mailer -vvv
        'mail' => [
            'driver' => 'mailer',
            'config' => [
                'dsn' => env('NOTIFY_MAIL_DSN'),
            ],
        ],

        // Push Deer
        'pushDeer' => [
            'driver' => 'pushDeer',
            'config' => [
                'token' => env('NOTIFY_PUSHDEER_TOKEN'),
                'base_uri' => env('NOTIFY_PUSHDEER_BASE_URI'),
            ],
        ],

        // QQ Channel Bot
        // 安装依赖 composer require textalk/websocket -vvv
        'qqChannelBot' => [
            'driver' => 'qqChannelBot',
            'config' => [
                'appid' => env('NOTIFY_QQCHANNELBOT_APPID'),
                'token' => env('NOTIFY_QQCHANNELBOT_TOKEN'),
                'channel_id' => env('NOTIFY_QQCHANNELBOT_CHANNEL_ID'),
                'environment' => env('NOTIFY_QQCHANNELBOT_ENVIRONMENT', 'production'),
            ],
        ],

        // Server 酱
        'serverChan' => [
            'driver' => 'serverChan',
            'config' => [
                'token' => env('NOTIFY_SERVERCHAN_TOKEN'),
            ],
        ],

        // Slack
        'slack' => [
            'driver' => 'slack',
            'config' => [
                'webhook_url' => env('NOTIFY_SLACK_WEBHOOK_URL'),
                'channel' => env('NOTIFY_SLACK_CHANNEL'),
            ],
        ],

        // Telegram
        'telegram' => [
            'driver' => 'telegram',
            'config' => [
                'token' => env('NOTIFY_TELEGRAM_TOKEN'),
                'chat_id' => env('NOTIFY_TELEGRAM_CHAT_ID'),
            ],
        ],

        // 企业微信群机器人
        'weWork' => [
            'driver' => 'weWork',
            'config' => [
                'token' => env('NOTIFY_WEWORK_TOKEN'),
            ],
        ],

        // 息知
        'xiZhi' => [
            'driver' => 'xiZhi',
            'config' => [
                'type' => env('NOTIFY_XIZHI_TYPE', 'single'), // [single, channel]
                'token' => env('NOTIFY_XIZHI_TOKEN'),
            ],
        ],
    ],
];
