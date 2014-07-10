{if $merchantplus_order.valid == 1}
<div class="conf confirmation">
	{l s='Congratulations! Your payment is pending verification, and your order has been saved under' mod='merchantplus'}{if isset($merchantplus_order.reference)} {l s='the reference' mod='merchantplus'} <b>{$merchantplus_order.reference|escape:html:'UTF-8'}</b>{else} {l s='the ID' mod='merchantplus'} <b>{$merchantplus_order.id|escape:html:'UTF-8'}</b>{/if}.
</div>
{else}
<div class="error">
	{l s='Unfortunately, an error occurred during the transaction.' mod='merchantplus'}<br /><br />
	{l s='Please double-check your credit card details and try again. If you need further assistance, feel free to contact us anytime.' mod='merchantplus'}<br /><br />
{if isset($merchantplus_order.reference)}
	({l s='Your Order\'s Reference:' mod='merchantplus'} <b>{$merchantplus_order.reference|escape:html:'UTF-8'}</b>)
{else}
	({l s='Your Order\'s ID:' mod='merchantplus'} <b>{$merchantplus_order.id|escape:html:'UTF-8'}</b>)
{/if}
</div>
{/if}
