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

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/blocks/courseshop/locallib.php');
require_once($CFG->dirroot.'/blocks/courseshop/forms/formelement_choosefile.php');

class Product_Form extends moodleform {
	private $mode;
	private $catalog;
	private $blockid;
	private $pinned;
	
	function __construct($mode, &$catalog, $blockid, $pinned, $action){
		$this->mode = $mode;
		$this->catalog = $catalog;
		$this->blockid = $blockid;
		$this->pinned = $pinned;
    	parent::__construct($action);
    }
	
	function definition() {
		global $CFG;
		
        if (!$this->catalog->isslave){
        	$sets = get_records_select('courseshop_catalogitem', " catalogid = '{$this->catalog->id}' AND (isset = 1 OR isset = 2) ", 'id, name');
        }

		
		// Setting variables
		$mform =& $this->_form;

    	$mform->addElement('hidden', 'catalogid');
		$mform->setDefault('catalogid', $this->catalog->id);
		
		// Adding title and description
    	$mform->addElement('html', print_heading(get_string($this->mode.'product', 'block_courseshop')));
		
		// Adding fieldset
		$attributes = 'size="50" maxlength="200"';
		$attributesshort = 'size="24" maxlength="24"';
		$attributeslong = 'size="60" maxlength="255"';
		$attributes_description = 'cols="50" rows="8"';

		$mform->addElement('text', 'code', get_string('code', 'block_courseshop'), $attributesshort);
		$mform->setType('code', PARAM_RAW);
		$mform->addElement('text', 'name', get_string('name', 'block_courseshop'), $attributeslong);
		$mform->setType('name', PARAM_TEXT);
		$mform->addElement('text', 'shortname', get_string('label', 'block_courseshop'), $attributesshort);
		$mform->setType('shortname', PARAM_TEXT);
        
        $mform->addElement('htmleditor', 'description', get_string('description'));
	    $mform->setType('description', PARAM_CLEANHTML);
		$mform->setHelpButton('description', array('description', get_string('helpdescription', 'block_courseshop'), 'block_courseshop')); 

		$mform->addElement('text', 'price1', get_string('unitprice1', 'block_courseshop'), $attributesshort);
	    $mform->setType('price1', PARAM_NUMBER);

        if (@$CFG->block_coursehop_prices > 1){
    		$mform->addElement('text', 'price2', get_string('unitprice1', 'block_courseshop'), $attributesshort);
		    $mform->setType('price2', PARAM_NUMBER);
    	}
        if (@$CFG->block_coursehop_prices > 2){
		    $mform->addElement('text', 'price3', get_string('unitprice1', 'block_courseshop'), $attributesshort);
		    $mform->setType('price3', PARAM_NUMBER);
		}
		
		$taxcodeopts = get_records_menu('courseshop_tax', '', '', 'title', 'id, title');
		$mform->addElement('select', 'taxcode', get_string('taxcode', 'block_courseshop'), $taxcodeopts);
		$mform->setDefault('taxcode', null);
		$mform->setType('taxcode', PARAM_INT);
		$mform->setHelpButton('taxcode', array('taxhelp', get_string('helptax', 'block_courseshop'), 'block_courseshop'));

        if (!$this->catalog->isslave){
		    $mform->addElement('text', 'stock', get_string('stock', 'block_courseshop'), $attributesshort);
		    $mform->setType('stock', PARAM_NUMBER);

		    $mform->addElement('text', 'sold', get_string('sold', 'block_courseshop'), $attributesshort);
		    $mform->setType('sold', PARAM_NUMBER);

            $maxquantopts = array('0' => get_string('unlimited'),
                                  '1' => '1',
                                  '2' => '2',
                                  '3' => '3',
                                  '4' => '4',
                                  '5' => '5',
                                  '10' => '10',
                                  '20' => '20',
                                  '50' => '50'
                                  );
		    $mform->addElement('select', 'maxdeliveryquant', get_string('maxdeliveryquant', 'block_courseshop'), $maxquantopts);
		    $mform->setType('maxdeliveryquant', PARAM_INT);
		} else {
		    $mform->addElement('hidden', 'stock');
		    $mform->addElement('hidden', 'sold');
		    $mform->addElement('hidden', 'maxdeliveryquant');
		}

        $radiogroup = array();
	    $radiogroup[] = &$mform->createElement('radio', 'onlyforloggedin', '', get_string('loggedin', 'block_courseshop'), 1);
	    $radiogroup[] = &$mform->createElement('radio', 'onlyforloggedin', '', get_string('both', 'block_courseshop'), 0);
	    $radiogroup[] = &$mform->createElement('radio', 'onlyforloggedin', '', get_string('loggedout', 'block_courseshop'), -1);
	    $mform->addGroup($radiogroup, 'loggedingroup', get_string('onlyfor', 'block_courseshop'), array(' '), false);
	    $mform->setDefault('onlyforloggedin', 0);

		//$group[] = &$mform->createElement('checkbox', 'showdescriptioninset', '', get_string('showdescriptioninset', 'block_courseshop'), 1);
		// $mform->addGroup($group, 'setvisibilityarray', '', array(' '), false);


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

		/*
		$mform->addElement('choosefile', 'image2', get_string('image', 'block_courseshop'), array('blockid' => $this->blockid, 'pinned' => $this->pinned), array('maxlength' => 255, 'size' => 48));
	    $mform->setType('image', PARAM_TEXT);
	    */

		$mform->addElement('text', 'thumb', get_string('thumbnail', 'block_courseshop'), $attributes);
	    $mform->setType('thumb', PARAM_TEXT);

		$mform->addElement('text', 'requireddata', get_string('requireddata', 'block_courseshop'), $attributes);
	    $mform->setType('requireddata', PARAM_TEXT);

		$statusopts = courseshop_get_status();
		$mform->addElement('select', 'status', get_string('status', 'block_courseshop'), $statusopts);
	    $mform->setType('status', PARAM_TEXT);

        $mform->addElement('htmleditor', 'notes', get_string('notes', 'block_courseshop'));
	    $mform->setType('notes', PARAM_CLEANHTML);
		$mform->setHelpButton('notes', array('description', get_string('helpnote', 'block_courseshop'), 'block_courseshop'));

        if (!$this->catalog->isslave){
            $setopts[0] = get_string('outofset', 'block_courseshop');
            if (!empty($sets)){
                foreach($sets as $set){
                    $setopts[$set->id] = $set->name;
                }
            }

            $mform->addElement('select', 'setid', get_string('set', 'block_courseshop'), $setopts);
        }

        $group = array();
		$group[] = &$mform->createElement('checkbox', 'shownameinset', '', get_string('shownameinset', 'block_courseshop'), 1);
		$group[] = &$mform->createElement('checkbox', 'showdescriptioninset', '', get_string('showdescriptioninset', 'block_courseshop'), 1);
		$mform->addGroup($group, 'setvisibilityarray', '', array(' '), false);
		
		$handleropts['0'] = get_string('disabled', 'block_courseshop');
		$handleropts['1'] = get_string('dedicated', 'block_courseshop');
		$handleropts = array_merge($handleropts, courseshop_get_standard_handlers_options());
		
		$mform->addElement('select', 'enablehandler', get_string('enablehandler', 'block_courseshop'), $handleropts);		

		$mform->addElement('text', 'handlerparams', get_string('handlerparams', 'block_courseshop'), $attributes);
	    $mform->setType('handlerparams', PARAM_TEXT);
		$mform->setHelpButton('handlerparams', array('handlerparams', get_string('helpnote', 'block_courseshop'), 'block_courseshop'));

		$mform->addRule('code', null, 'required');
		$mform->addRule('taxcode', null, 'required');
		$mform->addRule('shortname', null, 'required');
		$mform->addRule('name', null, 'required');
		$mform->addRule('categoryid', null, 'required');
		$mform->addRule('price1', '', 'required');

		// Adding submit and reset button
        $buttonarray = array();
    	$buttonarray[] = &$mform->createElement('submit', 'go_submit', get_string('submit'));
    	$buttonarray[] = &$mform->createElement('cancel', 'go_cancel', get_string('cancel'));
        
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');		
	}
}

?>