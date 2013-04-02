<?php

if ($cmd == 'import'){
	unset($SESSION->shoppingcart);
	foreach(array_keys($_GET) as $inputkey){
		if ($inputkey == 'shipping'){
			$SESSION->shoppingcart[$inputkey] = optional_param($inputkey, 0, PARAM_BOOL);
			continue;
		}
		$SESSION->shoppingcart[$inputkey] = optional_param($inputkey, '', PARAM_TEXT);
	}	
}

?>