<?php

abstract class shop_handler{

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

	/**
	* What is happening on order time, before it has been actually paied out
	*
	*/	
	abstract function produce_prepay(&$data);

	/**
	* What is happening after it has been actually paied out, interactively
	* or as result of a delayed sales administration action.
	*/	
	abstract function produce_postpay(&$data);

	/**
	* when implemented, the cron task for this handler will be run on courseshop cron
	* cron can be used to notify users for end of product life, user role unassigns etc.
	*/
	// function cron(){}
}