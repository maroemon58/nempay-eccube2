<?php
// 本番環境
if (PLG_NEMPAY_PROD_MODE == 1) {
    define('PLG_NEMPAY_NIS_URL', 'http://alice3.nem.ninja:7890');
// テスト環境
} else {
    define('PLG_NEMPAY_NIS_URL', 'http://104.128.226.60:7890');
}

// レート取得先URL
define('PLG_NEMPAY_TICKER_URL', 'https://api.zaif.jp/api/1/ticker/xem_jpy');

// NemPay決済情報保持カラム(dtb_order)
define('PLG_NEMPAY_ORDER_CHECK', 'memo01');
define('PLG_NEMPAY_RATE', 'memo03');
define('PLG_NEMPAY_PAYMENT_AMOUNT', 'memo04');
define('PLG_NEMPAY_CONFIRM_AMOUNT', 'memo05');
define('PLG_NEMPAY_HISTORY', 'memo06');

