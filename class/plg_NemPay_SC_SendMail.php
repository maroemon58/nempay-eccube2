<?php

require_once CLASS_REALDIR . 'SC_SendMail.php';
require_once PLUGIN_UPLOAD_REALDIR . 'NemPay/class/helper/plg_NemPay_SC_Helper_NemPay.php';
require_once PLUGIN_UPLOAD_REALDIR . 'NemPay/class/plg_NemPay_SC_NemPay.php';
require_once PLUGIN_UPLOAD_REALDIR . 'NemPay/class/util/plg_NemPay_SC_Utils.php';

class plg_NemPay_SC_SendMail extends SC_SendMail {
    
    public function sendMail($isHtml = false){
        $objHelperNem = new plg_NemPay_SC_Helper_NemPay();
        
        $order_id = $_SESSION['order_id'];

        $is_nem = $objHelperNem->isNemPayOrder($order_id);
        if ($is_nem) {
            $filepath = $objHelperNem->getQrcodeImagePath($order_id);
            $result = $this->sendAttachMail($filepath);
        } else {
            $result = parent::sendMail();
        }
        
        return $result;
    }
    
    /**
     * 添付ファイル付きメールを送信する。
     *
     * @param string $filepath
     * @return boolean
     */
    public function sendAttachMail($filepath) {
        // 添付ファイルをセットする。
        $objMailMime = new Mail_mime();
        
        //MIMEタイプの取得
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $filepath);
        finfo_close($finfo);
        if ($mime_type) {
            $objMailMime->addAttachment($filepath, $mime_type);
        } else {
            GC_Utils_Ex::gfPrintLog('添付ファイルの取得に失敗しました:' . $filePath);
        }
        
        // 本文のテキストをセットする。
        $objMailMime->setTxtBody($this->body);
        $bodyParam = array(
            "head_charset" => "ISO-2022-JP",
            "text_charset" => "ISO-2022-JP"
        );
        $body = $objMailMime->get($bodyParam);
        // ヘッダーをセットする。
        $header = $isHtml ? $this->getHTMLHeader() : $this->getTEXTHeader();
        $header = $objMailMime->headers($header);
        // メール送信
        $recip = $this->getRecip();
        $result = $this->objMail->send($recip, $header, $body);
        if (PEAR::isError($result)) {
            // XXX Windows 環境では SJIS でメッセージを受け取るようなので変換する。
            $msg = mb_convert_encoding($result->getMessage(), CHAR_CODE, 'auto');
            $msg = 'メール送信に失敗しました。[' . $msg . ']';
            trigger_error($msg, E_USER_WARNING);
            GC_Utils_Ex::gfDebugLog($header);
            return false;
        }
        return true;
    }
}