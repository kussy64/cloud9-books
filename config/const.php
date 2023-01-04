<?php

return [
    // CSVのヘッダーの順序
    'CSV_HEADER_NUM' => [
        'USER_ID' => [
            'NAME' => 'user_id',
            'INDEX' => 0,
        ],
        'ID' => [
            'NAME' => 'id',
            'INDEX' => 1,
        ],
        'NAME' => [
            'NAME' => 'name',
            'INDEX' => 2,
        ],
        'EMAIL' => [
            'NAME' => 'email',
            'INDEX' => 3,
        ],
        'PASSWORD' => [
            'NAME' => 'password',
            'INDEX' => 4,
        ],
        'AGE' => [
            'NAME' => 'age',
            'INDEX' => 5,
        ],
        'AUTHORITY' => [
            'NAME' => 'authority',
            'INDEX' => 6,
        ],
    ],

    // CSVインポート最大許容行数
    'CSV_IMPORT_MAX_LINE' => 50,

    // 日付フォーマット
    'DEFAULT_DATE_FORMAT' => 'Y-m-d H:i:s',
];