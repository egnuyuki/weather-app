<?php

return [
    // CORSを適用するパス
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    // 許可するHTTPメソッド
    'allowed_methods' => ['*'],

    // 許可する送信元（フロントエンドのドメイン）
    'allowed_origins' => ['http://localhost:3000'], // 本番環境では指定推奨: ['https://example.com']

    // 正規表現による送信元制限
    'allowed_origins_patterns' => [],

    // 許可するHTTPヘッダー
    'allowed_headers' => ['*'],

    // ブラウザに公開するヘッダー
    'exposed_headers' => [],

    // プリフライトリクエストのキャッシュ時間
    'max_age' => 0,

    // クッキーや認証情報を許可するか
    'supports_credentials' => false,
];
