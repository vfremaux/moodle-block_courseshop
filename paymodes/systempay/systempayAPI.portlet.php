<?php


// mandatory
$parms['vads_action_mode'] = 'INTERACTIVE' ;
$parms['vads_currency'] = $CFG->block_courseshop_systempay_currency_code;
$parms['vads_amount'] = floor($portlet->amount * 100);
$parms['vads_ctx_mode'] = ($CFG->block_courseshop_test) ? 'TEST' : 'PRODUCTION' ;
$parms['vads_page_action'] = 'PAYMENT';
$parms['vads_payment_config'] = 'SINGLE';
$parms['vads_site_id'] = $CFG->block_courseshop_systempay_merchant_id ;
$parms['vads_trans_id'] = $portlet->onlinetransactionid ; // 6 chars from 000000 to 899999 / no special chars
$parms['vads_trans_date'] =  gmdate('YmdHis'); // 20 chars max / no special chars
$parms['vads_version'] =  'V2'; // chars max / no special chars

// accessory

$parms['vads_shop_name'] = ''+ @$this->shopblock->config->shopcaption;

$parms['vads_cust_email'] = $portlet->customer->email;
$parms['vads_cust_city'] = $portlet->customer->city;
$parms['vads_cust_zip'] = $portlet->customer->zip;

$parms['vads_url_success'] = $portlet->returnurl; // return url (normal)
$parms['vads_url_error'] = $portlet->returnurl; // return url (normal)
$parms['vads_url_cancel'] = $portlet->returnurl; // return url (normal)
$parms['vads_url_refused'] = $portlet->returnurl; // return url (normal)

$lang = substr(current_language(), 0, 2);
if (!preg_match('/fr|en|us|it|de|nl|pt|es|ja|zh/', $lang)) $lang = 'en';
$parms['vads_language'] = $lang;

$return_context = 'systempayback' . '-' .$this->shopblock->instance->id. '-'.(0 + $this->shopblock->pinned).'-' .$portlet->transactionid;
$encodedcontext = base64_encode($return_context);
$parms['vads_order_info'] = $encodedcontext; // some private transaction data to restore context on return

// technical tuning
$parms['vads_return_mode'] = 'GET';
$parms['vads_validation_mode'] = 0;

// last signature calculation in test or production mode
$certificate = ($CFG->block_courseshop_test) ? @$CFG->block_courseshop_systempay_test_certificate : @$CFG->block_courseshop_systempay_prod_certificate ;
$parms['signature'] = $this->generate_sign($parms, $certificate) ;

$url = $CFG->block_courseshop_systempay_service_url;

$confirmstr = get_string('confirmorder', 'block_courseshop');

if (!empty($url)){
?>
<div class="payportlet">
	<form method="get" name="systempayform" action="<?php echo $url ?>">
		<?php 
		foreach($parms as $key => $value){
			$value = htmlentities($value);
			$lang = substr(current_language(), 0, 2);
			echo "<input type=\"hidden\" name=\"$key\" value=\"$value\" />\n";
		}
		echo "<input type=\"image\" src=\"{$CFG->wwwroot}/blocks/courseshop/paymodes/systempay/pix/{$lang}.png\" value=\"{$confirmstr}\" />";
		?>
	</form>
</div>
<?php
} else {
	print_box(get_string('errorsystempaynotsetup', 'systempay', '', $CFG->dirroot.'/blocks/courseshop/paymodes/systempay/lang/'));
}
?>