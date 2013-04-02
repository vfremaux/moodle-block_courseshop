<?php 

class shop_handler_EXTEND_1{

    var $courseidnumber;    
    var $rangeextension;
    
    function __construct($label){
        $this->coursename = 'AMF';
        $this->rangeextension = 7 * DAYSECS;
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
        global $CFG, $USER;
        
        // Assign Student role in course for the period
        if (!$course = get_record('course', 'idnumber', $this->courseidnumber)){
            error("Bad target course for product (IDNumber)");
        }
        
        $context = get_context_instance(CONTEXT_COURSE, $course->id);
        
        $userid = (empty($data->foruser)) ? $USER->id : $data->foruser ;
        $studentrole = get_record('role', 'shortname', 'student');

        if (!$assigndata = get_record('role_assignments', 'contextid', $context->id, 'userid', $userid, 'roleid',$studentrole->id)){
            $productiondata->public = get_string('processerror', 'block_courseshop');
            $productiondata->private = get_string('processerror', 'block_courseshop');
            $productiondata->salesadmin = "No assignation for this user $userid in context $context->id. Nothing done.";
            return $productiondata;
        }

        $assigndata->timeend += $this->rangeextension;
                
        if(update_record('role_assignments', $assigndata)){
            $productcode = str_replace('shop_handler_', '', get_class($this));
            add_to_log(0, 'block_courseshop', 'production', "blocks/courseshop/bills/view.php?id={$data->blockid}&view=viewBill&billid={$data->id}", $productcode, $data->blockid);

            $maildata->courseid = $course->id;
            $maildata->extension = $this->rangeextension / ( 7 * DAYSECS);
            $maildata->username = fullname($data);
            
            $productiondata->public = get_string('productiondata_public', 'ADD_TIME', $maildata, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
            $productiondata->private = get_string('productiondata_private', 'ADD_TIME', $maildata, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
            $productiondata->salesadmin = get_string('productiondata_sales', 'ADD_TIME', $maildata, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');

            // record the bill is soldout
            set_field('courseshop_bill', 'status', 'SOLDOUT', 'id', $data->id);
        } else {
            $productiondata->public = get_string('processerror', 'block_courseshop');
            $productiondata->private = get_string('processerror', 'block_courseshop');
            $productiondata->salesadmin = "Update Error ";
        }        

        return $productiondata;
    }    
}

/**
* generates a suitable username from first and last name
*/
function generate_username($firstname, $lastname){

    $firstname = strtolower($firstname);
    $firstname = str_replace('\'', '', $firstname);
    $firstname = preg_replace('/\s+/', '-', $firstname);
    $lastname = strtolower($lastname);
    $lastname = str_replace('\'', '', $lastname);
    $lastname = preg_replace('/\s+/', '-', $lastname);
    
    return $firstname.'.'.$lastname;
}

?>