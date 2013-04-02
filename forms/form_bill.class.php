<?php

    /**
    * Moodle - Modular Object-Oriented Dynamic Learning Environment
    *          http://moodle.org
    * Copyright (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
    *
    * This program is free software: you can redistribute it and/or modify
    * it under the terms of the GNU General Public License as published by
    * the Free Software Foundation, either version 2 of the License, or
    * (at your option) any later version.
    *
    * This program is distributed in the hope that it will be useful,
    * but WITHOUT ANY WARRANTY; without even the implied warranty of
    * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    * GNU General Public License for more details.
    *
    * You should have received a copy of the GNU General Public License
    * along with this program.  If not, see <http://www.gnu.org/licenses/>.
    *
    * Defines form to add a new billitem
    *
    * @package    block-courseshop
    * @subpackage classes
    * @reviewer   Valery Fremaux <valery.fremaux@club-internet.fr>
    * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
    * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
    *
    */

    // Security
    if (!defined('MOODLE_INTERNAL')) die("You are not authorized to run this file directly");

    require_once($CFG->libdir.'/formslib.php');
    require_once($CFG->dirroot.'/blocks/courseshop/locallib.php');

    class Bill_Form extends moodleform {
    	private $mode;
		private $blockid;
    	    	   	
    	function __construct($mode, $action, $blockid){
    		$this->blockid = $blockid;
			$this->mode = '_'.$mode;
    		parent::__construct($action);
        }
    	
    	function definition() {
    		global $CFG;
			
    		// Setting variables
    		$mform =& $this->_form;
									
			//Adding title and description
			$mform->addElement('html', print_heading(get_string($this->mode, 'block_courseshop'))); 
		
    		$attributes = 'size="47" maxlength="200"';
			$attributesshort = 'size="30" maxlength="200"';
			$attributesint = 'size="5" maxlength="200"';
			$attributesjscustomer = 'onchange = listClear(document.getElementById(\'id_useraccountid\'))';
			$attributesjsuser = 'onchange = listClear(document.getElementById(\'id_userid\'))';
			  	
        	// Adding fieldset
			
			
			$mform->addElement('hidden', 'cmd', $this->mode);
			
    	    $mform->addElement('hidden', 'billid');
				
    		$mform->addElement('text', 'title', get_string('billtitle', 'block_courseshop'), $attributesshort);
			$mform->setType('title', PARAM_TEXT);
					
			$custos = get_records('courseshop_customer');
			
			//getting the full name of customers
			$fullname_custo_select = array();
			$fullname_custo_select['0'] = get_string('choosecustomer', 'block_courseshop');
			
			foreach ($custos as $custo) {
			$fullname_custo_select[$custo->id] = fullname($custo);	
			}
			
			//select user whithout customer account
			$sqluser = 
			"SELECT u.id, u.lastname, u.firstname 
			FROM {$CFG->prefix}user as u
			WHERE u.id not in (select hasaccount from {$CFG->prefix}courseshop_customer)";
			
			$users = get_records_sql($sqluser);
			
			//getting the full names
			$fullname_user_select = array();
			$fullname_user_select['0'] = get_string('chooseuser', 'block_courseshop');
			
			foreach ($users as $user) {
			$fullname_user_select[$user->id] = fullname($user);
			}
						
			//set default for user select
										
			$userarray = array();
			
			$userarray[] = &$mform->createElement('select', 'userid', get_string('customers', 'block_courseshop'), $fullname_custo_select, $attributesjscustomer);
			$userarray[] = &$mform->createElement('select', 'useraccountid', get_string('users', 'block_courseshop'), $fullname_user_select, $attributesjsuser);
			
			$mform->addGroup($userarray, 'selectar', get_string('user', 'block_courseshop'), '&nbsp;'. get_string('or', 'block_courseshop').'&nbsp;', false); 
			
			$mform->setHelpButton('selectar', array('customer_account', get_string('customer_account', 'block_courseshop'), 'block_courseshop'));
			
			$lastordering = get_field_sql('SELECT max(ordering) from '.$CFG->prefix.'courseshop_bill');
			$lastordering += 1;
			
			
    		$mform->addElement('hidden', 'ordering', $lastordering);
    		
			$mform->addElement('htmleditor', 'abstract', get_string('abstract', 'block_courseshop').':');
											
			$radioarray = array();
			
			$radioarray[] = &$mform->createElement('radio', 'ignoretax', '', get_string('yes'), 0, $attributes);
			$radioarray[] = &$mform->createElement('radio', 'ignoretax', '', get_string('no'), 1, $attributes);
			
			$mform->addGroup($radioarray, 'radioar', get_string('allowtax', 'block_courseshop').':', array(' '), false);
    		
			$mform->setHelpButton('radioar', array('allowtax', get_string('allowingtax', 'block_courseshop'), 'block_courseshop'));
			
			$context = get_context_instance(CONTEXT_BLOCK, $this->blockid);
			$billeditors = get_users_by_capability($context, 'block/courseshop:beassigned', 'u.id, firstname, lastname');
			
			$editoropt = array();
			
			if ($billeditors) {
			
				foreach ($billeditors as $billeditor) { 
				$editoropt[$billeditor->id] = fullname($billeditor);
				}
				
			}
			
			$mform->addElement('select', 'assignedto', get_string('assignedto', 'block_courseshop').':', $editoropt);
			
			$mform->setHelpButton('assignedto', array('bill_assignation', get_string('bill_assignation', 'block_courseshop'), 'block_courseshop'));
							
			$status = array ('PENDING' => 'PENDING',
							 'PAYBACK'=>'PAYBACK',
							 'PARTIAL'=>'PARTIAL',
							 'SOLDOUT'=>'SOLDOUT',
							 'DELAYED'=>'DELAYED',
							 'RECOVERING'=>'RECOVERING',
							 'CANCELLED'=>'CANCELLED',
							 'WORKING'=>'WORKING'	);
							
			$mform->addElement('select', 'status', get_string('status', 'block_courseshop').':', $status);
						
			$worktype = array ( 'PROD' => 'PROD',
								'PACK' => 'PACK',
								'OTHER' => 'OTHER' );
			
			$mform->addElement('select', 'worktype', get_string('worktype', 'block_courseshop').':', $worktype);
			
			$paymode = array();
			
			$dir_name = $CFG->dirroot.'/blocks/courseshop/paymodes';
			$dir = opendir($dir_name) or die (print_string('dirnotfound', 'block_courseshop'));
			$i = 1;
			while ($var = readdir($dir)) { 
				if ($var != '.' && $var != '..' && $var != '.svn' && $var != 'CVS') {
					if (is_dir($dir_name.'/'.$var)) {
						//$member = "enable$var";
						//if (empty($theBlock->config->$member)){
						$paymodes[$var] = get_string($var, 'block_courseshop');
						$i = $i +1;
						//}
					}	
				}	
			}

			$currencies = courseshop_get_supported_currencies();
			$mform->addElement('select', 'currency', get_string('currency', 'block_courseshop').':', $currencies);
			$mform->setDefault('currency', $CFG->block_courseshop_defaultcurrency);

			$mform->addElement('select', 'paymode', get_string('paymodes', 'block_courseshop').':', $paymodes);
			
			$mform->addElement('date_selector', 'timetodo', get_string('timetodo', 'block_courseshop')); 
						
			$mform->addElement('date_selector', 'expectedpaiement', get_string('expectedpaiement', 'block_courseshop').':');
						
    		// Adding submit and reset button
			
            $buttonarray = array();
        	$buttonarray[] = &$mform->createElement('submit', 'go_submit', get_string('submit'));
        	$buttonarray[] = &$mform->createElement('cancel', 'go_cancel', get_string('cancel'));
            
            $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
            $mform->closeHeaderBefore('buttonar');		
    	}
    }
?>