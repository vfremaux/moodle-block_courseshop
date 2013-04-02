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
* @package    block-courseshop
* @subpackage classes
* @reviewer   Valery Fremaux <valery.fremaux@club-internet.fr>
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
* @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
*
*/

/// Security

if (!defined('MOODLE_INTERNAL')) die("You are not authorized to run this file directly");

require_once($CFG->libdir.'/formslib.php');

class Customer_Form extends moodleform {
	private $mode;
	
	function __construct($mode, $action){
		$this->mode = $mode;
    	parent::__construct($action);
    }
	
	function definition() {
		global $CFG;
		
		include $CFG->dirroot.'/blocks/courseshop/country.php';
		// Setting variables
		$mform =& $this->_form;

		// Adding title and description
    	$mform->addElement('html', print_heading(get_string($this->mode.'customer', 'block_courseshop')));
		
		// Adding fieldset
		$attributes = 'size="45" maxlength="200"';
		$attributes_description = 'cols="50" rows="8"';
		
		$mform->addElement('text', 'firstname', get_string('customerfirstname', 'block_courseshop'));
        $mform->addElement('text', 'lastname', get_string('customerlastname','block_courseshop'));
   		$mform->addElement('text', 'address1', get_string('address1', 'block_courseshop'), $attributes);
		$mform->addElement('text', 'address2', get_string('address2', 'block_courseshop'), $attributes);
		$mform->addElement('text', 'zip', get_string('zip', 'block_courseshop'));
		$mform->addElement('text', 'city', get_string('city', 'block_courseshop'));
		$mform->addElement('text', 'email', get_string('email', 'block_courseshop'), $attributes);
		$choices = get_list_of_countries();
        $choices = array('' => get_string('selectacountry').'...') + $choices;
        $mform->addElement('select', 'country', get_string('taxcountry', 'block_courseshop'), $choices); 
		$mform->addElement('text', 'organisation', get_string('organisation', 'block_courseshop'));
		
		
		$mform->addRule('firstname', null, 'required');
		$mform->addRule('lastname', null, 'required');
		$mform->addRule('address1', null, 'required');
		$mform->addRule('zip', null, 'required');
		$mform->addRule('city', null, 'required');
		$mform->addRule('email', null, 'required');
		$mform->addRule('country', null, 'required');
		
		// Adding submit and reset button
       	$buttonarray = array();
    	$buttonarray[] = &$mform->createElement('submit', 'go_submit', get_string('submit'));
    	$buttonarray[] = &$mform->createElement('cancel', 'go_cancel', get_string('cancel'));
		$mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
		
		$mform->setHelpButton('firstname', array('shopform', get_string('help_informations', 'block_courseshop'), 'block_courseshop'));
		$mform->setHelpButton('lastname', array('shopform', get_string('help_informations', 'block_courseshop'), 'block_courseshop'));
		$mform->setHelpButton('address1', array('shopform', get_string('help_informations', 'block_courseshop'), 'block_courseshop'));
		$mform->setHelpButton('zip', array('shopform', get_string('help_informations', 'block_courseshop'), 'block_courseshop'));
		$mform->setHelpButton('city', array('shopform', get_string('help_informations', 'block_courseshop'), 'block_courseshop'));
		$mform->setHelpButton('email', array('shopform', get_string('help_informations', 'block_courseshop'), 'block_courseshop'));
		$mform->setHelpButton('country', array('shopform', get_string('help_informations', 'block_courseshop'), 'block_courseshop'));
			
	}
}

?>