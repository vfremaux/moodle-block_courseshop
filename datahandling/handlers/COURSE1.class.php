<?php 

include_once $CFG->dirroot.'/blocks/courseshop/datahandling/shophandler.class.php';

class shop_handler_COURSE1 extends shop_handler{

	function __construct($label){
		parent::__construct($label);
	}        
        
    var $coursename = 'COURSE1';

    function produce_prepay(&$data){
        global $CFG;

		// If Customer already has account in incoming data we have nothing to do
		if (!is_null($data->user)){
            $productiondata->public = get_string('knownaccount', 'block_courseshop', $data->user->username);
            $productiondata->private = get_string('knownaccount', 'block_courseshop', $data->user->username);
            $productiondata->salesadmin = get_string('knownaccount', 'block_courseshop', $data->user->username);
            return $productiondata;
		}
		
		// In this case we can have a early Customer that never confirmed a product or a brand new Customer comming in.
		// The Customer might match with an existing user... 
		// TODO : If a collision is to be detected, a question should be asked to the customer.

        // Create Moodle User but no assignation
        $newuser->username = courseshop_generate_username($data->customer->firstname, $data->customer->lastname);
        $password = generate_password(8);
        $newuser->city = $data->customer->city;
        $newuser->country = $data->customer->country;
        $newuser->firstname = $data->customer->firstname;
        $newuser->lastname = $data->customer->lastname;
        $newuser->email = $data->customer->email;

        $newuser->auth = 'manual';
        $newuser->confirmed = 1;
        $newuser->lastip = getremoteaddr();
        $newuser->timemodified = time();
        $newuser->mnethostid = $CFG->mnet_localhost_id;

		// Check we really got it in user table. Create it in other cases.
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

            $productiondata->public = get_string('productiondata_public', 'COURSESHOP_DEMO', '', $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
            $a->username = $newuser->username;
            $a->password = $password;
            $productiondata->private = get_string('productiondata_private', 'COURSESHOP_DEMO', $a, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
            $productiondata->salesadmin = get_string('productiondata_sales', 'COURSESHOP_DEMO', $newuser->username, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
        } else {
            $productiondata->public = get_string('knownaccount', 'block_courseshop', $newuser->username);
            $productiondata->private = get_string('knownaccount', 'block_courseshop', $newuser->username);
            $productiondata->salesadmin = get_string('kownaccount', 'block_courseshop', $newuser->username);
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
        if (!role_assign($role->id, $data->user->id, 0, $context->id, $now, 0, false, 'manual', time())){
        	debug_trace('Post Pay Process : COURSE1 failed...');
        }

        $productiondata->public = get_string('productiondata_assign_public', 'COURSE1', '', $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
        $productiondata->private = get_string('productiondata_assign_private', 'COURSE1', $course->id, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
        $productiondata->salesadmin = get_string('productiondata_assign_sales', 'COURSE1', $course->id, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');

        // record the bill is soldout
        set_field('courseshop_bill', 'status', 'SOLDOUT', 'id', $data->id);

        return $productiondata;
    }
    
}

?>