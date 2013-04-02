<?php

require_once $CFG->dirroot.'/blocks/courseshop/paymodes/paymode.class.php';
require_once $CFG->dirroot.'/blocks/courseshop/locallib.php';

# Response codes
define('MRCNT_PAYMENT_ACCEPTED', '00'); // Autorisation acceptée
define('MRCNT_MAX_LIMIT_REACHED', '02'); // Demande d’autorisation par téléphone à la banque à cause d’un dépassement du plafond d’autorisation sur la carte, si vous êtes autorisé à forcer les transactions. (cf. Annexe L) Dans le cas contraire, vous obtiendrez un code 05.
define('MRCNT_INVALID_MERCHANT', '03'); // Champ merchant_id invalide, vérifier la valeur renseignée dans la requête Contrat de vente à distance inexistant, contacter votre banque.
define('MRCNT_PAYMENT_REJECTED', '05'); // Autorisation refusée
define('MRCNT_INVALID_TX', '12'); // Transaction invalide, vérifier les paramètres transférés dans la requête.
define('MRCNT_USER_CANCELLED', '17'); // Annulation de l’internaute
define('MRCNT_FORMAT_ERROR', '30'); // Erreur de format.
define('MRCNT_POSSIBLY_EVIL', '34'); // Suspicion de fraude
define('MRCNT_MAX_TRIES', '75'); // Nombre de tentatives de saisie du numéro de carte dépassé.
define('MRCNT_UNVAILABLE', '90'); // Service temporairement indisponible

class courseshop_paymode_mercanet extends courseshop_paymode{

	function __construct(&$shopblockinstance){
		parent::__construct('mercanet', $shopblockinstance, true, true);
	}
		
	// prints a payment porlet in an order form
	function print_payment_portlet(&$portlet){
		global $CFG;
		
		echo '<table id="mercanet_panel"><tr><td>';
		print_string('mercanetDoorTransferText', 'block_courseshop');
        echo '</td></tr>';
		echo '<tr><td align="right"><br />';

	   	$portlet->sessionid = session_id();
	   	$portlet->amount = $portlet->totaltaxedamount;
	   	$portlet->onlinetransactionid = $this->generate_online_id();
	   	echo "Transaction ID : ".$portlet->onlinetransactionid;
	   	$portlet->returnurl = $CFG->wwwroot."/blocks/courseshop/paymodes/mercanet/process.php";
	   	$portlet->cancelurl = $CFG->wwwroot."/blocks/courseshop/paymodes/mercanet/cancel.php";
	   	$portlet->ipnurl = $CFG->wwwroot."/blocks/courseshop/paymodes/mercanet/mercanet_ipn.php";
	   	
	   	include($CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/mercanetAPI.portlet.php');

		echo '<center><p><span class="procedureOrdering"></span>';
		/*
		$payonlinestr = get_string('payonline', 'block_courseshop');
		echo "<input type=\"button\" name=\"go_btn\" value=\"$payonlinestr\" onclick=\"document.confirmation.submit();\" />";
		*/
		echo '<p><span class="courseshop-procedure-cancel">X</span> ';
		$cancelstr = get_string('cancel');
		echo "<a href=\"{$CFG->wwwroot}/blocks/courseshop/shop/view.php?view=shop&id={$this->shopblock->instance->id}&pinned={$this->shopblock->pinned}\" class=\"smalltext\">$cancelstr</a>";	
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
	    $updatedbill->onlinetransactionid = $paydata['merchant_id'].'-'.$paydata['transmission_date'].'-'.$paydata['transaction_id'];
	    $updatedbill->paymode = 'mercanet';
	    $updatedbill->status = 'CANCELLED';
	    update_record('courseshop_bill', $updatedbill);

		// cancel shopping cart
		unset($SESSION->shoppingcart);
		
		redirect($CFG->wwwroot.'/blocks/courseshop/shop/view.php?view=shop&id='.$instanceid.'&pinned='.$pinned);
	}

	// processes an explicit payment return
	function process(){
		global $CFG, $SESSION;

		$paydata = $this->decode_return_data();				
		
		//	Erreur, affiche le message d'erreur
	
		if ($paydata['code'] != 0 && !empty($paydata['error'])){
			$mercanetapierrorstr = get_string('mercanetapierror', 'block_courseshop');
			echo "<center><b>{$mercanetapierrorstr}</b></center>";
			echo '<br/><br/>';
			$mercaneterror = get_string('mercaneterror', 'block_courseshop', $paydata['error']);
			echo $mercaneterror.'<br/>';
			return false;
		} else {
			// OK, affichage des champs de la réponse
			if (debugging() && $CFG->block_courseshop_test){
				# OK, affichage du mode DEBUG si activé
				echo "<center>\n";
				echo "<H3>R&eacute;ponse manuelle du serveur MERCANET</H3>\n";
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
			if ($aFullBill->status == 'DELAYED'){
				// processing bill changes
				if ($paydata['response_code'] == MRCNT_PAYMENT_ACCEPTED){
				    $updatedbill->id = $aFullBill->id;
				    $updatedbill->onlinetransactionid = $paydata['merchant_id'].'-'.$paydata['transmission_date'].'-'.$paydata['transaction_id'];
				    $aFullBill->status = $updatedbill->status = 'SOLDOUT';
				    update_record('courseshop_bill', $updatedbill);
				    
				    // redirect to success for ordering production with significant data
				    courseshop_trace("[$transid] Mercanet : Transation Complete, transferring to success end point");
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

			if ($aFullBill->status == 'SOLDOUT'){
			    redirect($CFG->wwwroot.'/blocks/courseshop/shop/view.php?view=success&id='.$instanceid.'&cmd=finish&pinned='.$pinned.'&transid='.$transid);
			}
		}
	}

	// processes a payment asynchronoous confirmation
	function process_ipn(){
		global $CFG, $SESSION;

		$paydata = $this->decode_return_data();				
		
		//	Erreur, affiche le message d'erreur
	
		if ($paydata['code'] != 0 && !empty($paydata['error'])){
			$mercanetapierrorstr = get_string('mercanetapierror', 'block_courseshop');
			courseshop_trace("Mercanet IPN : {$mercanetapierrorstr} : ".$paydata['error']);
			die;
		} else {
			// OK, affichage des champs de la réponse

			list($cmd, $instanceid, $pinned, $transid) = explode('-', $paydata['return_context']);
			courseshop_trace("[$transid] Mercanet IPN processing");
	
			// mark transaction (order record) as abandonned
		    $blocktable = ($pinned) ? 'block_pinned' : 'block_instance' ;
		    if (!$instance = get_record($blocktable, 'id', $instanceid)){
		        error('Invalid block');
		    }
		    $theBlock = block_instance('courseshop', $instance);
			$aFullBill = courseshop_get_full_bill($transid, $theBlock);

			// processing bill changes
			if ($aFullBill->status == 'PENDING' || $aFullBill->status == 'DELAYED'){
				if ($paydata['response_code'] == MRCNT_PAYMENT_ACCEPTED){
				    $updatedbill->id = $aFullBill->id;
				    $updatedbill->onlinetransactionid = $paydata['merchant_id'].'-'.$paydata['transmission_date'].'-'.$paydata['transaction_id'];
				    $bill->status = $updatedbill->status = 'SOLDOUT';
				    update_record('courseshop_bill', $updatedbill);
					courseshop_trace("[$transid]  Mercanet IPN : success, transferring to success controller");
					
					// now we need to execute non interactive production code
					// this SHOULD NOT be done by redirection as Mercanet server migyht not 
					// handle this. Thus only use the controller and die afterwwods.
					
      				include_once $CFG->dirroot.'/blocks/courseshop/shop/success.controller.php';
      				die;
					
				} else {
				    $updatedbill->id = $aFullBill->id;
				    $updatedbill->status = 'FAILED';
				    update_record('courseshop_bill', $updatedbill);
					courseshop_trace("[$transid] Mercanet IPN failure : ".$paydata['response_code']);
					die;				    
				}
			}
		}
	}
	
	// provides global settings to add to courseshop settings when installed
	function settings(&$settings){
		global $CFG;
		
		$settings->add(new admin_setting_heading('block_courseshop_'.$this->name, get_string($this->name.'paymodeparams', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/'.$this->name.'/lang/'), ''));

		$settings->add(new admin_setting_configtext('block_courseshop_mercanet_merchant_id', get_string('mercanetmerchantid', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'),
		                   get_string('configmercanetmerchantid', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'), '', PARAM_TEXT));

		// TODO : Generalize
		$countryoptions['fr'] = get_string('france', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/');
		$countryoptions['be'] = get_string('belgium', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/');
		$countryoptions['en'] = get_string('england', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/');
		$countryoptions['de'] = get_string('germany', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/');
		$countryoptions['es'] = get_string('spain', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/');
		$settings->add(new admin_setting_configselect('block_courseshop_mercanet_country', get_string('mercanetcountry', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'),
		                   get_string('configmercanetcountry', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'), '', $countryoptions));

		$settings->add(new admin_setting_heading('block_courseshop_mercanet_generatepathfile', '', '<a href="'.$CFG->wwwroot.'/blocks/courseshop/paymodes/mercanet/makepathfile.php">'.get_string('makepathfile', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/').'</a>'));
		                   
		$currencycodesoptions = array('978' => get_string('cur978', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'),
									'840' => get_string('cur840', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'),
									'756' => get_string('cur756', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'),
									'826' => get_string('cur826', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'),
									'124' => get_string('cur124', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'),
									// Yen 392 0 106 106
									// Peso Mexicain 484 2 106.55 10655
									'949' => get_string('cur949', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'),
									'036' => get_string('cur036', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'),
									'554' => get_string('cur554', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'),
									'578' => get_string('cur578', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'),
									'986' => get_string('cur986', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'),
									'032' => get_string('cur032', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'),
									'116' => get_string('cur116', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'),
									'901' => get_string('cur901', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'),
									'752' => get_string('cur752', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'),
									'208' => get_string('cur208', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'),
									'702' => get_string('cur702', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'));		
		
		$settings->add(new admin_setting_configselect('block_courseshop_mercanet_currency_code', get_string('mercanetcurrencycode', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'),
		                   get_string('configmercanetcurrencycode', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'), '', $currencycodesoptions));

		$processoroptions = array('32' => '32 bits', '64' => '64 bits');
		$settings->add(new admin_setting_configselect('block_courseshop_mercanet_processor_type', get_string('mercanetprocessortype', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'),
		                   get_string('configmercanetprocessortype', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'), '', $processoroptions));
	}
	
	/**
	* Generates the realpath file for the required implementation from the template file. 
	*/
	function generate_pathfile(){
		global $CFG;
		
		$os = (preg_match('/Linux/i', $CFG->os)) ? 'linux' : 'win' ;
		
		$pathfiletemplate = $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/mercanet_615_PLUGIN_'.$os.$CFG->block_courseshop_mercanet_processor_type.'/param/pathfile.tpl';
		$pathfile = $this->get_pathfile($os);
		
		echo $pathfiletemplate;
		assert(file_exists($pathfiletemplate));

		$tmp = implode('', file($CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/mercanet_615_PLUGIN_'.$os.$CFG->block_courseshop_mercanet_processor_type.'/param/pathfile.tpl'));

		if ($os == 'linux'){
			$tmp = str_replace('<%%DIRROOT%%>', $CFG->dirroot, $tmp);
			$tmp = str_replace('<%%DATAROOT%%>', $CFG->dataroot, $tmp);
		} else {
			// make exact windows slashing for writing pathfile
			$tmp = str_replace('<%%DIRROOT%%>', str_replace('/', '\\', $CFG->dirroot), $tmp);
			$tmp = str_replace('<%%DATAROOT%%>', str_replace('/', '\\', $CFG->dataroot), $tmp);
			$tmp = str_replace('<%%CERTIFICATEID%%>', $CFG->block_courseshop_mercanet_certificate_id, $tmp);
			$mercanetdebug = ($CFG->block_courseshop_test) ? 'YES' : 'NO';
			$tmp = str_replace('<%%DEBUG%%>', $mercanetdebug, $tmp);
			$tmp = str_replace('<%%COUNTRY%%>', $CFG->block_courseshop_mercanet_country, $tmp);
			
		}
		
		if ($PATHFILE = fopen($pathfile, 'w')){
			fputs($PATHFILE, $tmp);
			fclose($PATHFILE);
			notice('pathfile generated', $CFG->wwwroot.'/admin/settings.php?section=blocksettingcourseshop');
		} else {
			notice('Pathfile is not writable. Check file permissions on system. This is a very SENSIBLE file. Don\'t forget to protect it back after operation.', $CFG->wwwroot.'/admin/settings.php?section=blocksettingcourseshop');
		}
	}	
	
	/**
	* returns pathfile location
	*/
	function get_pathfile($os){
		global $CFG;

		if (!isset($CFG->block_courseshop_mercanet_processor_type)){
			$CFG->block_courseshop_mercanet_processor_type = '32';
		}

		if ($os == 'linux'){
			return $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/mercanet_615_PLUGIN_'.$os.$CFG->block_courseshop_mercanet_processor_type.'/param/pathfile';
		} else {
			return str_replace('/', "\\", $CFG->dirroot).'\\blocks\\courseshop\\paymodes\\mercanet\\mercanet_615_PLUGIN_'.$os.$CFG->block_courseshop_mercanet_processor_type.'\\param\\pathfile';
		}
	}
	
	/**
	* returns mercanet request form generator location
	*/
	function get_request_bin($os){
		global $CFG;
		
		$exeextension = ($os == 'linux') ? '' : '.exe';
		$relpath = ($os == 'linux') ? 'static/' : '';
		return $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/mercanet_615_PLUGIN_'.$os.$CFG->block_courseshop_mercanet_processor_type.'/bin/'.$relpath.'request'.$exeextension;
	}

	/**
	* returns mercanet request form response decoder
	*/
	function get_response_bin($os){
		global $CFG;

		$exeextension = ($os == 'linux') ? '' : '.exe';
		$relpath = ($os == 'linux') ? 'static/' : '';
		return $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/mercanet_615_PLUGIN_'.$os.$CFG->block_courseshop_mercanet_processor_type.'/bin/'.$relpath.'response'.$exeextension;
	}
	
	/**
	* generates a suitable online id for the transaction.
	* real bill online id is : merchant_country, merchant_id, payment_date, and the onlinetxid generated here.
	*/
	function generate_online_id(){
		global $CFG;

		$now = time();
		$midnight = mktime (0, 0, 0, date("n", $now), date("j", $now), date("Y", $now));
		if ($midnight > 0 + @$CFG->courseshop_mercanet_lastmidnight){
			set_config('courseshop_mercanet_idseq', 1);
			set_config('courseshop_mercanet_lastmidnight', $midnight);
		}
		
		$onlinetxid = sprintf('%06d', ++$CFG->courseshop_mercanet_idseq);
		set_config('courseshop_mercanet_idseq', $CFG->courseshop_mercanet_idseq);
		
		return $onlinetxid;		
	}
	
	/**
	* Get the mercanet buffer and extract info from cryptic response.
	*/
	function decode_return_data(){
		global $CFG;
		
		// Récupération de la variable cryptée DATA
		$message = 'message='.$_POST['DATA'];
		
		if (empty($message)){
	  		$mercanetreturnerrorstr = get_string('emptymessage', 'block_courseshop');
			echo "<br/><center>$mercanetreturnerrorstr</center><br/>";
			return false;
		}
		
		// Initialisation du chemin du fichier pathfile (à modifier)
	    //   ex :
	    //    -> Windows : $pathfile="pathfile=c:/repertoire/pathfile";
	    //    -> Unix    : $pathfile="pathfile=/home/repertoire/pathfile";
	   
		$os = (preg_match('/Linux/i', $CFG->os)) ? 'linux' : 'win' ;
		$pathfile = 'pathfile='.$this->get_pathfile($os);
	
		// Initialisation du chemin de l'executable response (à modifier)
		// ex :
		// -> Windows : $path_bin = "c:/repertoire/bin/response";
		// -> Unix    : $path_bin = "/home/repertoire/bin/response";
		//
	
		$path_bin = $this->get_response_bin($os);
	
		// Appel du binaire response
	  	$message = escapeshellcmd($message);
		$result = exec("$path_bin $pathfile $message");
		
		//	Sortie de la fonction : !code!error!v1!v2!v3!...!v29
		//		- code=0	: la fonction retourne les données de la transaction dans les variables v1, v2, ...
		//				: Ces variables sont décrites dans le GUIDE DU PROGRAMMEUR
		//		- code=-1 	: La fonction retourne un message d'erreur dans la variable error
	
		//	on separe les differents champs et on les met dans une variable tableau
	
		$paymentresponse = explode ("!", $result);

		//  analyse du code retour
	
	  	if (!$paymentresponse || (($paymentresponse[1] === '' /* code */) && ($paymentresponse[2] == '' /* error */))){
	  		$mercanetapierrorstr = get_string('errorcallingAPI', 'block_courseshop', $path_bin);
			echo "<br/><center>$mercanetapierrorstr</center><br/>";
			return false;
	 	}
	
		//	Récupération des données de la réponse
		
		$paydata['code'] = $paymentresponse[1];
		$paydata['error'] = $paymentresponse[2];
		$paydata['merchant_id'] = $paymentresponse[3];
		$paydata['merchant_country'] = $paymentresponse[4];
		$paydata['amount'] = $paymentresponse[5];
		$paydata['transaction_id'] = $paymentresponse[6];
		$paydata['payment_means'] = $paymentresponse[7];
		$paydata['transmission_date'] = $paymentresponse[8];
		$paydata['payment_time'] = $paymentresponse[9];
		$paydata['payment_date'] = $paymentresponse[10];
		$paydata['response_code'] = $paymentresponse[11];
		$paydata['payment_certificate'] = $paymentresponse[12];
		$paydata['authorisation_id'] = $paymentresponse[13];
		$paydata['currency_code'] = $paymentresponse[14];
		$paydata['card_number'] = $paymentresponse[15];
		$paydata['cvv_flag'] = $paymentresponse[16];
		$paydata['cvv_response_code'] = $paymentresponse[17];
		$paydata['bank_response_code'] = $paymentresponse[18];
		$paydata['complementary_code'] = $paymentresponse[19];
		$paydata['complementary_info'] = $paymentresponse[20];
		$paydata['return_context'] = $paymentresponse[21];
		$paydata['caddie'] = $paymentresponse[22];
		$paydata['receipt_complement'] = $paymentresponse[23];
		$paydata['merchant_language'] = $paymentresponse[24];
		$paydata['language'] = $paymentresponse[25];
		$paydata['customer_id'] = $paymentresponse[26];
		$paydata['order_id'] = $paymentresponse[27];
		$paydata['customer_email'] = $paymentresponse[28];
		$paydata['customer_ip_address'] = $paymentresponse[29];
		$paydata['capture_day'] = $paymentresponse[30];
		$paydata['capture_mode'] = $paymentresponse[31];
		$paydata['data'] = $paymentresponse[32];
		$paydata['order_validity'] = $paymentresponse[33];  
		$paydata['transaction_condition'] = $paymentresponse[34];
		$paydata['statement_reference'] = $paymentresponse[35];
		$paydata['card_validity'] = $paymentresponse[36];
		$paydata['score_value'] = $paymentresponse[37];
		$paydata['score_color'] = $paymentresponse[38];
		$paydata['score_info'] = $paymentresponse[39];
		$paydata['score_threshold'] = $paymentresponse[40];
		$paydata['score_profile'] = $paymentresponse[41];
		
		return $paydata;
	}
}
