<?php

class plg_NemPay_SC_Utils {

    /**
     * ログを出力
     *
     * @param string $msg ログメッセージ
     */
    function printLog($msg) {
        $log_path = DATA_REALDIR . 'logs/NemPay.log';
        GC_Utils_Ex::gfPrintLog($msg, $log_path);
    }

}
?>
