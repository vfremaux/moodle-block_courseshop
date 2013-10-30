<?php

require_once $CFG->dirroot.'/blocks/courseshop/paymodes/paymode.class.php';
require_once $CFG->dirroot.'/blocks/courseshop/locallib.php';

# Response codes (vads_status)
define('SP_PAYMENT_ACCEPTED', '00'); // Paiement réalisé avec succès
define('SP_PAYMENT_JOIN_BANK', '02'); // Le commerçant doit contacter la banque du porteur.
define('SP_PAYMENT_REJECTED', '05'); // Echéance du paiement refusée
define('SP_PURCHASE_CANCELLED', '17'); // Abandon de l’internaute
define('SP_REQUEST_ERROR', '30'); // Erreur de requete
define('SP_INTERNAL_ERROR', '96'); // Erreur interne de traitement

$vads_status = array(
	SP_PAYMENT_ACCEPTED => 'SP_PAYMENT_ACCEPTED',
	SP_PAYMENT_JOIN_BANK => 'SP_PAYMENT_JOIN_BANK', // Le commerçant doit contacter la banque du porteur.
	SP_PAYMENT_REJECTED => 'SP_PAYMENT_REJECTED', // Echéance du paiement refusée
	SP_PURCHASE_CANCELLED => 'SP_PURCHASE_CANCELLED', // Abandon de l’internaute
	SP_REQUEST_ERROR => 'SP_REQUEST_ERROR', // Erreur de requete
	SP_INTERNAL_ERROR => 'SP_INTERNAL_ERROR', // Erreur interne de traitement
);

// Extra error explicitaton code (vads_extra_result)
define('SP_STATUS_NOCHECK', '');   // Pas de contrôle effectué
define('SP_STATUS_GOOD', '00'); // Tous les contrôles se sont déroulés avec succès
define('SP_STATUS_OVER', '02'); // La carte a dépassé l’encours autorisé
define('SP_STATUS_SELLER_EXCLUDES', '03'); // La carte appartient à la liste grise du commerçant
define('SP_STATUS_COUNTRY_EXCLUDES', '04'); // Le pays d’émission de la carte appartient à la liste grise du commerçant ou le pays d’émission de la carte n’appartient pas à la liste blanche du commerçant.
define('SP_STATUS_IP_EXCLUDES', '05'); // L’adresse IP appartient à la liste grise du commerçant
define('SP_STATUS_BINCODE_EXCLUDES', '06'); // Le code bin appartient à la liste grise du commerçant
define('SP_STATUS_E_CARD', '07'); // Détection d’une E-Carte Bleue
define('SP_STATUS_LOCAL_CARD', '08'); // Détection d’une carte commerciale nationale
define('SP_STATUS_FOREIGN_CARD', '09'); // Détection d’une carte commerciale étrangère
define('SP_STATUS_AUTH_CARD', '14'); // Détection d’une carte à autorisation systématique
define('SP_STATUS_BAD_COUNTRY', '20'); // Contrôle de cohérence : aucun pays ne correspond (pays IP, payscarte, pays client)
define('SP_STATUS_IP_COUNTRY_EXCLUDES', '30'); // Le pays de l’adresse IP appartient à la liste grise
define('SP_STATUS_TECH_ERROR', '99'); // Problème technique rencontré par le serveur lors du traitement d’un des contrôles locaux 

$vads_extra_status = array(
	SP_STATUS_NOCHECK => 'SP_STATUS_NOCHECK',   // Pas de contrôle effectué
	SP_STATUS_GOOD => 'SP_STATUS_GOOD', // Tous les contrôles se sont déroulés avec succès
	SP_STATUS_OVER => 'SP_STATUS_OVER', // La carte a dépassé l’encours autorisé
	SP_STATUS_SELLER_EXCLUDES => 'SP_STATUS_SELLER_EXCLUDES', // La carte appartient à la liste grise du commerçant
	SP_STATUS_COUNTRY_EXCLUDES => 'SP_STATUS_COUNTRY_EXCLUDES', // Le pays d’émission de la carte appartient à la liste grise du commerçant ou le pays d’émission de la carte n’appartient pas à la liste blanche du commerçant.
	SP_STATUS_IP_EXCLUDES => 'SP_STATUS_IP_EXCLUDES', // L’adresse IP appartient à la liste grise du commerçant
	SP_STATUS_BINCODE_EXCLUDES => 'SP_STATUS_BINCODE_EXCLUDES', // Le code bin appartient à la liste grise du commerçant
	SP_STATUS_E_CARD => 'SP_STATUS_E_CARD', // Détection d’une E-Carte Bleue
	SP_STATUS_LOCAL_CARD => 'SP_STATUS_LOCAL_CARD', // Détection d’une carte commerciale nationale
	SP_STATUS_FOREIGN_CARD => 'SP_STATUS_FOREIGN_CARD', // Détection d’une carte commerciale étrangère
	SP_STATUS_AUTH_CARD => 'SP_STATUS_AUTH_CARD', // Détection d’une carte à autorisation systématique
	SP_STATUS_BAD_COUNTRY => 'SP_STATUS_BAD_COUNTRY', // Contrôle de cohérence : aucun pays ne correspond (pays IP, payscarte, pays client)
	SP_STATUS_IP_COUNTRY_EXCLUDES => 'SP_STATUS_IP_COUNTRY_EXCLUDES', // Le pays de l’adresse IP appartient à la liste grise
	SP_STATUS_TECH_ERROR => 'SP_STATUS_TECH_ERROR', // Problème technique rencontré par le serveur lors du traitement d’un des contrôles locaux 
);

define('SP_SECURE_NO', '0');
define('SP_SECURE_13DS', '1 3DS');
define('SP_SECURE_13DR', '1 3DR');
define('SP_SECURE_1ECB', '1 ECB');

// waranty codes
define('SP_WARANTY_YES', 'YES'); // Le paiement est garanti
define('SP_WARANTY_NO', 'NO'); // Le paiement n’est pas garanti
define('SP_WARANTY_UNKNOWN', 'UNKNOWN'); // Suite à une erreur technique, le paiement ne peut pas être garanti
define('SP_WARANTY_NA', ''); // Garantie de paiement non applicable 

class courseshop_paymode_systempay extends courseshop_paymode{

	function __construct(&$shopblockinstance){
		parent::__construct('systempay', $shopblockinstance, true, true);
	}
		
	// prints a payment porlet in an order form
	function print_payment_portlet(&$portlet){
		global $CFG;
		
		echo '<table id="systempay_panel"><tr><td>';
		print_string('systempayDoorTransferText', 'block_courseshop');
        echo '</td></tr>';
		echo '<tr><td align="center"><br />';
		
		// print_object($portlet);

	   	$portlet->sessionid = session_id();
	   	$portlet->amount = $portlet->totaltaxedamount;
	   	$portlet->merchant_id = $CFG->block_courseshop_sellerID;
	   	$portlet->onlinetransactionid = $this->generate_online_id();
	   	$portlet->returnurl = $CFG->wwwroot."/blocks/courseshop/paymodes/systempay/process.php";
	   	
	   	include($CFG->dirroot.'/blocks/courseshop/paymodes/systempay/systempayAPI.portlet.php');

		echo '<center><p><span class="procedureOrdering"></span>';
		/*
		$payonlinestr = get_string('payonline', 'block_courseshop');
		echo "<input type=\"button\" name=\"go_btn\" value=\"$payonlinestr\" onclick=\"document.confirmation.submit();\" />";
		*/
		echo '<p><span class="courseshop-procedure-cancel">X</span> ';
		$cancelstr = get_string('cancel');
		echo "<a href=\"{$CFG->wwwroot}/blocks/courseshop/shop/view.php?view=shop&id={$this->shopblock->instance->id}&pinned={$this->shopblock->pinned}\" class=\"smalltext\">$cancelstr</a>";	
		echo '</td></tr></table>';
    }

	// prints a payment porlet in an order form
	function print_invoice_info(&$billdata = null){
		echo get_string($this->name.'paymodeinvoiceinfo', $this->name, '', $CFG->dirroot.'/blocks/courseshop/paymodes/'.$this->name.'/lang/');
	}

	function print_complete(){
        echo compile_mail_template('billCompleteText', array(), 'block_courseshop') ; 
	}

	// extract DATA, get context_return and bounce to shop entrance with proper context values
	function cancel(){
		global $CFG, $SESSION;
		
		$paydata = $this->decode_return_data();
		
		list($cmd, $instanceid, $pinned, $transid) = explode('-', $paydata['return_context']);

		// mark transaction (order record) as abandonned
	    $blocktable = ($pinned) ? 'block_pinned' : 'block_instance' ;
	    if (!$instance = get_record($blocktable, 'id', $instanceid)){
	        error('Invalid block');
	    }
	    $theBlock = block_instance('courseshop', $instance);
		$aFullBill = courseshop_get_full_bill($transid, $theBlock);

	    $updatedbill->id = $aFullBill->id;
	    $updatedbill->onlinetransactionid = $paydata['shop_id'].'-'.$paydata['transmission_date'].'-'.$paydata['transaction_id'];
	    $updatedbill->paymode = 'systempay';
	    $updatedbill->status = 'CANCELLED';
	    update_record('courseshop_bill', $updatedbill);

		// cancel shopping cart
		unset($SESSION->shoppingcart);
		
		redirect($CFG->wwwroot.'/blocks/courseshop/shop/view.php?view=failure&id='.$instanceid.'&pinned='.$pinned);
	}

	/**
	* processes an explicit payment return
	*/
	function process(){
		global $CFG, $SESSION;

		$paydata = $this->decode_return_data();				
		
		// OK, affichage des champs de la réponse
		if (debugging() && $CFG->block_courseshop_test){
			# OK, affichage du mode DEBUG si activé
			echo "<center>\n";
			echo "<H3>R&eacute;ponse manuelle du serveur SP Plus</H3>\n";
			echo "</center>\n";
			echo '<hr/>';
			print_object($paydata);
			echo "<br/><br/><hr/>";
		}

		list($cmd, $instanceid, $pinned, $transid) = explode('-', $paydata['return_context']);

	    $blocktable = ($pinned) ? 'block_pinned' : 'block_instance' ;
	    if (!$instance = get_record($blocktable, 'id', $instanceid)){
	        error('Invalid block');
	    }
	    $theBlock = block_instance('courseshop', $instance);
		$aFullBill = courseshop_get_full_bill($transid, $theBlock);
	
		// bill could already be SOLDOUT by IPN	so do nothing
		// process it only if needing to process.
		if ($paydata['vads_result'] == SP_PAYMENT_ACCEPTED){
			// processing bill changes
			if ($aFullBill->status == 'DELAYED'){
			    $updatedbill->id = $aFullBill->id;
			    $updatedbill->onlinetransactionid = $paydata['vads_site_id'].'-'.$paydata['vads_trans_date'].'-'.$paydata['vads_trans_id'];
			    $aFullBill->status = $updatedbill->status = 'SOLDOUT';
			    $aFullBill->paiedamount = $paydata['vads_effective_amount'];
			    update_record('courseshop_bill', $updatedbill);
			    
			    // redirect to success for ordering production with significant data
			    courseshop_trace("[$transid] SystemPay : Transation Complete, transferring to success end point");
			    redirect($CFG->wwwroot.'/blocks/courseshop/shop/view.php?view=success&id='.$instanceid.'&cmd=finish&pinned='.$pinned.'&transid='.$transid);
			}

			if ($aFullBill->status == 'SOLDOUT'){
			    redirect($CFG->wwwroot.'/blocks/courseshop/shop/view.php?view=success&id='.$instanceid.'&cmd=finish&pinned='.$pinned.'&transid='.$transid);
			}
			
			//other situations should be weird cases...
			// Silent redirect but shop_trace something
			courseshop_trace("[$transid] SystemPay : Weird state sequence Trans accept in status ".$aFullBill->status);
		    redirect($CFG->wwwroot.'/blocks/courseshop/shop/view.php?view=success&id='.$instanceid.'&cmd=finish&pinned='.$pinned.'&transid='.$transid);
		} else {
		    $updatedbill->id = $aFullBill->id;
		    $bill->status = $updatedbill->status = 'FAILED';
		    update_record('courseshop_bill', $updatedbill);
		    
		    // Do not erase shopping cart : user might try again with other payment mean
			// unset($SESSION->shoppingcart);
		    
		    redirect($CFG->wwwroot.'/blocks/courseshop/shop/view.php?view=shop&id='.$instanceid.'&pinned='.$pinned.'&transid='.$transid);
		}

	}

	/**
	* processes a payment asynchronous confirmation
	*/
	function process_ipn(){
		global $CFG, $SESSION;

		$paydata = $this->decode_return_data();
				
		list($cmd, $instanceid, $pinned, $transid) = explode('-', $paydata['return_context']);
		courseshop_trace("[$transid] SystemPay IPN processing");

		$bill = get_record('courseshop_bill', 'transactionid', $transid);
		$laststatus = (strrchr($bill->remotestatus, ',')) ? 0 : substr(strrchr($bill->remotestatus, ','), 1);
		
		// initiate systempay processing
		switch($paydata['vads_result']){
			case SP_PAYMENT_ACCEPTED :
			
				// mark transaction (order record) as abandonned
			    $blocktable = ($pinned) ? 'block_pinned' : 'block_instance' ;
			    if (!$instance = get_record($blocktable, 'id', $instanceid)){
					courseshop_trace("[$transid] SystemPay Internal Error : Bad Block ID $instanceid ");
					die;
			    }
			    $theBlock = block_instance('courseshop', $instance);
				$aFullBill = courseshop_get_full_bill($transid, $theBlock);

				// processing bill changes
				if ($aFullBill->status == 'PENDING' || $aFullBill->status == 'DELAYED'){
				    $updatedbill->id = $aFullBill->id;
				    $updatedbill->onlinetransactionid = $paydata['vads_site_id'].'-'.$paydata['vads_trans_date'].'-'.$paydata['vads_trans_id'];
				    $aFullBill->status = $updatedbill->status = 'SOLDOUT';
				    $aFullBill->paiedamount = $paydata['vads_effective_amount'] / 100;
					$bill->remotestatus = $updatedbill->remotestatus = $vads_status[$paydata['vads_status']] ;
				    update_record('courseshop_bill', addslashes_object($updatedbill));
					courseshop_trace("[$transid]  SystemPay IPN : success, transferring to success controller");
							
					// now we need to execute non interactive production code
					// this SHOULD NOT be done by redirection as Systempay server might not 
					// handle this. Thus only use the controller and die afterwoods.
					
      				include_once $CFG->dirroot.'/blocks/courseshop/shop/success.controller.php';
      				die;
				}
				break;
			default:
			    $updatedbill->id = $aFullBill->id;
			    $updatedbill->status = 'FAILED';
				$bill->remotestatus = $vads_status[$paydata['vads_status']] ;
			    update_record('courseshop_bill', $updatedbill);
			    $tracereport = "[$transid] SystemPay IPN failure : {$vads_status[$paydata['vads_status']]} ";
			    if ($paydata['vads_status'] == SP_REQUEST_ERROR){
			    	$tracereport .= " / Error cause : {$vads_extra_status[$paydata['vads_extra_status']]} ";
			    }
				courseshop_trace($tracereport);
				die;				    
				break;
		}	
	}
	
	// provides global settings to add to courseshop settings when installed
	function settings(&$settings){
		global $CFG;
		
		$settings->add(new admin_setting_heading('block_courseshop_'.$this->name, get_string($this->name.'paymodeparams', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/'.$this->name.'/lang/'), ''));

		$settings->add(new admin_setting_configtext('block_courseshop_systempay_service_url', get_string('systempayserviceurl', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/systempay/lang/'),
		                   get_string('configsystempayserviceurl', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/systempay/lang/'), '', PARAM_TEXT));

		$settings->add(new admin_setting_configtext('block_courseshop_systempay_merchant_id', get_string('systempaymerchantid', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/systempay/lang/'),
		                   get_string('configsystempaymerchantid', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/systempay/lang/'), '', PARAM_TEXT));

		$settings->add(new admin_setting_configtext('block_courseshop_systempay_test_certificate', get_string('systempaytestcertificate', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/systempay/lang/'),
		                   get_string('configsystempaytestcertificate', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/systempay/lang/'), '', PARAM_TEXT));

		$settings->add(new admin_setting_configtext('block_courseshop_systempay_prod_certificate', get_string('systempayprodcertificate', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/systempay/lang/'),
		                   get_string('configsystempayprodcertificate', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/systempay/lang/'), '', PARAM_TEXT));


		// TODO : Generalize
		$countryoptions['FR'] = get_string('france', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/systempay/lang/');
		$countryoptions['EN'] = get_string('england', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/systempay/lang/');
		$countryoptions['DE'] = get_string('germany', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/systempay/lang/');
		$countryoptions['ES'] = get_string('spain', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/systempay/lang/');

		$settings->add(new admin_setting_configselect('block_courseshop_systempay_country', get_string('systempaycountry', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/systempay/lang/'),
		                   get_string('configsystempaycountry', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/systempay/lang/'), '', $countryoptions));

		$currencycodesoptions = array('978' => get_string('cur978', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'),
									'840' => get_string('cur840', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'),
									'756' => get_string('cur756', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'),
									'826' => get_string('cur826', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'),
									'124' => get_string('cur124', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'),
									// Yen 392 0 106 106
									// Peso Mexicain 484 2 106.55 10655
									// '949' => get_string('cur949', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'),
									// '036' => get_string('cur036', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'),
									// '554' => get_string('cur554', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'),
									// '578' => get_string('cur578', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'),
									// '986' => get_string('cur986', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'),
									// '032' => get_string('cur032', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'),
									// '116' => get_string('cur116', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'),
									// '901' => get_string('cur901', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'),
									// '752' => get_string('cur752', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'),
									// '208' => get_string('cur208', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'),
									// '702' => get_string('cur702', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/')
		);		
		
		$settings->add(new admin_setting_configselect('block_courseshop_systempay_currency_code', get_string('systempaycurrencycode', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/systempay/lang/'),
		                   get_string('configsystempaycurrencycode', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/systempay/lang/'), '', $currencycodesoptions));

	}
	
	/**
	* signs the parameter chain with seller's certificate
	*/
	function generate_sign($parms, $certificate){
		global $CFG;
				
		ksort($parms); // parameters need being sorted
		$signature = '';
		foreach ($parms as $key => $value){
			if(substr($key,0,5) == 'vads_') {
				$signature .= $value.'+';
			}
		}
		$signature .= $certificate;	// customerid is added at the end
		$encryptedsignature = sha1($signature);

		return($encryptedsignature);
	}
	
	/**
	* generates a suitable online id for the transaction.
	* real bill online id is : shopid (2d), payment_date (yyyymmdd as 8d), and the onlinetxid (6d) generated here.
	*/
	function generate_online_id(){
		global $CFG;

		$now = time();
		$midnight = mktime (0, 0, 0, date("n", $now), date("j", $now), date("Y", $now));
		if ($midnight > 0 + @$CFG->courseshop_systempay_lastmidnight){
			set_config('courseshop_systempay_idseq', 1);
			set_config('courseshop_systempay_lastmidnight', $midnight);
		}
		
		$onlinetxid = sprintf('%06d', ++$CFG->courseshop_systempay_idseq);
		set_config('courseshop_systempay_idseq', $CFG->courseshop_systempay_idseq);
		
		return $onlinetxid;		
	}
	
	/**
	* Get the systempay buffer and extract info from cryptic response.
	*/
	function decode_return_data(){
		global $CFG;
		
		// Récupération de la variable cryptée DATA
		$paydata = $_REQUEST;
		
		// decode private data as vads_order_info
		$paydata['return_context'] = base64_decode($paydata['vads_order_info']);
		
		if (empty($paydata['return_context'])){
	  		$systempayreturnerrorstr = get_string('emptymessage', 'block_courseshop');
			echo "<br/><center>$systempayreturnerrorstr</center><br/>";
			return false;
		}
				
		
		return $paydata;
	}

	/**
	* Get identifying data from the returned information from payment service.
	* guess transid from it
	* 
	* @returns an array with (cmd, block instance id, pinned, transid)
	*/
	function identify_transaction(){
		global $CFG;
				
		// decode private data as vads_order_info
		if (!$identity = base64_decode(@$_REQUEST['vads_order_info'])){
			return null;
		}
		return explode('-', $identity);		
	}
}
