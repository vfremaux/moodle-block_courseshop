<?php 

include_once $CFG->dirroot.'/blocks/courseshop/datahandling/shophandler.class.php';

class shop_handler_TRAIN_AMF_2 extends shop_handler{

	function __construct($label){
		parent::__construct($label);
	}        
        
    var $coursename = 'AMF';

    var $opendays = 14;
    
    function produce_prepay(&$data){
        global $CFG;

        // Create Moodle User but no assignation
        $newuser->username = courseshop_generate_username($data->firstname, $data->lastname);
        $password = generate_password(8);
        $newuser->city = $data->city;
        $newuser->country = $data->country;
        $newuser->firstname = $data->firstname;
        $newuser->lastname = $data->lastname;
        $newuser->email = $data->email;

        $newuser->auth = 'manual';
        $newuser->confirmed = 1;
        $newuser->lastip = getremoteaddr();
        $newuser->timemodified = time();
        $newuser->mnethostid = $CFG->mnet_localhost_id;

        if (!$data->user = get_record('user', 'username', $newuser->username)){
            if (insert_record('user', $newuser)) {
                $data->user = get_complete_user_data('username', $newuser->username);
                if(!empty($CFG->{'auth_'.$newuser->auth.'_forcechangepassword'})){
                    set_user_preference('auth_forcepasswordchange', 1, $data->user->id);
                }
                update_internal_user_password($data->user, $password);

                // bind customer record to Moodle userid
                $customer->id = $data->userid;
                $customer->hasaccount = $data->user->id;
                update_record('courseshop_customer', $customer);
            }            

            $productiondata->public = get_string('productiondata_public', 'TRAIN_AMF', '', $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
            $a->username = $newuser->username;
            $a->password = $password;
            $productiondata->private = get_string('productiondata_private', 'TRAIN_AMF', $a, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
            $productiondata->salesadmin = get_string('productiondata_sales', 'TRAIN_AMF', $newuser->username, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
        } else {
            $productiondata->public = get_string('knownaccount', 'block_courseshop', $newuser->username);
            $productiondata->private = get_string('knownaccount', 'block_courseshop', $newuser->username);
            $productiondata->salesadmin = get_string('knownaccount', 'block_courseshop', $newuser->username);
        }

        return $productiondata;
    }

    function produce_postpay(&$data){
        global $CFG;
        
        // Assign Student role in course for the period
        if ($course = get_record('course', 'shortname', $this->coursename)){
            $context = get_context_instance(CONTEXT_COURSE, $course->id);
        } else {
            error("Bad target course for product");
        }
        
        $role = get_record('role', 'shortname', 'student');
        $now = time();
        role_assign($role->id, $data->customer->hasaccount, 0, $context->id, $now, $now + DAYSECS * $this->opendays, true, 'manual', time());

        $productiondata->public = get_string('productiondata_assign_public', 'TRAIN_AMF', '', $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
        $productiondata->private = get_string('productiondata_assign_private', 'TRAIN_AMF', $course->id, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
        $productiondata->salesadmin = get_string('productiondata_assign_sales', 'TRAIN_AMF', $course->id, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');

        // record the bill is soldout
        set_field('courseshop_bill', 'status', 'SOLDOUT', 'id', $data->id);

        return $productiondata;
    }
    
}


?>