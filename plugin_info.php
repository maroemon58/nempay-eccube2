<?php

/**
 * プラグイン の情報クラス.
 *
 * @package NemPay
 * @version 0.1
 */
class plugin_info{
    static $PLUGIN_CODE       = "NemPay";
    static $PLUGIN_NAME       = "NemPay";
    static $CLASS_NAME        = "NemPay";
    static $PLUGIN_VERSION    = "0.1";
    static $COMPLIANT_VERSION = "2.13.0 以降";
    static $AUTHOR            = "maroemon58";
    static $DESCRIPTION       = "Nem(Xem)の決済機能を提供するプラグインです。";
    static $PLUGIN_SITE_URL   = "https://github.com/maroemon58/nempay-eccube2";
    static $AUTHOR_SITE_URL   = "https://github.com/maroemon58";
    static $LICENSE           = "";
    static $HOOK_POINTS       = array(
        array("loadClassFileChange", 'loadClassFileChange'),
        array("LC_Page_Shopping_Complete_action_after", 'shoppingCompleteActionAfter'),
        array("LC_Page_Admin_Order_action_before", 'adminOrderActionBefore'),
        array("LC_Page_Admin_Order_action_after", 'adminOrderActionAfter'),
        );
}
?>
