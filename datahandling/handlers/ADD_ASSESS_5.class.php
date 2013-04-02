<?php 

include_once $CFG->dirroot.'/blocks/courseshop/datahandling/shophandler.class.php';

class shop_handler_ADD_ASSESS_5 extends shop_handler{
    
    var $courseidnumber = 'AMF';
    var $assessextension = 5;
    var $userquizidnumber = 'EXAM_AMF';

    function __construct($label = ''){
		parent::__construct($label);
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
        
        // Add individual assessments
        if (!$course = get_record('course', 'idnumber', $this->courseidnumber)){
            error("Bad target course for product");
        }
        
        if ($cm = get_record('course_modules', 'idnumber', $this->userquizidnumber)){
            if ($assessquiz = get_record('userquiz', 'id', $cm->instance)){
                
                $userid = (empty($data->foruser)) ? $data->customer->hasaccount : $data->foruser ;
        
                $userdata = get_record('userquiz_userdata', 'userquizid', $assessquiz->id, 'userid', $userid);
                $userdata->attempts += $this->assessextension;
                
                if(update_record('userquiz_userdata', $userdata)){
                    $productcode = str_replace('shop_handler_', '', get_class($this));
                    add_to_log(0, 'block_courseshop', 'production', "blocks/courseshop/bills/view.php?id={$data->blockid}&view=viewBill&billid={$data->id}", $productcode, $data->blockid);
                }
                
                $maildata->courseid = $course->id;
                $maildata->extension = $this->assessextension;
                $maildata->username = fullname($data);
                
                $productiondata->public = get_string('productiondata_public', 'ADD_AMF_ATTEMPTS', $maildata, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
                $productiondata->private = get_string('productiondata_private', 'ADD_AMF_ATTEMPTS', $maildata, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
                $productiondata->salesadmin = get_string('productiondata_sales', 'ADD_AMF_ATTEMPTS', $maildata, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
    
                // record the bill is soldout
                set_field('courseshop_bill', 'status', 'SOLDOUT', 'id', $data->id);
            } else {
                $productiondata->public = "Bad userquiz definition";
                $productiondata->private = '';
                $productiondata->salesadmin = '';
            }
        } else {
            error("Missing module reference for this product handler");
        }

        return $productiondata;
    }    
}

?>