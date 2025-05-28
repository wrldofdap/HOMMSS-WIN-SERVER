<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | This option controls the default mailer that is used to send all email
    | messages unless another mailer is explicitly specified when sending
    | the message. All additional mailers can be configured within the
    | "mailers" array. Examples of each type of mailer are provided.
    |
    */

    'default' => env('MAIL_MAILER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the mailers used by your application plus
    | their respective settings. Several examples have been configured for
    | you and you are free to add your own as your application requires.
    |
    | Laravel supports a variety of mail "transport" drivers that can be used
    | when delivering an email. You may specify which one you're using for
    | your mailers below. You may also add additional mailers if needed.
    |
    | Supported: "smtp", "sendmail", "mailgun", "ses", "ses-v2",
    |            "postmark", "resend", "log", "array",
    |            "failover", "roundrobin"
    |
    */

    'mailers' => [

        'smtp' => [
            'transport' => 'smtp',
            'scheme' => env('MAIL_SCHEME'),
            'url' => env('MAIL_URL'),
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 2525),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => env('MAIL_TIMEOUT', 60),
            'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST)),

            // TLS Security Options
            'verify_peer' => env('MAIL_VERIFY_PEER', true),
            'verify_peer_name' => env('MAIL_VERIFY_PEER_NAME', true),
            'allow_self_signed' => env('MAIL_ALLOW_SELF_SIGNED', false),

            // Stream context options for enhanced TLS security
            'stream' => [
                'ssl' => [
                    'verify_peer' => env('MAIL_VERIFY_PEER', true),
                    'verify_peer_name' => env('MAIL_VERIFY_PEER_NAME', true),
                    'allow_self_signed' => env('MAIL_ALLOW_SELF_SIGNED', false),
                    'cafile' => env('MAIL_CAFILE'),
                    'capath' => env('MAIL_CAPATH'),
                    'local_cert' => env('MAIL_LOCAL_CERT'),
                    'local_pk' => env('MAIL_LOCAL_PK'),
                    'passphrase' => env('MAIL_PASSPHRASE'),
                    'ciphers' => env('MAIL_CIPHERS', 'HIGH:!aNULL:!eNULL:!EXPORT:!DES:!RC4:!MD5:!PSK:!SRP:!CAMELLIA'),
                    'peer_fingerprint' => env('MAIL_PEER_FINGERPRINT'),
                    'capture_peer_cert' => env('MAIL_CAPTURE_PEER_CERT', false),
                    'capture_peer_cert_chain' => env('MAIL_CAPTURE_PEER_CERT_CHAIN', false),
                    'SNI_enabled' => env('MAIL_SNI_ENABLED', true),
                    'disable_compression' => env('MAIL_DISABLE_COMPRESSION', true),
                    'peer_name' => env('MAIL_PEER_NAME', env('MAIL_HOST')),
                ],
            ],
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'postmark' => [
            'transport' => 'postmark',
            // 'message_stream_id' => env('POSTMARK_MESSAGE_STREAM_ID'),
            // 'client' => [
            //     'timeout' => 5,
            // ],
        ],

        'resend' => [
            'transport' => 'resend',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
        ],

        'roundrobin' => [
            'transport' => 'roundrobin',
            'mailers' => [
                'ses',
                'postmark',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | You may wish for all emails sent by your application to be sent from
    | the same address. Here you may specify a name and address that is
    | used globally for all emails that are sent by your application.
    |
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', 'Example'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Email Address
    |--------------------------------------------------------------------------
    |
    | This email address will receive contact form submissions and other
    | administrative notifications.
    |
    */

    'admin_email' => env('ADMIN_EMAIL', 'admin@hommss.com'),

];
