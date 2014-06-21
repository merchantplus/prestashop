<link href="{$module_dir}css/merchantplus.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="{$module_dir}merchantplus-prestashop.js"></script>
<a name="merchantplus-anchor"></a>
<div class="payment_module" style="border: 1px solid #595A5E;display: block;padding: 0.6em;text-decoration: none;margin-left: 0.7em;">
	<img src="{$module_dir}img/logo-merchantplus.png" class="merchantplus-logo" alt="First Data" />
	<h3 class="stripe_title">
		<img alt="" src="{$module_dir}img/secure-icon.png" /> {l s='Secure payment by credit card with Merchant Plus' mod='merchantplus'}
	</h3>
	<form action="{$module_dir}validation.php" method="post" name="merchantplus_form" id="merchantplus_form">
		<div id="merchantplusFrame">
		    {if isset($smarty.get.merchantplusError)}<p style="color: red;">{$smarty.get.merchantplusError|escape:'htmlall':'UTF-8'}</p>{/if}
			<p style="color: red;" id="error-submit"></p>			
			<table>
				<tr>
					<td>{l s='Card Holder Name' mod='merchantplus'}</td>
					<td><input type="text" name="merchantplus_card_holder" id="merchantplus_card_holder" size="30" style="width: 200px;" /></td>
				</tr>
				<tr>
					<td>{l s='Card Number' mod='merchantplus'}</td>
					<td>						
						<img class="cc-merchantplus-icon cc-merchantplus-disable" rel="Visa" alt="" src="{$module_dir}img/cc-visa.png" />
						<img class="cc-merchantplus-icon cc-merchantplus-disable" rel="MasterCard" alt="" src="{$module_dir}img/cc-mastercard.png" />
						<img class="cc-merchantplus-icon cc-merchantplus-disable" rel="Discover" alt="" src="{$module_dir}img/cc-discover.png" />
						<img class="cc-merchantplus-icon cc-merchantplus-disable" rel="American Express" alt="" src="{$module_dir}img/cc-amex.png" />
						<img class="cc-merchantplus-icon cc-merchantplus-disable" rel="JCB" alt="" src="{$module_dir}img/cc-jcb.png" />
						<img class="cc-merchantplus-icon cc-merchantplus-disable" rel="Diners Club" alt="" src="{$module_dir}img/cc-diners.png" />
						<br><br>
						<input type="text" name="x_card_num" id="merchantplus_cardnum" size="30" maxlength="16" autocomplete="Off" style="width: 200px;" />
					</td>
				</tr>
				<tr>
					<td>{l s='Expiration Date' mod='merchantplus'}</td>
					<td>
						<select id="merchantplus_exp_date_m" name="x_exp_date_m" style="width:60px;">
						{section name=date_m start=01 loop=13}
							<option value="{$smarty.section.date_m.index}">{$smarty.section.date_m.index}</option>
						{/section}
						</select> / <select name="x_exp_date_y">
						{section name=date_y start=14 loop=20}
							<option value="{$smarty.section.date_y.index}">20{$smarty.section.date_y.index}</option>
						{/section}
						</select>
					</td>
				</tr>
				<tr>
					<td>{l s='CVV' mod='merchantplus'}</td>
					<td>
						<input type="text" name="x_card_code" id="merchantplus_card_code" size="4" maxlength="4" />
						<a href="javascript:void(0)" class="merchantplus-card-cvc-info" style="border: none;">
							<img src="{$module_dir|escape}img/help.png" id="merchantplus_cvv_help" title="{l s='What\'s this?' mod='merchantplus'}" alt="" />{l s='What\'s this?' mod='merchantplus'}
							<div class="cvc-info">
								<img src="{$module_dir|escape}img/cvv.png" id="merchantplus_cvv_help_img"/>
							</div>
						</a>
					</td>
				</tr>
			</table>			
			<input type="submit" id="merchantplus_submit" value="{l s='Validate order' mod='merchantplus'}" class="button" />
			<div style="display: none;" id="merchantplus_submitload"><img src="{$img_ps_dir|escape}loader.gif" /></div>			
		</div>
	</form>
	<div style="clear:both"></div>
</div>