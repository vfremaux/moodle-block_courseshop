<?php 

/**
* STD_CREATE_COURSE is a standard shop product action handler that creates a course space for the customer
* and enrols the customer as editing teacher inside.
*
*/
include_once $CFG->dirroot.'/blocks/courseshop/datahandling/shophandler.class.php';

class shop_handler_STD_CREATE_COURSE extends shop_handler{

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

            $productiondata->public = get_string('productiondata_public', 'STD_CREATE_COURSE', '', $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
            $a->username = $newuser->username;
            $a->password = $password;
            $productiondata->private = get_string('productiondata_private', 'STD_CREATE_COURSE', $a, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
            $productiondata->salesadmin = get_string('productiondata_sales', 'STD_CREATE_COURSE', $newuser->username, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
        } else {
            $productiondata->public = get_string('knownaccount', 'block_courseshop', $newuser->username);
            $productiondata->private = get_string('knownaccount', 'block_courseshop', $newuser->username);
            $productiondata->salesadmin = get_string('kownaccount', 'block_courseshop', $newuser->username);
 			courseshop_trace("[{$data->transactionid}] Pre Pay Process : Known account {$data->user->username}.");
       }

        return $productiondata;
    }

    function produce_postpay(&$data){
        global $CFG;
        
        if (!isset($data->actionparams['coursecategory'])){
        	courseshop_trace("[{$data->transactionid}] Post Pay Process :  Missing handler action data (coursecategory) for STD_CREATE_COURSE standard handler");
        	return;
        }

        if (!isset($data->actionparams['template'])){
        	courseshop_trace("[{$data->transactionid}] Post Pay Process :  Missing handler action data (template) for STD_CREATE_COURSE standard handler");
        	return;
        }

        if (!isset($data->actionparams['duration'])){
        	courseshop_trace("[{$data->transactionid}] Post Pay Process : Missing handler action data (template) for STD_CREATE_COURSE standard handler");
        	return;
        }

        $coursetemplatename = $data->actionparams['template'];

        $now = time();
        $secsduration = $data->actionparams['duration'] * DAYSECS;
        $upto = ($secsduration) ? $now + $secsduration : 0 ;

        $c->category = $data->actionparams['coursecategory'];
        $c->shortname = courseshop_generate_shortname($data->user);
        $c->fullname = $data->required['fullname'];
        $c->enrollable = 0;
        $c->timecreated = time();
        $c->startdate = time();
        $c->lang = '';
        $c->theme = '';
        $c->cost = '';

		$template = get_record('course', 'shortname', $coursetemplatename);
		if ($templatepath = courseshop_delivery_check_available_backup($template->id)){
			if ($c->id = courseshop_create_course_from_template($templatepath->path, $c)){
				$context = get_context_instance(CONTEXT_COURSE, $c->id);
			} else {
		        $productiondata->public = get_string('productiondata_failure_public', 'STD_CREATE_COURSE', 'Code : COURSE_CREATION', $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
		        $productiondata->private = get_string('productiondata_failure_private', 'STD_CREATE_COURSE', $data, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
		        $productiondata->salesadmin = get_string('productiondata_failure_sales', 'STD_CREATE_COURSE', $data, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
        		courseshop_trace("[{$data->transactionid}] Post Pay Process : Course creation failure (DB reason)...");
	        	return $productiondata;
			}
		} else {
        	courseshop_trace("[{$data->transactionid}] Post Pay Process : Template $coursetemplatename has no backup...");
	        $productiondata->public = get_string('productiondata_failure_public', 'STD_CREATE_COURSE', 'Code : TEMPLATE_BACKUP', $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
	        $productiondata->private = get_string('productiondata_failure_private', 'STD_CREATE_COURSE', $data, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
	        $productiondata->salesadmin = get_string('productiondata_failure_sales', 'STD_CREATE_COURSE', $data, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
        	return $productiondata;
		}
        
        if (!$role = get_record('role', 'shortname', 'courseowner')){
	        $role = get_record('role', 'shortname', 'editingteacher');
	    }
        $now = time();
        if (!role_assign($role->id, $data->user->id, 0, $context->id, $now, $upto, false, 'manual', time())){
        	courseshop_trace("[{$data->transactionid}] Post Pay Process : STD_ONE_COURSE failed to assign teacher...");
	        $productiondata->public = get_string('productiondata_failure_public', 'STD_CREATE_COURSE', 'Code : TEACHER_ROLE_ASSIGN', $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
	        $productiondata->private = get_string('productiondata_failure2_private', 'STD_CREATE_COURSE', $c, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
	        $productiondata->salesadmin = get_string('productiondata_failure2_sales', 'STD_CREATE_COURSE', $c, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
	        return $productiondata;
        }

		$c->username = $data->user->username;
		$c->fullname = stripslashes($c->fullname);
        $productiondata->public = get_string('productiondata_post_public', 'STD_CREATE_COURSE', '', $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
        $productiondata->private = get_string('productiondata_post_private', 'STD_CREATE_COURSE', $c, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
        $productiondata->salesadmin = get_string('productiondata_post_sales', 'STD_CREATE_COURSE', $c, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');

    	courseshop_trace("[{$data->transactionid}] Post Pay Process : STD_ONE_COURSE completed in course {$c->shortname}.");

        return $productiondata;
    } 
}

?>