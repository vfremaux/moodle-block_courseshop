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
    require_once($CFG->dirroot.'/blocks/courseshop/locallib.php');

    class Set_Form extends moodleform {
    	private $mode;
    	private $catalog;
    	
    	function __construct($setid, $mode, &$catalog, $action){
    		$this->setid = $setid;
    		$this->mode = $mode;
    		$this->catalog = $catalog;
        	parent::__construct($action);
        }
    	
    	function definition() {
    		global $CFG;
    		
    		// Setting variables
    		$mform =& $this->_form;

	    	$mform->addElement('hidden', 'catalogid');
			$mform->setDefault('catalogid', $this->catalog->id);		

        	$mform->addElement('hidden', 'isset', 1);
        	$mform->addElement('hidden', 'price1', 0);
        	$mform->addElement('hidden', 'price2', 0);
        	$mform->addElement('hidden', 'price3', 0);
        	$mform->addElement('hidden', 'taxcode', 0);
        	$mform->addElement('hidden', 'stock', 0);
        	$mform->addElement('hidden', 'sold', 0);
        	$mform->addElement('hidden', 'maxdeliveryquant', 0);
        	$mform->addElement('hidden', 'setid', $this->setid);
    		
    		// Adding title and description
        	$mform->addElement('html', print_heading(get_string($this->mode.'set', 'block_courseshop')));
    		
    		// Adding fieldset
    		$attributes = 'size="50" maxlength="200"';
    		$attributesshort = 'size="24" maxlength="24"';
    		$attributes_description = 'cols="50" rows="8"';

    		$mform->addElement('text', 'code', get_string('code', 'block_courseshop'), $attributes);
    		$mform->setType('code', PARAM_RAW);
    		$mform->addElement('text', 'name', get_string('name', 'block_courseshop'), $attributes);
    		$mform->setType('name', PARAM_TEXT);
    		$mform->addElement('text', 'shortname', get_string('label', 'block_courseshop'), $attributesshort);
    		$mform->setType('shortname', PARAM_TEXT);
            
            $mform->addElement('htmleditor', 'description', get_string('description'));
		    $mform->setType('description', PARAM_CLEANHTML);
			$mform->setHelpButton('description', array('description', get_string('helpdescription', 'block_courseshop'), 'block_courseshop')); 
    		
            $radiogroup = array();
		    $radiogroup[] = &$mform->createElement('radio', 'onlyforloggedin', '', get_string('loggedin', 'block_courseshop'), 1);
		    $radiogroup[] = &$mform->createElement('radio', 'onlyforloggedin', '', get_string('both', 'block_courseshop'), 0);
		    $radiogroup[] = &$mform->createElement('radio', 'onlyforloggedin', '', get_string('loggedout', 'block_courseshop'), -1);
		    $mform->addGroup($radiogroup, 'loggedingroup', get_string('onlyfor', 'block_courseshop'), array(' '), false);
		    $mform->setDefault('onlyforloggedin', 0);

    		if ($cats = courseshop_get_categories($this->catalog)){
        		foreach($cats as $cat){
        		    $sectionopts[$cat->id] = $cat->name;
        		}
    		    $mform->addElement('select', 'categoryid', get_string('section', 'block_courseshop'), $sectionopts);
    		    $mform->setType('categoryid', PARAM_INT);
        	} else {
    		    $mform->addElement('static', 'nocats', get_string('nocats', 'block_courseshop'));
        	}

    		$mform->addElement('text', 'leafleturl', get_string('leafleturl', 'block_courseshop'), $attributes);
		    $mform->setType('leafleturl', PARAM_TEXT);

    		$mform->addElement('text', 'image', get_string('image', 'block_courseshop'), $attributes);
		    $mform->setType('image', PARAM_TEXT);

    		$mform->addElement('text', 'thumb', get_string('thumbnail', 'block_courseshop'), $attributes);
		    $mform->setType('thumb', PARAM_TEXT);

    		$mform->addElement('text', 'requireddata', get_string('requireddata', 'block_courseshop'), $attributes);
		    $mform->setType('requireddata', PARAM_TEXT);

    		$statusopts = courseshop_get_status();
    		$mform->addElement('select', 'status', get_string('status', 'block_courseshop'), $statusopts);
		    $mform->setType('status', PARAM_INT);

            $mform->addElement('htmleditor', 'notes', get_string('notes', 'block_courseshop'));
		    $mform->setType('notes', PARAM_CLEANHTML);
			$mform->setHelpButton('notes', array('description', get_string('helpnote', 'block_courseshop'), 'block_courseshop'));

    		$mform->addRule('shortname', null, 'required');
    		$mform->addRule('name', null, 'required');
			$mform->addRule('categoryid', null, 'required');

    		// Adding submit and reset button
            $buttonarray = array();
        	$buttonarray[] = &$mform->createElement('submit', 'go_submit', get_string('submit'));
        	$buttonarray[] = &$mform->createElement('cancel', 'go_cancel', get_string('cancel'));
            
            $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
            $mform->closeHeaderBefore('buttonar');		
    	}
    }
?>