<link href="{$module_dir}css/merchantplus.css" rel="stylesheet" type="text/css">
<script>
	$(document).ready(function(){
		$("#content.nobootstrap").css('min-width', '0px');
	})
</script>

<img src="{$merchantplus_tracking|escape:'htmlall':'UTF-8'}" alt="" style="display: none;"/>
<div class="merchantplus-wrap">
	{$merchantplus_confirmation}
	{if !$merchantplus_ssl}
		<br/>
		<div style="background-color: #fcf8e3; border-color: #faebcc; color: #8a6d3b; padding: 10px">
			<strong>{l s='SSL is not active on your shop.' mod='merchantplus'}</strong><br/>
			{l s='We highly recommend you to enable SSL on your shop. Most customers will not place their order if SSL is not enabled.' mod='merchantplus'}
		</div>
		<br/>
	{/if}
	<form action="{$merchantplus_form|escape:'htmlall':'UTF-8'}" id="merchantplus-configuration" method="post">
		<fieldset class="merchantplus-half L">
			<legend><img src="{$module_dir}img/icon-config.gif" alt="" />{l s='Configuration' mod='merchantplus'}</legend>
			<div>
				<p class="MB10">{l s='In order to use this module, please fill out the form with the credentials provided to you by MerchantPlus Gateway' mod='merchantplus'}</p>
				
				<label for="merchantplus_key_id">{l s='Login ID:' mod='merchantplus'}</label>
				<div class="margin-form">
					<input type="text" class="text" name="merchantplus_id" id="merchantplus_id" value="{$merchantplus_id|escape:'htmlall':'UTF-8'}" /> <sup>*</sup>
				</div>
				
				<label for="merchantplus_key">{l s='Transaction Key:' mod='merchantplus'}</label>
				<div class="margin-form">
					<input type="text" class="text" name="merchantplus_key" id="merchantplus_key" value="{$merchantplus_key|escape:'htmlall':'UTF-8'}" /> <sup>*</sup>
				</div>
				
				<label for="merchantplus_method">{l s='Transaction Method:' mod='merchantplus'}</label>
				<div class="margin-form">
					<select id="merchantplus_method" name="merchantplus_method">';
						<option value="AUTH_CAPTURE" {if ("AUTH_CAPTURE" == $merchantplus_method)} selected{/if}/> Capture
						<option value="AUTH_ONLY" {if ("AUTH_ONLY" == $merchantplus_method)} selected{/if}/> Authorization
					</select>
				</div>
				
				<label for="merchantplus_test_mode">{l s='Environment:' mod='merchantplus'}</label>
				<div class="margin-form">
					<select id="merchantplus_test_mode" name="merchantplus_test_mode">';
						<option value="test" {if ("test" == $merchantplus_test_mode)} selected{/if}/> Test Mode
						<option value="live" {if ("live" == $merchantplus_test_mode)} selected{/if}/> Live Mode
					</select>
				</div>
				
				<div class="margin-form">
					<input type="submit" class="button" name="submitMPData" value="{l s='Save' mod='merchantplus'}" />
				</div>
				<span class="small"><sup>*</sup> {l s='Required Fields' mod='merchantplus'}</span>
			</div>
			<!---
			<div class="merchantplus-half R">
				<h4>{l s='How to get your MerchantPlus credentials?' mod='merchantplus'}</h4>
				<ol>
					<li><p>{l s='Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor.' mod='merchantplus'}</a></p></li>					
					<li><p>{l s='Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor.' mod='merchantplus'}</a></p></li>					
					<li><p>{l s='Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor.' mod='merchantplus'}</a></p></li>					
					<li><p>{l s='Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor.' mod='merchantplus'}</a></p></li>					
					<li><p>{l s='Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor.' mod='merchantplus'}</a></p></li>					
					<li><p>{l s='Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor.' mod='merchantplus'}</a></p></li>					
					<li><p>{l s='Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor.' mod='merchantplus'}</a></p></li>					
					<li><p>{l s='Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor.' mod='merchantplus'}</a></p></li>					
					<li><p>{l s='Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor.' mod='merchantplus'}</a></p></li>					
					<li><p>{l s='Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor.' mod='merchantplus'}</a></p></li>					
				</ol>
			</div>
			--->
		</fieldset>
	</form>
</div>