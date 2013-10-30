<?php

class courseshop_export{

	var $filename;
	
	var $data;

	var $datadesc;

	/**
	* array of export options
	* 'addtimestamp' => 0/1
	*/
	var $options;

	function __construct($data, $datadesc, $options){

		$this->filename = $datadesc[0]['filename'];
		$this->data = $data;
		$this->datadesc = $datadesc;
		$this->options = $options;

		if (!empty($options['addtimestamp'])){
			$parts = pathinfo($this->filename);
			$this->filename = $parts['filename'].'-'.date('Ymdhi', time()).'.'.$parts['extension']; 
		}
		
		if (empty($this->datadesc[0]['purpose'])){
			$this->datadesc[0]['purpose'] = 'default';
		}
	}

	function open_export(){
	}

	function render(){
	}
	
	function close_export(){
	}
}