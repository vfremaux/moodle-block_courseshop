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
	
	

    // Security
	
	
    if (!defined('MOODLE_INTERNAL')) die("You are not authorized to run this file directly");

    require_once($CFG->libdir.'/formslib.php');

    class Category_Form extends moodleform {
    	private $mode;
    	
    	function __construct($mode, $action){
    		$this->mode = $mode;
        	parent::__construct($action);
        }
    	
    	function definition() {
    		global $CFG;			

    		$attributes = array('size' => 47,  'maxlength' => 200);
			$attributes_description = array('cols' => 50, 'rows' => 8);
    		// Setting variables
    		$mform =& $this->_form;
			
			//Title and descripton
			
			$mform->addElement('html', print_heading(get_string($this->mode.'category', 'block_courseshop'), 'center', 2, 'main', true)); 
			$mform->addElement('text', 'name', get_string('categoryname', 'block_courseshop'), $attributes); //Category name field
			$mform->addElement('htmleditor', 'description', get_string('categorydescription', 'block_courseshop')); //Category description field
			$mform->setHelpButton('description', array('description', get_string('helpdescription', 'block_courseshop'), 'block_courseshop'));
			
			
									
			//Informations required
			$mform->addRule('name', null, 'required');
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