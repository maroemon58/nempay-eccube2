
<!--{include file="`$smarty.const.TEMPLATE_ADMIN_REALDIR`admin_popup_header.tpl"}-->

<h2><!--{$tpl_subtitle}--></h2>
<form name="form1" id="form1" method="post" action="<!--{$smarty.server.REQUEST_URI|h}-->">
<input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->" />
<input type="hidden" name="mode" value="edit">

<h3>キー設定</h3>
<table border="0" cellspacing="1" cellpadding="8" summary=" ">
    <col width="30%" />
    <col width="70%" />
    <tr>
        <td bgcolor="#f3f3f3">環境切り替え</td>
        <td>
        	<!--{assign var=key value="prod_mode"}-->
            <span class="attention"><!--{$arrErr[$key]}--></span>
            <!--{html_radios name=$key options=$arrProdMode separator=" " selected=$arrForm[$key]}-->
        </td>
    </tr>
    <tr>
        <td bgcolor="#f3f3f3">出品者アカウント(入金先)</td>
        <td>
        	<!--{assign var=key value="seller_addr"}-->
            <span class="attention"><!--{$arrErr[$key]}--></span>
        	<input type="text" name="<!--{$key}-->" value="<!--{$arrForm[$key]|h}-->" size="50" class="box50" />
        </td>
    </tr>
</table>

<div class="btn-area">
    <ul>
        <li>
            <a class="btn-action" href="javascript:;" onclick="document.form1.submit();return false;"><span class="btn-next">この内容で登録する</span></a>
        </li>
    </ul>
</div>

</form>

<!--{include file="`$smarty.const.TEMPLATE_ADMIN_REALDIR`admin_popup_footer.tpl"}-->
