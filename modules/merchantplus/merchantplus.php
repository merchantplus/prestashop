<?php
/*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @version  Release: $Revision: 1.2 $
*
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) exit;

class MerchantPlus extends PaymentModule{
	private $_postErrors = array();

	public function __construct(){
		$this->name = 'merchantplus';
		$this->tab = 'payments_gateways';
		$this->version = '1.1';
		$this->author = 'MerchantPlus';

		parent::__construct();
		
		$this->displayName = $this->l('MerchantPlus');
		$this->description = $this->l('Receive payment with MerchantPlus Gateway');

		
		/* Backward compatibility */
		if (_PS_VERSION_ < 1.5)
			require(_PS_MODULE_DIR_.$this->name.'merchantplus/backward_compatibility/backward.php');
	}

	public function install(){
		$this->registerHook('displayMobileHeader');
		
		return Db::getInstance()->Execute('
		CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'merchantplus` (
			`id_cart` int(10) NOT NULL,
			`authorization_num` varchar(11) DEFAULT NULL,
			`transaction_tag` int(11) DEFAULT NULL,
			`id_shop` int(10) DEFAULT NULL,
			`date_add` datetime NOT NULL,
			`date_cancel` datetime DEFAULT NULL,
			`date_refund` datetime DEFAULT NULL,
			PRIMARY KEY  (`id_cart`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;') && 
		parent::install() &&
		$this->registerHook('payment') && 
		$this->registerHook('orderConfirmation');
	}

	public function uninstall(){
		Configuration::deleteByName('MERCHANTPLUS_ID');
		Configuration::deleteByName('MERCHANTPLUS_KEY');
		Configuration::deleteByName('MERCHANTPLUS_METHOD');
		Configuration::deleteByName('MERCHANTPLUS_TEST_MODE');

		return parent::uninstall();
	}

	public function getContent(){
		$html = '';
		if (Tools::isSubmit('submitMPData') && 
			isset($_POST['merchantplus_id']) && 
			isset($_POST['merchantplus_key']) &&
			!empty($_POST['merchantplus_id']) && 
			!empty($_POST['merchantplus_key']))
		{
			
			Configuration::updateValue('MERCHANTPLUS_ID', pSQL(Tools::getValue('merchantplus_id')));
			Configuration::updateValue('MERCHANTPLUS_KEY', pSQL(Tools::getValue('merchantplus_key')));
			Configuration::updateValue('MERCHANTPLUS_METHOD', pSQL(Tools::getValue('merchantplus_method')));
			Configuration::updateValue('MERCHANTPLUS_TEST_MODE', pSQL(Tools::getValue('merchantplus_test_mode')));
			$html = '<div style="background-color:#dff0d8;border-color:#d6e9c6;color:#3c763d;padding:10px">'
					.$this->l('Configuration updated successfully!').
					'</div>';							
		}
		else if (Tools::isSubmit('submitMPData'))
			$html = '<div style="background-color:#f2dede;border-color:#ebccd1;color:#a94442;padding:10px">'
					.$this->l('Please fill the required fields!').
					'</div>';

		$this->context->smarty->assign(array(
		'merchantplus_form' => './index.php?tab=AdminModules&configure=merchantplus&token='.Tools::getAdminTokenLite('AdminModules').'&tab_module='.$this->tab.'&module_name=merchantplus',
		'merchantplus_tracking' => 'http://www.prestashop.com/modules/merchantplus.png?url_site='.Tools::safeOutput($_SERVER['SERVER_NAME']).'&amp;id_lang='.(int)$this->context->cookie->id_lang,
		'merchantplus_id' => Configuration::get('MERCHANTPLUS_ID'),
		'merchantplus_key' => Configuration::get('MERCHANTPLUS_KEY'),
		'merchantplus_method' => Configuration::get('MERCHANTPLUS_METHOD'),
		'merchantplus_test_mode' => Configuration::get('MERCHANTPLUS_TEST_MODE'),
		'merchantplus_ssl' => Configuration::get('PS_SSL_ENABLED'),
		'merchantplus_confirmation' => $html));

		return $this->display(__FILE__, 'tpl/admin.tpl');
	}

	public function hookDisplayMobileHeader(){
		return $this->hookHeader();
	}

	public function hookPayment($params){
		$this->smarty->assign('merchantplus_ps_version', _PS_VERSION_);
		
		$currency = new Currency((int)$params['cart']->id_currency);

		if (!$this->active || Configuration::get('MERCHANTPLUS_ID') == '' || Configuration::get('MERCHANTPLUS_KEY') == '' && $currency->iso_code != 'USD')
			return false;

		return $this->display(__FILE__, 'tpl/payment.tpl');
	}
	
	/**
	 * Display a confirmation message after an order has been placed
	 *
	 * @param array Hook parameters
	 */
	public function hookOrderConfirmation($params){	
	
		if ($params['objOrder']->module != $this->name)
			return false;
		if ($params['objOrder'] && Validate::isLoadedObject($params['objOrder']) && isset($params['objOrder']->valid))
		{
			if (version_compare(_PS_VERSION_, '1.5', '>=') && isset($params['objOrder']->reference))
				$this->smarty->assign('merchantplus_order', array('id' => $params['objOrder']->id, 'reference' => $params['objOrder']->reference, 'valid' => $params['objOrder']->valid));
			else
				$this->smarty->assign('merchantplus_order', array('id' => $params['objOrder']->id, 'valid' => $params['objOrder']->valid));

			return $this->display(__FILE__, 'tpl/order-confirmation.tpl');
		}
	}

	private function _getTransaction($id_cart, $id_shop = null){
		return Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'merchantplus` WHERE `id_cart` = '.(int)$id_cart);
	}

	private function _insertTransaction($params){
		return Db::getInstance()->insert('merchantplus', $params);
	}

	public function validation(){
		$cart = $this->context->cart;
		
		if (Validate::isLoadedObject($cart) && !Order::getOrderByCartId((int)Tools::getValue('cart'))){
			
			$customer = new Customer((int)$cart->id_customer);
			$invoiceAddress = new Address((int)$cart->id_address_invoice);

			if((int)$cart->id_address_invoice != (int)$cart->id_address_delivery){
				$deliveryAddress = new Address((int)$cart->id_address_delivery);
			}else{
				$deliveryAddress = new Address((int)$cart->id_address_invoice);
			}
			
			// Merchant Info
			$params['x_login']             = Tools::safeOutput(Configuration::get('MERCHANTPLUS_ID'));
			$params['x_tran_key']          = Tools::safeOutput(Configuration::get('MERCHANTPLUS_KEY'));

			// TEST TRANSACTION
			$params['x_test_request']      = (Configuration::get('MERCHANTPLUS_TEST_MODE') == "test" ? 'TRUE' : 'FALSE');

			// AIM Head
			$params['x_version']           = '3.1';

			// TRUE Means that the Response is going to be delimited
			$params['x_delim_data']        = 'TRUE';
			$params['x_delim_char']        = '|';
			$params['x_relay_response']    = 'FALSE';

			// Transaction Info
			$params['x_method']            = 'CC';
			$params['x_type']              = Tools::safeOutput(Configuration::get('MERCHANTPLUS_METHOD'));
			$params['x_amount']            = number_format((float)$cart->getOrderTotal(true, 3), 2, '.', '');

			// Test Card
			$month = ((int)$_POST['x_exp_date_m']<9?'0'.$_POST['x_exp_date_m']:$_POST['x_exp_date_m']);
			$params['x_card_num']          = Tools::safeOutput($_POST['x_card_num']);
			$params['x_exp_date']          = Tools::safeOutput($month.$_POST['x_exp_date_y']);
			$params['x_card_code']         = Tools::safeOutput($_POST['x_card_code']);
			$params['x_trans_id']          = '';

			// Order Info
			$params['x_invoice_num']       = (int)$_POST['x_invoice_num'];
			$params['x_description']       = $params['x_amount'];

			// Customer Info
			$params['x_first_name']        = Tools::safeOutput($invoiceAddress->firstname);
			$params['x_last_name']         = Tools::safeOutput($invoiceAddress->lastname);
			$params['x_company']           = Tools::safeOutput($invoiceAddress->company);
			$params['x_address']           = Tools::safeOutput($invoiceAddress->address1.' '.$invoiceAddress->address2);
			$params['x_city']              = Tools::safeOutput($invoiceAddress->city);
			$params['x_state']             = '';
			$params['x_zip']               = Tools::safeOutput($invoiceAddress->postcode);
			$params['x_country']           = Tools::safeOutput($invoiceAddress->country);
			$params['x_phone']             = Tools::safeOutput($invoiceAddress->phone);
			$params['x_fax']               = '';
			$params['x_email']             = '';
			$params['x_cust_id']           = Tools::safeOutput($invoiceAddress->id_customer);
			$params['x_customer_ip']       = '';

			// shipping info
			$params['x_ship_to_first_name']= Tools::safeOutput($deliveryAddress->firstname);
			$params['x_ship_to_last_name'] = Tools::safeOutput($deliveryAddress->lastname);
			$params['x_ship_to_company']   = Tools::safeOutput($deliveryAddress->company);
			$params['x_ship_to_address']   = Tools::safeOutput($deliveryAddress->address1.' '.$deliveryAddress->address2);
			$params['x_ship_to_city']      = Tools::safeOutput($deliveryAddress->city);
			$params['x_ship_to_state']     = '';
			$params['x_ship_to_zip']       = Tools::safeOutput($deliveryAddress->postcode);
			$params['x_ship_to_country']   = Tools::safeOutput($deliveryAddress->country);
			
			$result = $this->_merchantPlusCall($params);			
			
			if ($result['http_code']>=200 && $result['http_code']<300){										
				$data = $result['body'];
				$delim = $data{1};					
				$data = explode($delim, $data);	
				
				if($data[0] == 1){
					$this->_insertTransaction(
						array(
							'id_cart' => (int)$cart->id, 
							'authorization_num' => pSQL($data[4]), 
							'transaction_tag' => (int)$data[6], 
							'date_add' => date('Y-m-d H:i:s')
						)
					);
					$this->validateOrder(
						(int)$cart->id, 
						(int)Configuration::get('PS_OS_PAYMENT'), 
						(float)$data[8],
						$this->displayName,
						pSQL($data[3]),
						array(),
						null,
						false,
						$cart->secure_key
					);
					Configuration::updateValue('MERCHANTPLUS_CONFIGURATION_OK', true);
										
					if (version_compare(_PS_VERSION_, '1.5', '>='))
					{
						$new_order = new Order((int)$this->currentOrder);
						if (Validate::isLoadedObject($new_order))
						{
							$payment = $new_order->getOrderPaymentCollection();
							$payment[0]->transaction_id = (int)$data[6];
							$payment[0]->save();
						}
					}
										
					if (_PS_VERSION_ < 1.5)
						$redirect = __PS_BASE_URI__.'order-confirmation.php?id_cart='.(int)$this->context->cart->id.'&id_module='.(int)$this->id.'&id_order='.(int)$this->currentOrder.'&key='.$this->context->customer->secure_key;
					else
						$redirect = __PS_BASE_URI__.'index.php?controller=order-confirmation&id_cart='.(int)$this->context->cart->id.'&id_module='.(int)$this->id.'&id_order='.(int)$this->currentOrder.'&key='.$this->context->customer->secure_key;

					Tools::redirect($redirect);
					exit;
				}else{
					$error = $this->error_status();
					
					$error_msg = trim($error[$data[0]][$data[2]]);

					Logger::AddLog('[MerchantPlusData] '.Tools::safeOutput($error_msg), 2);
					$checkout_type = Configuration::get('PS_ORDER_PROCESS_TYPE') ? 'order-opc' : 'order';
					$url = (_PS_VERSION_ >= '1.5' ? 'index.php?controller='.$checkout_type.'&' : $checkout_type.'.php?').'step=3&cgv=1&merchantplusError='.$error_msg.'#merchantplus-anchor';

					if (!isset($_SERVER['HTTP_REFERER']) ||	strstr($_SERVER['HTTP_REFERER'], 'order'))
						Tools::redirect($url);
					elseif (strstr($_SERVER['HTTP_REFERER'], '?'))
						Tools::redirect(Tools::safeOutput($_SERVER['HTTP_REFERER']).'&merchantplusError='.$error_msg.'#merchantplus-anchor', '');
					else
						Tools::redirect(Tools::safeOutput($_SERVER['HTTP_REFERER']).'?merchantplusError='.$error_msg.'#merchantplus-anchor', '');
				}			
			}else{
				die('Unfortunately your order could not be validated. Error: "Merchant Plus Gateway", please contact us.');
			}			
		}
		else
			die('Unfortunately your order could not be validated. Error: "Invalid Cart ID", please contact us.');
	}

	private function _merchantPlusCall($params){
		
		$url = 'https://gateway.merchantplus.com/cgi-bin/PAWebClient.cgi';
		
		/* Do the CURL request ro Merchant Plus */
		$request = curl_init($url);
		curl_setopt($request, CURLOPT_HEADER, 0);
		curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($request, CURLOPT_POSTFIELDS, http_build_query($params, '', '&'));
		curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($request, CURLOPT_SSL_VERIFYHOST, false);
		$response = curl_exec($request);			
		$info = curl_getinfo($request);
		curl_close($request);
		
		return array('http_code'=>$info['http_code'], 'body'=>$response);
	}
	
	function error_status() {
		$error[2][2] 	= 'This transaction has been declined.';
		$error[3][6] 	= 'The credit card number is invalid.';
		$error[3][7] 	= 'The credit card expiration date is invalid.';
		$error[3][8] 	= 'The credit card expiration date is invalid.';
		$error[3][13] 	= 'The merchant Login ID or Password or TransactionKey is invalid or the account is inactive.';
		$error[3][15] 	= 'The transaction ID is invalid.';
		$error[3][16] 	= 'The transaction was not found';
		$error[3][17] 	= 'The merchant does not accept this type of credit card.';
		$error[3][19] 	= 'An error occurred during processing. Please try again in 5 minutes.';
		$error[3][33] 	= 'A required field is missing.';
		$error[3][42] 	= 'There is missing or invalid information in a parameter field.';
		$error[3][47] 	= 'The amount requested for settlement may not be greater than the original amount authorized.';
		$error[3][49] 	= 'A transaction amount equal or greater than $100000 will not be accepted.';
		$error[3][50] 	= 'This transaction is awaiting settlement and cannot be refunded.';
		$error[3][51] 	= 'The sum of all credits against this transaction is greater than the original transaction amount.';
		$error[3][57] 	= 'A transaction amount less than $1 will not be accepted.';
		$error[3][64] 	= 'The referenced transaction was not approved.';
		$error[3][69] 	= 'The transaction type is invalid.';
		$error[3][70] 	= 'The transaction method is invalid.';
		$error[3][72] 	= 'The authorization code is invalid.';
		$error[3][73] 	= 'The driver\'s license date of birth is invalid.';
		$error[3][84] 	= 'The referenced transaction was already voided.';
		$error[3][85] 	= 'The referenced transaction has already been settled and cannot be voided.';
		$error[3][86] 	= 'Your settlements will occur in less than 5 minutes. It is too late to void any existing transactions.';
		$error[3][87] 	= 'The transaction submitted for settlement was not originally an AUTH_ONLY.';
		$error[3][88] 	= 'Your account does not have access to perform that action.';
		$error[3][89] 	= 'The referenced transaction was already refunded.';
		$error[3][90] 	= 'Data Base Error.';
		
		return $error;
	}
}