<?php

require_once PLUGIN_UPLOAD_REALDIR . 'NemPay/class/plg_NemPay_SC_NemPay.php';
require_once PLUGIN_UPLOAD_REALDIR . 'NemPay/class/helper/plg_NemPay_SC_Helper_NemPay.php';
require_once PLUGIN_UPLOAD_REALDIR . 'NemPay/class/util/plg_NemPay_SC_Utils.php';

/**
 * プラグインのメインクラス
 *
 * @package NemPay
 * @version 0.1
 */
class NemPay extends SC_Plugin_Base {

    /**
     * コンストラクタ
     */
    public function __construct(array $arrSelfInfo) {
        parent::__construct($arrSelfInfo);

        $arrConfig = unserialize($arrSelfInfo['free_field1']);
        /**
         * 設定値を定数として定義
         * ※キー名(小文字)→定数名(大文字)
         */
        // プレフィックス「PLG_NEMPAY_」を付与
        foreach ($arrConfig as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $key2) {
                    define('PLG_NEMPAY_'.mb_strtoupper($key.'_'.$key2), 'selected');
                }
            } else {
                define('PLG_NEMPAY_'.mb_strtoupper($key), $value);
            }
        }
        // 定数を読み込み
        require_once PLUGIN_UPLOAD_REALDIR . 'NemPay/inc/include.php';
    }

    /**
     * インストール
     * installはプラグインのインストール時に実行されます.
     * 引数にはdtb_pluginのプラグイン情報が渡されます.
     *
     * @param array $arrPlugin plugin_infoを元にDBに登録されたプラグイン情報(dtb_plugin)
     * @return void
     */
    function install($arrPlugin) {
        plg_NemPay_SC_Utils::printLog("NemPay install start.");

        // ファイルのコピー
        $copy_dir = PLUGIN_UPLOAD_REALDIR . $arrPlugin['plugin_code'] . '/copy/';
        
        // ロゴファイル
        if(copy($copy_dir.'logo.png', PLUGIN_HTML_REALDIR . $arrPlugin['plugin_code'].'/logo.png') === false);

        // 決済用ファイル
        self::copyNemPayFile($arrPlugin);
        
        // DB変更
        self::installSQL();

        plg_NemPay_SC_Utils::printLog("NemPay install end.");
    }

    /**
     * アンインストール
     * uninstallはアンインストール時に実行されます.
     * 引数にはdtb_pluginのプラグイン情報が渡されます.
     *
     * @param array $arrPlugin プラグイン情報の連想配列(dtb_plugin)
     * @return void
     */
    function uninstall($arrPlugin) {
        // 決済用ファイル
        self::deleteNemPayFile();
        
    	// 関連DB情報削除
    	self::uninstallSQL();

        // プラグインフォルダ削除
        SC_Helper_FileManager_Ex::deleteFile(PLUGIN_HTML_REALDIR . $arrPlugin['plugin_code']);
    }

    /**
     * 稼働
     * enableはプラグインを有効にした際に実行されます.
     * 引数にはdtb_pluginのプラグイン情報が渡されます.
     *
     * @param array $arrPlugin プラグイン情報の連想配列(dtb_plugin)
     * @return void
     */
    function enable($arrPlugin) {
        // nop
    }

    /**
     * 停止
     * disableはプラグインを無効にした際に実行されます.
     * 引数にはdtb_pluginのプラグイン情報が渡されます.
     *
     * @param array $arrPlugin プラグイン情報の連想配列(dtb_plugin)
     * @return void
     */
    function disable($arrPlugin) {
        //nop
    }

    /**
     * 処理の介入箇所とコールバック関数を設定
     * (プラグインインスタンス生成時に実行)
     *
     * @param SC_Helper_Plugin $objHelperPlugin プラグインヘルパーオブジェクト
     * @param  integer $priority 優先度
     * @return void
     */
    function register(SC_Helper_Plugin $objHelperPlugin, $priority) {
        $objHelperPlugin->addAction("loadClassFileChange", array(&$this, "loadClassFileChange"), $this->arrSelfInfo['priority']);
        $objHelperPlugin->addAction('LC_Page_Shopping_Complete_action_after', array(&$this, 'shoppingCompleteActionAfter'));
    }
    
    function loadClassFileChange(&$classname, &$classpath) {
        if ($classname == 'SC_SendMail_Ex') {
            $classpath = PLUGIN_UPLOAD_REALDIR . "NemPay/class/plg_NemPay_SC_SendMail.php";
            $classname = 'plg_NemPay_SC_SendMail';
        }
    }

    function shoppingCompleteActionAfter(LC_Page_Ex $objPage) {
        $objQuery =& SC_Query_Ex::getSingletonInstance();
        // 受注情報の取得
        $order_id = $_SESSION['order_id'];
        $where = 'order_id = ? AND del_flg = 0';
        $arrOrder = $objQuery->getRow('*', 'dtb_order', $where, array($order_id));
        
        //その他決済情報
        if ($arrOrder['memo01'] == 'NemPay') {
            $objHelperNem = new plg_NemPay_SC_Helper_NemPay();
            $arrOther = unserialize($arrOrder['memo02']);

            foreach ($arrOther as $other_key => $other_val) {
                if (SC_Utils_Ex::sfTrim($other_val['value']) == '') {
                    $arrOther[$other_key]['value'] = '';
                }
            }
            $filepath = $objHelperNem->getQrcodeImagePath($order_id);
            $arrOther['qr_code']['value'] = '<img src="data:image/png;base64,' . base64_encode(file_get_contents($filepath)) . '" alt="QR">';

            $objPage->arrOther = $arrOther;
        }
    }

    /**
     * プラグインインストール用DB変更
     *
     * @return void
     */
    function installSQL() {
        $objQuery =& SC_Query_Ex::getSingletonInstance();

        plg_NemPay_SC_Utils::printLog("installSQL start.");

        // 決済情報にNemPayを追加
        self::lfInsertPayment($objQuery);

        plg_NemPay_SC_Utils::printLog("installSQL end.");
    }

    /**
     * プラグインアンインストール用DB変更
     *
     * @return void
     */
    function uninstallSQL() {
        $objQuery =& SC_Query_Ex::getSingletonInstance();

        self::lfDeletePayment($objQuery);
    }

    function lfInsertPayment(&$objQuery) {
        if (!$objQuery->exists('dtb_payment', 'memo03 = ?', array('NemPay'))) {
            $arrVal = array(
                            'payment_id'     => $objQuery->nextVal('dtb_payment_payment_id'),
                            'payment_method' => 'NemPay',
                            'charge'         => 0,
                            'rank'           => NULL,
                            'fix'            => 2,
                            'status'         => 1,
                            'del_flg'        => 0,
                            'creator_id'     => $_SESSION['member_id'],
                            'create_date'    => 'CURRENT_TIMESTAMP',
                            'update_date'    => 'CURRENT_TIMESTAMP',
                            'module_path'    => MODULE_REALDIR . 'mdl_nempay/nempay.php',
                            'memo03'         => 'NemPay',
                            'rule_min'       => NULL,
                            'rule_max'       => NULL,
                            'upper_rule'     => NULL,
                        );
            $objQuery->insert('dtb_payment', $arrVal);

            // NemPayを全ての配送方法に紐づけ
            $payment_id = $objQuery->get('payment_id', 'dtb_payment', 'memo03 = ?', array('NemPay'));
            $objQuery->setGroupBy('deliv_id');
            $arrDelivList = $objQuery->select('deliv_id, max(rank)', 'dtb_payment_options');
            foreach ($arrDelivList as $deliv) {
                $deliv_id = $deliv['deliv_id'];
                $rank = $deliv['max(rank)'] + 1;
                $arrVal = array(
                                'deliv_id' => $deliv_id,
                                'payment_id' => $payment_id,
                                'rank' => $rank,
                            );
                $objQuery->insert('dtb_payment_options', $arrVal);
            }
        } else {
            plg_NemPay_SC_Utils::printLog("installSQL already exist record payment_method='NemPay' in dtb_payment.");
        }
    }

    function lfDeletePayment(&$objQuery) {
        $payment_id = $objQuery->get('payment_id', 'dtb_payment', 'memo03 = ?', array('NemPay'));
        $objQuery->delete('dtb_payment_options', 'payment_id = ?', array($payment_id));
        $objQuery->delete('dtb_payment', 'memo03 = ?', array('NemPay'));
    }

    function lfDeletePagelayout(&$objQuery) {
        $arrPageData = self::lfGetPagelayout();

        foreach ($arrPageData as $pageData) {
            $objQuery->delete('dtb_pagelayout', 'url = ?', array($pageData['file_name'] . '.php'));
        }
    }
    
    function copyNemPayFile($arrPlugin) {
        // コピー元
        $copy_dir = PLUGIN_UPLOAD_REALDIR . $arrPlugin['plugin_code'] . '/copy/';
        
        // モジュール
        SC_Utils_Ex::copyDirectory($copy_dir . 'modules/', MODULE_REALDIR);
        
        $qrcode_dir = DATA_REALDIR . '/upload/nempay'; 
        if(!file_exists($qrcode_dir)){
            mkdir($qrcode_dir, 0755);
        }
    }

    function deleteNemPayFile() {
        // モジュールフォルダ削除
        if(SC_Helper_FileManager_Ex::deleteFile(MODULE_REALDIR . 'mdl_nempay') === false) {
            plg_NemPay_SC_Utils::printLog("mdl_nempay delete dir failed.");
        }
    }

}
?>
