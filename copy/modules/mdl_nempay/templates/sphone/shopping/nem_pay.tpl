
<script type="text/javascript">//<![CDATA[
    var sent = false;

    function fnCheckSubmit() {
        if (sent) {
            alert("只今、処理中です。しばらくお待ち下さい。");
            eccube.setModeAndSubmit('confirm','','');
            return false;
        }
        sent = true;
        return true;
    }
//]]></script>

<section id="undercolumn">
        <h2 class="title"><!--{$tpl_title|h}--></h2>

        <form name="form1" id="form1" method="post" action="?">
            <input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->" />
            <input type="hidden" name="mode" value="confirm" />

            <section class="otherconfirm_area">
            <div class="form_area">
                <div class="formBox">
                    <div class="innerBox">
                        <em>受注番号</em>：<!--{$arrOrder.order_id|h}-->
                    </div>
                    <div class="innerBox">
                        <em>お支払い金額(円)</em>：<!--{$arrOrder.payment_total|n2s}--> 円
                    </div>
                    <div class="innerBox">
                        <em>お支払い金額(XEM)</em>：<!--{$xem_amount|h}-->
                    </div>
                    <div class="innerBox">
                        <em>参考レート(24時間平均)</em>：<!--{$xem_jpy|h}-->
                    </div>
                </div>
            </div>
            
            <table>
                <tr>
                    <td>
                        以上の内容で間違いなければ、下記「注文完了ページへ」ボタンをクリックしてください。<br />
                        <span class="attention">※ご注文完了後、お支払い用のQRコードが発行されます。</span>
            	    </td>
                </tr>
            </table>
            
            <div class="btn_area">
                <ul class="btn_btm">
                    <li><a rel="external" href="#" class="btn" onclick="return fnCheckSubmit();">ご注文完了ページへ</a></li>
                    <li><a rel="external" href="#" class="btn_back" onclick="eccube.setModeAndSubmit('return', '', ''); return false;">戻る</a></li>
                </ul>
            </div>
        </form>
</section>