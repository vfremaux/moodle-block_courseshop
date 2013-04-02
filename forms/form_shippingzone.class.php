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
include_once $CFG->dirroot.'/blocks/courseshop/country.php';

class ShippingZone_Form extends moodleform {
	private $mode;
	
	function __construct($mode, $action){
		$this->mode = $mode;
    	parent::__construct($action);
    }
	
	function definition() {
		global $CFG;
		$codeattributes = 'size="10" maxlength="10"';
		$applicattributes = 'size="50"';
		// Setting variables
		$mform =& $this->_form;
		
		//Title and descripton

		$mform->addElement('html', print_heading(get_string($this->mode.'shippingzone', 'block_courseshop'))); 
		$mform->addElement('text', 'zonecode', get_string('zonecode', 'block_courseshop'), $codeattributes); // Shipzone code
		$mform->addElement('text', 'description', get_string('description', 'block_courseshop')); // Shipzone description
		$mform->addElement('text', 'billscopeamount', get_string('billscopeamount', 'block_courseshop')); // Bill scope amount when bill applied

		$taxoptions = get_records_menu('courseshop_tax', '', '', 'title', 'id,title');
		$mform->addElement('select', 'taxid', get_string('tax', 'block_courseshop'), $taxoptions); // Bill scope amount when bill applied

		$mform->addElement('text', 'applicability', get_string('applicability', 'block_courseshop'), $applicattributes); // the formula used to check application of shipping zone
								
		//Informations required
		$mform->addRule('zonecode', null, 'required');
		$mform->addRule('description', null, 'required');
		          
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