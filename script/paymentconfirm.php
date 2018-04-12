<?php

require_once dirname(__FILE__) . '/../../../../../html/require.php';
require_once PLUGIN_UPLOAD_REALDIR . 'NemPay/class/batch/plg_NemPay_LC_Batch_PaymentConfirm.php';

$batchObj = new plg_NemPay_LC_Batch_PaymentConfirm();
$batchObj->execute();
