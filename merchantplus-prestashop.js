/*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @version  Release: $Revision: 6844 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

$(document).ready(function() {
	/* Determine the Credit Card Type */
	function GetCardType(number)
        {            
            var re = new RegExp("^4");
            if (number.match(re) != null)
                return "Visa";
 
            re = new RegExp("^(34|37)");
            if (number.match(re) != null)
                return "American Express";
 
            re = new RegExp("^5[1-5]");
            if (number.match(re) != null)
                return "MasterCard";
 
            re = new RegExp("^60");
            if (number.match(re) != null)
                return "Discover";
            
 			re = new RegExp("^21|18|35");
            if (number.match(re) != null)
                return "JCB";
                
            re = new RegExp("^30[0-5]|36|38");
            if (number.match(re) != null)
                return "Diners Club";            
            
            return "";
        }	
	var sts = false;
	$('#merchantplus_cardnum').live('keyup', function(){		
		if ($(this).val().length >= 2)
		{
			sts = false;
			merchantplus_card_type = GetCardType($(this).val());
			$('.cc-merchantplus-icon').removeClass('cc-merchantplus-enable');
			$('.cc-merchantplus-icon').removeClass('cc-merchantplus-disable');
			$('.cc-merchantplus-icon').each(function() {
				if ($(this).attr('rel') == merchantplus_card_type){
					$(this).addClass('cc-merchantplus-enable');
					sts = true;
				}else{
					$(this).addClass('cc-merchantplus-disable');					
				}
			});
		}
		else
		{
			sts = false;
			$('.cc-merchantplus-icon').removeClass('cc-merchantplus-enable');
			$('.cc-merchantplus-icon:not(.disable)').addClass('cc-merchantplus-disable');
		}
	});
		
	$('#merchantplus_form').submit(function(){	
		var submit = true;
		var msg = "";
		if($('#merchantplus_card_holder').val() == '' 
			|| $('#merchantplus_cardnum').val() == '' 
			|| $('#merchantplus_card_code').val() == ''
			|| $('#merchantplus_card_code').val() == '') 
		{
			msg = "&raquo; All field is required.<br>";
			submit = false;
		}
		if(!sts) {
			msg = msg+"&raquo; You must enter valid card number.<br>";
			submit = false;
		}		
		if(!submit) $("#error-submit").html(msg);
		
		return submit;
	});	
});