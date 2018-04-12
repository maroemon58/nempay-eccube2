<?php

require_once CLASS_EX_REALDIR . 'page_extends/LC_Page_Ex.php';
require_once PLUGIN_UPLOAD_REALDIR . 'NemPay/class/helper/plg_NemPay_SC_Helper_NemPay.php';
require_once PLUGIN_UPLOAD_REALDIR . 'NemPay/class/util/plg_NemPay_SC_Utils.php';

/**
 * NemPayの設定クラス
 */
class LC_Page_Mdl_NemPay extends LC_Page_Ex {

    /**
     * 初期化する.
     *
     * @return void
     */
    function init() {
        parent::init();

        $template_dir = MODULE_REALDIR . "mdl_nempay/templates/";
        if (SC_Display_Ex::detectDevice() === DEVICE_TYPE_SMARTPHONE) {
            $this->tpl_mainpage = $template_dir . 'sphone/shopping/nem_pay.tpl';
        } else {
            $this->tpl_mainpage = $template_dir . 'pc/shopping/nem_pay.tpl';
        }

        $this->tpl_subtitle = "NemPayお支払い確認画面";
        $this->tpl_title = "NemPayお支払い確認";
        $this->httpCacheControl('nocache');
    }

    /**
     * プロセス.
     *
     * @return void
     */
    function process() {
        $this->action();
        $this->sendResponse();
    }

    /**
     * Page のアクション.
     *
     * @return void
     */
    function action() {
        $objNem = new plg_NemPay_SC_NemPay();
        $objHelperNem = new plg_NemPay_SC_Helper_NemPay();
        $objPurchase = new SC_Helper_Purchase_Ex();

        // 受注IDを取得
        $order_id = $_SESSION['order_id'];
        if (SC_Utils_Ex::isBlank($order_id)) {
            SC_Utils_Ex::sfDispSiteError(PAGE_ERROR, '', true);
            return;
        }
        // 受注情報を取得
        $arrOrder = $objPurchase->getOrder($order_id);
        if ($arrOrder['status'] != ORDER_PENDING) {
            switch ($arrOrder['status']) {
                case ORDER_NEW:
                case ORDER_PRE_END:
                case ORDER_PAY_WAIT:
                case ORDER_CANCEL:
                    SC_Response_Ex::sendRedirect(SHOPPING_COMPLETE_URLPATH);
                    SC_Response_Ex::actionExit();
                    break;
                default:
                    break;
            }
        }

        $objQuery =& SC_Query_Ex::getSingletonInstance();
        $objQuery->begin();

        switch ($this->getMode()) {
            case 'return':
                $objPurchase->rollbackOrder($order_id, ORDER_CANCEL, true);
                $objQuery->commit();
                SC_Response_Ex::sendRedirect(SHOPPING_CONFIRM_URLPATH);
                SC_Response_Ex::actionExit();
                break;
            case 'confirm':
                $msg = $objHelperNem->getShortHash($order_id);
                // QRコード作成
                $objHelperNem->createQrcodeImage($order_id, $arrOrder[PLG_NEMPAY_PAYMENT_AMOUNT], $msg);
                
                // 更新情報
                $sqlVal = array();
                $sqlVal[PLG_NEMPAY_ORDER_CHECK] = 'NemPay';
                $sqlVal['memo02'] = $this->setMail($arrOrder[PLG_NEMPAY_PAYMENT_AMOUNT], $msg);
                
                // 受注情報を更新
                $objPurchase->sfUpdateOrderStatus($order_id, ORDER_PAY_WAIT, null, null, $sqlVal);

                // 受注メール送信
                $objHelperMail = new SC_Helper_Mail_Ex();
                $objHelperMail->sfSendOrderMail($order_id, 1);
                $objQuery->commit();
                // 受注完了ページへ遷移
                plg_NemPay_SC_Utils::printLog("NemPay end. order complete. order_id=".$order_id);
                SC_Response_Ex::sendRedirect(SHOPPING_COMPLETE_URLPATH);
                SC_Response_Ex::actionExit();
                break;
            default:
                $xem_jpy = $objNem->getRate();
                if (SC_Utils_Ex::isBlank($xem_jpy)) {
                    plg_NemPay_SC_Utils::printLog("ERROR: cannot get xem_jpy. order_id=".$order_id);
                    $err_msg = '決済情報の取得に失敗しました。もう一度決済を試みてください。';
                    SC_Utils_Ex::sfDispSiteError(FREE_ERROR_MSG, '', false, $err_msg);
                    SC_Response_Ex::actionExit();
                }
                $xem_amount = round($arrOrder['payment_total'] / $xem_jpy, 3);
                
                $sqlVal = array(
                    PLG_NEMPAY_RATE => $xem_jpy,
                    PLG_NEMPAY_PAYMENT_AMOUNT => $xem_amount,
                );
                $objQuery->update('dtb_order', $sqlVal, 'order_id = ?', array($order_id));
                $objQuery->commit();
        }
        
        $this->arrOrder = $arrOrder;
        $this->xem_jpy = $xem_jpy;
        $this->xem_amount = $xem_amount;
    }
    
    function setMail($amount, $msg) {
        $arrNemMail = array();
        
        $arrNemMail['title']['name'] = 'NEM決済についてのご連絡';
        $arrNemMail['title']['value'] = true;
        $arrNemMail['qr_explain_title']['value'] = '【お支払いについてのご説明】';
        $arrNemMail['qr_explain']['value'] = <<< __EOS__
お客様の注文はまだ決済が完了していません。
お支払い情報に記載されている支払い先アドレスに指定の金額とメッセージを送信してください。
送金から一定時間経過後、本サイトに反映され決済が完了します。

※NanoWalletでQRコードをスキャンするとお支払い情報が読み込まれます。
※メッセージが誤っている場合、注文に反映されませんのご注意ください。
※送金金額が受付時の金額に満たない場合、決済は完了されません。複数回送金された場合は合算した金額で判定されます。

__EOS__;
        $arrNemMail['pay_info']['value'] = '【お支払い情報】';
        $arrNemMail['address']['name'] = '支払先アドレス';
        $arrNemMail['address']['value'] = PLG_NEMPAY_SELLER_ADDR;
        $arrNemMail['amount']['name'] = 'お支払い金額';
        $arrNemMail['amount']['value'] = $amount . ' XEM';
        $arrNemMail['message']['name'] = 'メッセージ';
        $arrNemMail['message']['value'] = $msg;
        
        return serialize($arrNemMail);
    }

    /**
     * デストラクタ.
     *
     * @return void
     */
    function destroy() {
        parent::destroy();
    }
}
?>
