<?php

class plg_NemPay_LC_Batch_PaymentConfirm extends SC_Batch{

	public function execute () {
        // 未決済受注取得
        $arrOrder = $this->getOrderPayWaitForNemPay();

        if (SC_Utils_Ex::isBlank($arrOrder)) {
            return 0;
        }
        
        // NEM受信トランザクション取得
        $objNem = new plg_NemPay_SC_NemPay();
        $arrData = $objNem->getIncommingTransaction();
		foreach ($arrData as $data) {
            $msg = pack("H*", $data['transaction']['message']['payload']);
            
            // 対象受注
            if (isset($arrOrder[$msg])) {
                $order = $arrOrder[$msg];
                
                // トランザクションチェック
                $transaction_id = $data['meta']['id'];
                if (!SC_Utils_Ex::isBlank($order[PLG_NEMPAY_HISTORY])) {
                    $transactionData = json_decode($order[PLG_NEMPAY_HISTORY], true);
                    if (isset($transactionData[$transaction_id])) {
						plg_NemPay_SC_Utils::printLog("batch error: processed transaction. transaction_id = " . $transaction_id);
                        continue;
                    }       
                } else {
                    $transactionData = array();
                }
                
                $sqlVal = array();
                
                $amount = $data['transaction']['amount'] / 1000000;
                $pre_amount = SC_Utils_Ex::isBlank($order[PLG_NEMPAY_CONFIRM_AMOUNT]) ? 0 : $order[PLG_NEMPAY_CONFIRM_AMOUNT];
                $sqlVal[PLG_NEMPAY_CONFIRM_AMOUNT] = $pre_amount + $amount;
                
                $transactionData[$transaction_id] = $amount;
                $sqlVal[PLG_NEMPAY_HISTORY] = json_encode($transactionData);

				plg_NemPay_SC_Utils::printLog("batch info: received. order_id = " . $order['order_id'] . " amount = " . $amount);

                if ($order[PLG_NEMPAY_PAYMENT_AMOUNT] <= $sqlVal[PLG_NEMPAY_CONFIRM_AMOUNT]) {
                    $sqlVal['status'] = ORDER_PRE_END;
                    $sqlVal['payment_date'] = 'CURRENT_TIMESTAMP';
					
					$this->sendPayEndMail($order['order_email'], $order['order_name01'], $order['order_name02']);
					plg_NemPay_SC_Utils::printLog("batch info: pay end. order_id = " . $order['order_id']);
                }
                
                // 更新
                $this->updateOrderForNemPay($order['order_id'], $sqlVal);				
            }
            
		}		
	}
    
    function getOrderPayWaitForNemPay() {
        $objHelperNem = new plg_NemPay_SC_Helper_NemPay();
        $objQuery =& SC_Query_Ex::getSingletonInstance();
        
        $table = 'dtb_order';
        $where = PLG_NEMPAY_ORDER_CHECK . ' = ? and status = ?';
        $arrVal = $objQuery->select('*', $table, $where , array('NemPay', ORDER_PAY_WAIT));
        
        $arrRet = array();
        foreach ($arrVal as $v) {
            $shortHash = $objHelperNem->getShortHash($v['order_id']);
            $arrRet[$shortHash] = $v;
        }
        
        return $arrRet;
    }
    
    function updateOrderForNemPay($order_id, $sqlVal) {
        $objQuery =& SC_Query_Ex::getSingletonInstance();

        $objQuery->update('dtb_order', $sqlVal, 'order_id = ?', array($order_id));
    }
	
	function sendPayEndMail($order_email, $order_name01, $order_name02) {
		// メール送信処理
        $objSendMail = new SC_SendMail_Ex();
		
		$arrInfo = SC_Helper_DB_Ex::sfGetBasisData();
        $bcc = $arrInfo['email01'];
        $from = $arrInfo['email03'];
        $error = $arrInfo['email04'];
        
		$subject = '【' . $arrInfo['shop_name'] . '】入金確認通知';
		$body = <<<__EOS__
{$order_name01} {$order_name02} 様

NEM決済の入金を確認致しました。
__EOS__;

        $objSendMail->setItem('', $subject, $body, $from, $arrInfo['shop_name'], $from, $error, $error, $bcc);
        $objSendMail->setTo($order_email, $order_name01 . ' '. $order_name02 .' 様');

    	$objSendMail->sendMail();
	}
}

?>
