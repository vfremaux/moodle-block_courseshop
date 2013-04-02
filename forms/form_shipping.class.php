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
* Defines form to add a new project
*
* @package    block-prf-catalogue
* @subpackage classes
* @reviewer   Valery Fremaux <valery.fremaux@club-internet.fr>
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
* @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
*
*/

/// Security
	
if (!defined('MOODLE_INTERNAL')) die("You are not authorized to run this file directly");

require_once $CFG->libdir.'/formslib.php';

class ProductShipping_Form extends moodleform {
	private $mode;
	private $catalogid;
	private $shippingid;
	private $zoneid;
	private $productcode;
	
	function __construct($mode, $catalogid, $zoneid, $productcode, $action){
		$this->mode = $mode;
		$this->catalogid = $catalogid;
		if ($zoneid && $productcode){
			if ($shipping = get_record('courseshop_catalogshipping', 'zoneid', $zoneid, 'productcode', $productcode)){
				$this->shippingid = $shipping->id;
			}
		}
		$this->zoneid = $zoneid;
		$this->productcode = $productcode;
    	parent::__construct($action);
    }
	
	function definition() {
		global $CFG;
		$codeattributes = 'size="10" maxlength="10"';
		// Setting variables
		$mform =& $this->_form;
		
		//Title and description

		$mform->addElement('html', print_heading(get_string($this->mode.'shipping', 'block_courseshop'))); 
		if ($this->mode == 'edit'){
			$mform->addElement('hidden', 'shippingid', $this->shippingid);
		}
		if ($this->productcode){
			$mform->addElement('html', '<div class="fitem"><div class="fitemtitle">'.get_string('productcode', 'block_courseshop').'</div><div class="felement">'.$this->productcode.'</div></div>'); // Shipzone code
			$mform->addElement('hidden', 'productcode', $this->productcode);
		} else {
			$sql = "
				SELECT
					ci.code,
					ci.name
				FROM
					{$CFG->prefix}courseshop_catalogitem ci
				WHERE
					ci.catalogid = $this->catalogid AND
					ci.code NOT IN (SELECT productcode FROM {$CFG->prefix}courseshop_catalogshipping WHERE zoneid = $this->zoneid )
				ORDER BY code
			";
			$productoptions = get_records_sql_menu($sql);
			$mform->addElement('select', 'productcode', get_string('productcode', 'block_courseshop'), $productoptions); // Product code
		}
		if ($this->zoneid){
			$zone = get_record('courseshop_catalogshipzone', 'id', $this->zoneid);
			$mform->addElement('html', '<div class="fitem">><div class="fitemtitle">'.get_string('zoneid', 'block_courseshop').':</div><div class="felement">'.$zone->description.'</div></div>');
			$mform->addElement('hidden', 'zoneid', $zone->id);
		} else {
			$zonesql = "
				SELECT
					csz.id,
					csz.descriprtion
				FROM
					{$CFG->prefix}courseshop_catalogshipzone csz
				WHERE
					csz.id NOT IN (SELECT zoneid FROM {$CFG->prefix}courseshop_catalogshipping WHERE productcode = $this->productcode )
				ORDER BY descrioption
			";
			$zoneoptions = get_records_sql_menu($sql);
			$mform->addElement('select', 'zoneid', get_string('zoneid', 'block_courseshop'), $zoneoptions);
		}
								
		$mform->addElement('text', 'value', get_string('shippingfixedvalue', 'block_courseshop')); //
		$mform->addElement('text', 'formula', get_string('formula', 'block_courseshop')); // Shipzone description
		$mform->addElement('text', 'a', get_string('param_a', 'block_courseshop')); //
		$mform->addElement('text', 'b', get_string('param_b', 'block_courseshop')); //
		$mform->addElement('text', 'c', get_string('param_c', 'block_courseshop')); //

		//Informations required
		$mform->addRule('productcode', null, 'required');
		$mform->addRule('zoneid', null, 'required');
		          
		// Adding submit and reset button
        $buttonarray = array();
    	$buttonarray[] = &$mform->createElement('submit', 'go_submit', get_string('submit'));
    	$buttonarray[] = &$mform->createElement('cancel', 'go_cancel', get_string('cancel'));
        		
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');		
	} 
		
	function validation(){
	}
}

?>