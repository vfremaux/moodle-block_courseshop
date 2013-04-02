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

/// Security

if (!defined('MOODLE_INTERNAL')) die("You are not authorized to run this file directly");

global $CFG; 

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/blocks/courseshop/locallib.php');

class BillItem_Form extends moodleform {
	private $mode;
	
	private $bill;
	
	function __construct($mode, $bill, $action){
		$this->mode = $mode;
		$this->bill = $bill;
    	parent::__construct($action);
    }
	
	function definition() {
		global $CFG;

		// Setting variables
		$mform =& $this->_form;
		
		// Adding title and description
    	$mform->addElement('html', print_heading(get_string($this->mode.'billitem', 'block_courseshop')));
    	// $error_float = print_string('error_costNotAFloat', 'block_courseshop');
		// print_string("error_quantityNotAFloat");
    	$js = "
			<script type=\"text/javascript\">
			function calculatePrice() {
			    var cost = parseFloat(document.billItem.unitCost.value);
			    var quantity = parseFloat(document.billItem.quantity.value);
			    if (isNaN(cost)) {
			        alert(\"error\");
			        document.billItem.unitCost.value = \"0.00\";
			        exit();
			    };
			    if (isNaN(quantity)) {
			        alert(\"error2\");
			        document.billItem.quantity.value = \"1\";
			        exit();
			    };
			    var priceDisplay = document.getElementById('totalPrice'); 
			    var price = new Number(cost * quantity);
			    priceDisplay.innerHTML = price.toFixed(\"2\");
			}
			</script>
		";        	
        	
    	$ordering = ($this->mode == 'add') ? $this->bill->maxordering + 1 : 0 ;
    	
    	$mform->addElement('html', $js);
		
		// Adding fieldset
		$attributes = 'size="50" maxlength="200"';
		$attributesshort = 'size="24" maxlength="24"';
		$attributes_description = 'cols="50" rows="8"';
		
		$mform->addElement('static', 'billtitle', get_string('bill', 'block_courseshop'), "ORD-{$this->bill->emissiondate}-{$this->bill->id}");
		
		$lastordering = get_field_sql('SELECT max(ordering) from '.$CFG->prefix.'courseshop_bill');
		$lastordering = $lastordering + 1;			
		$mform->addElement('hidden', 'ordering', $lastordering);
		
		$mform->addElement('text', 'itemcode', get_string('code', 'block_courseshop'), $attributesshort);
		$mform->addElement('htmleditor', 'abstract', get_string('abstract', 'block_courseshop'), $attributesshort);

        $mform->addElement('htmleditor', 'description', get_string('description'));
	    $mform->setType('description', PARAM_CLEANHTML);

		$mform->addElement('date_selector', 'delay', get_string('timetodo', 'block_courseshop'));

		$mform->addElement('text', 'unitcost', get_string('unittex', 'block_courseshop'), $attributesshort);

		$mform->addElement('text', 'quantity', get_string('quantity', 'block_courseshop').':', $attributesshort);

		$mform->addElement('static', 'totalprice', get_string('total'), "<span id=\"totalPrice\">0.00</span> ". $CFG->block_courseshop_defaultcurrency);
        
		$billid = required_param('billid');
		
		$bills = get_record('courseshop_bill', 'id', $billid);
		
		if ($bills->ignoretax == 0) {		
			$taxcodeopts = get_records_menu('courseshop_tax', '', '', 'title', 'id, title');			
    		$mform->addElement('select', 'taxcode', get_string('taxcode', 'block_courseshop'), $taxcodeopts);
    		$mform->setType('taxcode', PARAM_INT);			
		}

		// Adding submit and reset button
        $buttonarray = array();
    	$buttonarray[] = &$mform->createElement('submit', 'go_submit', get_string('submit'));
    	$buttonarray[] = &$mform->createElement('cancel', 'go_cancel', get_string('cancel'));
        
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');		
	}
}

?>