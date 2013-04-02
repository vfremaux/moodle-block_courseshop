<?php 

/**
* STD_ENROL_ONE_COURSE is a standard shop product action handler that enrols in one course setup in
* actiondata.
* actiondata is defined as an action customized information for a specific product in the 
* product definition, where one standard handler is choosen. 
*
*/
include_once $CFG->dirroot.'/blocks/courseshop/datahandling/shophandler.class.php';

class shop_handler_STD_ENROL_ONE_COURSE extends shop_handler{

	function __construct($label){
		parent::__construct($label);
	}
        
    function produce_prepay(&$data){
        global $CFG;

		// If Customer already has account in incoming data we have nothing to do
		if (!is_null($data->user)){
            $productiondata->public = get_string('knownaccount', 'block_courseshop', $data->user->username);
            $productiondata->private = get_string('knownaccount', 'block_courseshop', $data->user->username);
            $productiondata->salesadmin = get_string('knownaccount', 'block_courseshop', $data->user->username);
			courseshop_trace("[{$data->transactionid}] Pre Pay Process : Known account {$data->user->username} at process entry.");
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
            if ($newuserid = insert_record('user', $newuser)) {
                $data->user = get_complete_user_data('username', $newuser->username);
                if(!empty($CFG->{'auth_'.$newuser->auth.'_forcechangepassword'})){
                    set_user_preference('auth_forcepasswordchange', 1, $data->user->id);
                }
                update_internal_user_password($data->user, $password);

                // bind customer record to Moodle userid
                $customer->id = $data->userid;
                $customer->hasaccount = $data->user->id;
                update_record('courseshop_customer', $customer);

				courseshop_trace("[{$data->transactionid}] Pre Pay Process : New user created {$newuser->username} with ID $newuserid.");
            }            

            $productiondata->public = get_string('productiondata_public', 'STD_ENROL_ONE_COURSE', '', $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
            $a->username = $newuser->username;
            $a->password = $password;
            $productiondata->private = get_string('productiondata_private', 'STD_ENROL_ONE_COURSE', $a, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
            $productiondata->salesadmin = get_string('productiondata_sales', 'STD_ENROL_ONE_COURSE', $newuser->username, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
        } else {
			courseshop_trace("[{$data->transactionid}] Pre Pay Process : Known account {$data->user->username}.");
            $productiondata->public = get_string('knownaccount', 'block_courseshop', $newuser->username);
            $productiondata->private = get_string('knownaccount', 'block_courseshop', $newuser->username);
            $productiondata->salesadmin = get_string('kownaccount', 'block_courseshop', $newuser->username);
        }

        return $productiondata;
    }

    function produce_postpay(&$data){
        global $CFG;
        
        if (!isset($data->actionparams['coursename'])){
            error("Missing handler action data for STD_ENROL_ONE_COURSE standard handler");
        }

        $coursename = $data->actionparams['coursename'];
        $rolename = @$data->actionparams['role'];
        if (empty($rolename)){
        	$rolename = 'student';
        }
        
        // Assign Student role in course for the period
        if ($course = get_record('course', 'shortname', $coursename)){
            $context = get_context_instance(CONTEXT_COURSE, $course->id);
        } else {
            error("Bad target course for product");
        }
        
        $role = get_record('role', 'shortname', $rolename);
        $now = time();
        if (!role_assign($role->id, $data->user->id, 0, $context->id, $now, 0, false, 'manual', time())){
        	courseshop_trace("[{$data->transactionid}] Post Pay Process : STD_ENROL_ONE_COURSE failed...");
	        $productiondata->public = get_string('productiondata_failure_public', 'STD_ENROL_ONE_COURSE', 'Code : ROLE ASSIGN', $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
	        $productiondata->private = get_string('productiondata_failure_private', 'STD_ENROL_ONE_COURSE', $course->id, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
	        $productiondata->salesadmin = get_string('productiondata_failure_sales', 'STD_ENROL_ONE_COURSE', $course->id, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
        	return $productiondata;
        }

        $productiondata->public = get_string('productiondata_assign_public', 'STD_ENROL_ONE_COURSE', '', $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
        $productiondata->private = get_string('productiondata_assign_private', 'STD_ENROL_ONE_COURSE', $course->id, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
        $productiondata->salesadmin = get_string('productiondata_assign_sales', 'STD_ENROL_ONE_COURSE', $course->id, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');

		courseshop_trace("[{$data->transactionid}] Post Pay Process : STD_ENROL_ONE_COURSE completed in $coursename...");
        return $productiondata;
    } 
}

?>