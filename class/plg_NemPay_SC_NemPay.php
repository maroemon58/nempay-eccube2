<?php

require_once PLUGIN_UPLOAD_REALDIR . 'NemPay/class/util/plg_NemPay_SC_Utils.php';

class plg_NemPay_SC_NemPay {

    function getIncommingTransaction() {
        $url = PLG_NEMPAY_NIS_URL . '/account/transfers/incoming';
        $parameter = array('address' => PLG_NEMPAY_SELLER_ADDR);
        
        $result = $this->req($url, $parameter);
        
        return $result['data'];        
    }
    
    function getRate() {
        $url = PLG_NEMPAY_TICKER_URL;
        $parameter = array();
        
        $result = $this->req($url, $parameter);
        
        return $result['vwap']; 
    }
    
    function req($url, $parameters, $count = 1) {
        $qs = $this->_getParametersAsString($parameters);

        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $url . $qs);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        $r = curl_exec($c);
        $info = curl_getinfo($c);
        $errno = curl_errno($c);
        $error = curl_error($c);
        curl_close($c);
        if (!$r || $errno) {
            plg_NemPay_SC_Utils::printLog("nis error: unexpected response. url：". $url." parameter：".var_export($parameter,true));

            return false;
        }

        if ($info['http_code'] != 200) {
            plg_NemPay_SC_Utils::printLog("nis error: unexpected response. url：". $url." parameter：".var_export($parameter,true));
            
            return false;
        }

        $arrRes = json_decode($r, true);
        
        return $arrRes;
    }
    
    function _getParametersAsString(array $parameters) {
        $queryParameters = array();
        foreach ($parameters as $key => $value) {
            $queryParameters[] = $key . '=' . $this->_urlencode($value);
        }
        return '?' . implode('&', $queryParameters);
    }
    
    function _urlencode($value) {
        return str_replace('%7E', '~', rawurlencode($value));
    }
}
?>
