<script type="text/javascript">//<![CDATA[
    var sent = false;

    function fnCheckSubmit() {
        if (sent) {
            alert("只今、処理中です。しばらくお待ち下さい。");
            return false;
        }
        sent = true;
        return true;
    }
//]]></script>

<div id="undercolumn">
    <div id="undercolumn_shopping">
        <p class="flow_area"><img src="<!--{$TPL_URLPATH}-->img/picture/img_flow_03.jpg" alt="購入手続きの流れ" /></p>
        <h2 class="title"><!--{$tpl_title|h}--></h2>

        <form name="form1" id="form1" method="post" action="?">
            <input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->" />
            <input type="hidden" name="mode" value="confirm" />

            <table summary="お支払方法選択">
                <tr>
                    <th>受注番号</th>
                    <td><!--{$arrOrder.order_id|h}--></td>
                </tr>
                <tr>
                    <th>お支払い金額(円)</th>
                    <td><!--{$arrOrder.payment_total|n2s}--> 円</td>
                </tr>
                <tr>
                    <th>お支払い金額(XEM)</th>
                    <td><!--{$xem_amount|h}--> XEM</td>
                </tr>
                <tr>
                    <th>参考レート(24時間平均)</th>
                    <td><!--{$xem_jpy}--> 円／XEM</td>
              </tr>
            </table>
            
            <table>
                <tr>
                    <td>
                        以上の内容で間違いなければ、下記「注文完了ページへ」ボタンをクリックしてください。<br />
                        <span class="attention">※ご注文完了後、お支払い用のQRコードが発行されます。</span>
            	    </td>
                </tr>
            </table>
            
            <div class="btn_area">
                <ul>
                    <li>
                        <a href="?" onclick="eccube.setModeAndSubmit('return', '', ''); return false;">
                            <img class="hover_change_image" src="<!--{$TPL_URLPATH}-->img/button/btn_back.jpg" alt="戻る" />
                        </a>
                    </li>
                    <li>
                        <input type="image" onclick="return fnCheckSubmit();" class="hover_change_image" src="<!--{$TPL_URLPATH}-->img/button/btn_order_complete.jpg" alt="ご注文完了ページへ"  name="next" id="next" />
                    </li>
              </ul>
            </div>
        </form>
    </div>
</div>
