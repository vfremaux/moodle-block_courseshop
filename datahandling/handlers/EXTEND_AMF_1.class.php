<?php 

include_once $CFG->dirroot.'/blocks/courseshop/datahandling/shophandler.class.php';

class shop_handler_EXTEND_AMF_1 extends shop_handler{

    var $courseidnumbers;    
    var $rangeextension;
    
    function __construct($label = ''){
		parent::__construct($label);
        $this->courseidnumbers = array('AMF', 'HOTLINE', 'AMFRESOURCES', 'AMFACTU');
        $this->rangeextension = 30 * DAYSECS;
    }
    
    function produce_prepay(&$data){
        global $CFG, $USER;

        $productiondata->public = '';
        $productiondata->private = '';
        $productiondata->salesadmin = '';

        $data->user = get_record('user', 'id', $USER->id, '', '', '', '', 'id,firstname,lastname,email,mailformat,emailstop,username');

        return $productiondata;
    }

    function produce_postpay(&$data){
        global $CFG;

        $productiondata->public = '';
        $productiondata->private = '';
        $productiondata->salesadmin = '';
        
        // Assign Student role in course for the period

        $userid = (empty($data->foruser)) ? $data->customer->hasaccount : $data->foruser ;
        $foruser = get_record('user', 'id', $userid, '', '', '', '', 'id,firstname,lastname,email,mailformat,emailstop,username');
        $studentrole = get_record('role', 'shortname', 'student');
        $productcode = str_replace('shop_handler_', '', get_class($this));

        foreach($this->courseidnumbers as $courseidnumber){
	        if (!$course = get_record('course', 'idnumber', $courseidnumber)){
	            $productiondata->public .= get_string('processerror', 'block_courseshop')."\n";
	            $productiondata->private .= get_string('processerror', 'block_courseshop')."\n";
	            $productiondata->salesadmin .= "No context for course $courseidnumber. Nothing done.\n";
	            continue;
	        }
	        
	        $context = get_context_instance(CONTEXT_COURSE, $course->id);
	        	
	        if (!$assigndata = get_record('role_assignments', 'contextid', $context->id, 'userid', $userid, 'roleid',$studentrole->id)){
	            $productiondata->public .= get_string('processerror', 'block_courseshop')."\n";
	            $productiondata->private .= get_string('processerror', 'block_courseshop')."\n";
	            $productiondata->salesadmin .= "No assignation for this user $userid in context $context->id. Nothing done.\n";
	            continue;
	        }
	
	        $assigndata->timeend += $this->rangeextension;
	                
	        if(update_record('role_assignments', $assigndata)){
	            add_to_log(0, 'block_courseshop', 'production', "blocks/courseshop/bills/view.php?id={$data->blockid}&view=viewBill&billid={$data->id}", $productcode, $data->blockid);
	
	            $maildata->courseid = $course->id;
	            $maildata->extension = $this->rangeextension / DAYSECS;
	            $maildata->username = fullname($foruser);
	            
	            $productiondata->public .= get_string('productiondata_public', 'ADD_TIME', $maildata, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/')."\n";
	            $productiondata->private .= get_string('productiondata_private', 'ADD_TIME', $maildata, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/')."\n";
	            $productiondata->salesadmin .= get_string('productiondata_sales', 'ADD_TIME', $maildata, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/')."\n";
	
	            // record the bill is soldout
	            set_field('courseshop_bill', 'status', 'SOLDOUT', 'id', $data->id);
	        } else {
	            $productiondata->public .= get_string('processerror', 'block_courseshop')."\n";
	            $productiondata->private .= get_string('processerror', 'block_courseshop')."\n";
	            $productiondata->salesadmin .= "Update Error \n";
	        }
	    }

    	courseshop_trace("[{$data->transactionid}] Post Pay Process : EXTEND_AMF_1 Completed.");
        return $productiondata;
    }    
}

/**
* generates a suitable username from first and last name
*/
/*
function generate_username($firstname, $lastname){

    $firstname = strtolower($firstname);
    $firstname = str_replace('\'', '', $firstname);
    $firstname = preg_replace('/\s+/', '-', $firstname);
    $lastname = strtolower($lastname);
    $lastname = str_replace('\'', '', $lastname);
    $lastname = preg_replace('/\s+/', '-', $lastname);
    
    return $firstname.'.'.$lastname;
}
*/
?>