<?php

require_once MODULE_REALDIR .  'mdl_nempay/LC_Page_Mdl_NemPay.php';

$objPage = new LC_Page_Mdl_NemPay();
register_shutdown_function(array($objPage, 'destroy'));
$objPage->init();
$objPage->process();
?>
