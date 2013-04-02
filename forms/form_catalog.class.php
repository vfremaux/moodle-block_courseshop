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

    class Catalog_Form extends moodleform {
    	private $mode;
    	
    	function __construct($mode, $action){
    		$this->mode = $mode;
        	parent::__construct($action);
        }
    	
    	function definition() {
    		global $CFG;
    		
    		// Setting variables
    		$mform =& $this->_form;
    		
    		// Adding title and description
        	$mform->addElement('html', print_heading(get_string($this->mode.'catalog', 'block_courseshop')));
    		
    		// Adding fieldset
    		$attributes = 'size="50" maxlength="200"';
    		$attributes_description = 'cols="50" rows="8"';

    		$mform->addElement('hidden', 'catalogid');

    		$mform->addElement('text', 'name', get_string('name', 'block_courseshop'), $attributes);
            
            $mform->addElement('htmleditor', 'description', get_string('description', 'block_courseshop'));
			
			$mform->setHelpButton('description', array('description', get_string('helpdescription', 'block_courseshop'), 'block_courseshop'));
    		
    		$mform->addElement('htmleditor', 'salesconditions', get_string('salesconditions', 'block_courseshop'));
    				
    		$mform->addRule('name', null, 'required');
    		$mform->addRule('description', null, 'required');

            // Add catalog mode settings

            $sql = "
               SELECT DISTINCT
                  ci.*
               FROM
                  {$CFG->prefix}courseshop_catalog as ci
               WHERE
                 ci.id = ci.groupid
            ";            
            $masterCatalogOptions = array();
            if ($masterCatalogs = get_records_sql($sql)){
            
                foreach($masterCatalogs as $acat){
                    $masterCatalogOptions[$acat->id] = $acat->name;
                }
            }

            $linkedarray = array();
            $linkedarray[] = &MoodleQuickForm::createElement('radio', 'linked', '', get_string('standalone', 'block_courseshop'), 'free');
            $linkedarray[] = &MoodleQuickForm::createElement('radio', 'linked', '', get_string('master', 'block_courseshop'), 'master');
            if (!empty($masterCatalogOptions)){
                $linkedarray[] = &MoodleQuickForm::createElement('radio', 'linked', '', get_string('slaveto', 'block_courseshop'), 'slave');
        		$linkedarray[] = &MoodleQuickForm::createElement('select', 'groupid', '', $masterCatalogOptions);
        	}
            $mform->addGroup($linkedarray, 'linkedarray', '', array(' '), false);            
            $mform->setDefault('linked', 'free');
            
    		
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