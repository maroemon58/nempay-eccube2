<?php

require_once CLASS_EX_REALDIR . 'page_extends/admin/LC_Page_Admin_Ex.php';

/**
 * NemPayの設定クラス
 */
class LC_Page_Plugin_NemPay_Config extends LC_Page_Admin_Ex {

    var $arrPlugin = array();

    /**
     * 初期化する.
     *
     * @return void
     */
    function init() {
        parent::init();
        $this->tpl_mainpage = PLUGIN_UPLOAD_REALDIR ."NemPay/templates/admin/config.tpl";
        $this->tpl_subtitle = "NemPay コンフィグ画面";

        $this->arrProdMode = array('テスト環境', '本番環境');
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
        $objFormParam = new SC_FormParam_Ex();
        $this->lfInitParam($objFormParam);
        $objFormParam->setParam($_POST);
        $objFormParam->convParam();

        $this->arrPlugin = SC_Plugin_Util_Ex::getPluginByPluginCode('NemPay');

        switch ($this->getMode()) {
        case 'edit':
            $arrForm = $objFormParam->getHashArray();
            $this->arrErr = $this->lfCheckError($objFormParam);
            // エラーなしの場合にはデータを更新
            if (count($this->arrErr) == 0) {
                // データ更新
                $this->registerPluginConfig($arrForm);

                $this->tpl_onload = "alert('登録が完了しました。');";
            }
            break;
        default:
            // プラグイン情報を取得.
            $arrForm = $this->getPluginConfig();
            break;
        }
        $this->arrForm = $arrForm;
        $this->setTemplate($this->tpl_mainpage);
    }

    /**
     * デストラクタ.
     *
     * @return void
     */
    function destroy() {
        parent::destroy();
    }

    /**
     * パラメーター情報の初期化
     *
     * @param object $objFormParam SC_FormParamインスタンス
     * @return void
     */
    function lfInitParam(&$objFormParam) {
        $objFormParam->addParam('環境切り替え', 'prod_mode', INT_LEN, 'n', array('SELECT_CHECK', 'MAX_LENGTH_CHECK'));
        $objFormParam->addParam('出品者アカウント', 'seller_addr', STEXT_LEN, 'KVa', array('EXIST_CHECK', 'NO_SPTAB', 'MAX_LENGTH_CHECK'));
    }

    /**
     * 入力内容のチェックを行う.
     *
     * @param  SC_FormParam $objFormParam SC_FormParam インスタンス
     * @return array
     */
    function lfCheckError($objFormParam) {
        $objErr = new SC_CheckError_Ex($objFormParam->getHashArray());
        $objErr->arrErr = $objFormParam->checkError();

        return $objErr->arrErr;

    }

    /**
     * プラグイン設定情報を登録
     *
     * @param array $arrConfig 入力値
     */
    function registerPluginConfig($arrConfig) {
        $plugin_id = $this->arrPlugin['plugin_id'];

        if ($this->isInstallPlugin()) {
            $objQuery =& SC_Query_Ex::getSingletonInstance();
            $objQuery->update('dtb_plugin', array(
                'free_field1' => serialize($arrConfig),
            ), 'plugin_id = ?', array($plugin_id));
        }
    }

    /**
     * プラグイン設定情報を取得もしくは初期値を設定
     *
     * @return array 設定値
     */
    function getPluginConfig() {
        if ($this->isInstallPlugin()) {
            $arrConfig = unserialize($this->arrPlugin['free_field1']);

            // 設定値が空の場合、初期値を設定
            if ($arrConfig['prod_mode'] == '') {
                $arrConfig['prod_mode'] = 0;
            }

            return $arrConfig;
        }
    }

    /**
     * プラグインが有効か判定
     *
     * @return bool
     */
    function isInstallPlugin() {
        if ($this->arrPlugin) {
            return true;
        } else {
            return false;
        }
    }

}
?>
