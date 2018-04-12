<?php

require_once PLUGIN_UPLOAD_REALDIR .  'NemPay/LC_Page_Plugin_NemPay_Config.php';

$objPage = new LC_Page_Plugin_NemPay_Config();
register_shutdown_function(array($objPage, 'destroy'));

$objPage->init();
$objPage->process();
?>
