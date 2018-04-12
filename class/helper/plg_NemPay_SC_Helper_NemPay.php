<?php

require_once PLUGIN_UPLOAD_REALDIR . 'NemPay/class/plg_NemPay_SC_NemPay.php';
require_once PLUGIN_UPLOAD_REALDIR . 'NemPay/class/util/plg_NemPay_SC_Utils.php';

class plg_NemPay_SC_Helper_NemPay {
    
    function isNemPayOrder($order_id) {
        $result = false;
        
        $objQuery =& SC_Query_Ex::getSingletonInstance();
        $arrRet = $objQuery->getRow('*', 'dtb_order', 'order_id = ?', array($order_id));
        if ($arrRet[PLG_NEMPAY_ORDER_CHECK] == 'NemPay') {
            $result = true;
        }
        
        return $result;
    }
    
    function createQrcodeImage($order_id, $amount, $msg) {
        require_once PLUGIN_UPLOAD_REALDIR . 'NemPay/vendor/Image/QRCode.php';
        
        $arrData = array(
            'v' => 2,
            'type' => 2,
            'data' => 
                array (
                    'addr' => PLG_NEMPAY_SELLER_ADDR,
                    'amount' => $amount * 1000000,
                    'msg' => $msg,
                    'name' => '',
            ),
        );
        
        $filepath = $this->getQrcodeImagePath($order_id);

        $qr = new Image_QRCode();
        $image = $qr->makeCode(SC_Utils_Ex::jsonEncode($arrData), 
                               array('output_type' => 'return'));
        imagepng($image, $filepath);
        imagedestroy($image);
    }
    
    function getQrcodeImagePath($order_id) {
        return DATA_REALDIR . "upload/nempay/" . $order_id . '.png';
    }
    
    function getShortHash($order_id) {
        return rtrim(base64_encode(md5(PLG_NEMPAY_SELLER_ADDR . $order_id . AUTH_MAGIC, true)), '=');  
    }
}
