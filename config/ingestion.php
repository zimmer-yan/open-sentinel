<?php

return [
    'url' => env('INGESTION_URL', env('APP_URL')),

    'max_bytes' => (int) env(
        'INGESTION_MAX_BYTES',
        1_048_576,
    ),

    'max_items' => (int) env('INGESTION_MAX_ITEMS', 100),

    'max_message_bytes' => (int) env(
        'INGESTION_MAX_MESSAGE_BYTES',
        16_384,
    ),

    'max_log_batch_size' => (int) env(
        'INGESTION_MAX_LOG_BATCH_SIZE',
        500,
    ),

    'queue' => env('INGESTION_QUEUE', 'ingestion'),
];
