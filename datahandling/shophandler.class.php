<?php

class shop_handler{

	var $productlabel;
	
	function __construct($label){
		$this->productlabel = $label;
	}
	
	function process_required(&$billinfo){
		$required = preg_grep("/required_{$this->productlabel}_.*/", array_keys($_REQUEST));
		$billinfo->required = array();
		foreach($required as $field){
			preg_match("/required_{$this->productlabel}_(.*)/", $field, $matches);
			$billinfo->required[$matches[1]] = $_REQUEST[$field];
		}
	}
}