<?php

include_once $CFG->libdir.'/formslib.php';

if (!defined('MOODLE_INTERNAL')) { die("You cannot use this script this way !"); }

class ResetForm extends moodleform {

	var $pinned;
	var $blockid;

	function __construct($blockid, $pinned){
		$this->pinned = $pinned;
		$this->blockid = $blockid;
		parent::__construct();
	}

    // Define the form
    function definition () {
		
        $mform =& $this->_form;
        //Accessibility: "Required" is bad legend text.

        /// Add some extra hidden fields
        $mform->addElement('hidden', 'id', $this->blockid);
        $mform->addElement('hidden', 'pinned', $this->pinned);

        $mform->addElement('checkbox', 'bills', get_string('resetbills', 'block_courseshop'));

        $mform->addElement('checkbox', 'customers', get_string('resetcustomers', 'block_courseshop'));

        $mform->addElement('checkbox', 'catalogs', get_string('resetcatalogs', 'block_courseshop'));

		$mform->disabledIf('bills', 'customers', 'checked');
		$mform->disabledIf('bills', 'catalogs', 'checked');

        $this->add_action_buttons(true, get_string('reset', 'block_courseshop'));
    }

}

?>