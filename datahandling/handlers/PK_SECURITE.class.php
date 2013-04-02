<?php 

class shop_handler_PK_SECURITE{
    
    var $courseidnumber = 'AMF';
    var $userquizidnumber = 'EXAM_AMF';

    var $opendays = 180;
    var $initialatempts = 2;
    
    function produce_prepay(&$data){
        global $CFG;

        // Create Moodle User but no assignation
        $newuser->username = generate_username($data->customer->firstname, $data->customer->lastname);
        $password = generate_password(8);
        $newuser->city = $data->customer->city;
        $newuser->country = $data->customer->country;
        $newuser->firstname = $data->customer->firstname;
        $newuser->lastname = $data->customer->lastname;
        $newuser->email = $data->customer->email;

        $newuser->auth = 'manual';
        $newuser->confirmed = 1;
        $newuser->lang = current_language();
        $newuser->lastip = getremoteaddr();
        $newuser->timemodified = time();
        $newuser->mnethostid = $CFG->mnet_localhost_id;

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

            $productiondata->public = get_string('productiondata_public', 'TRAIN_AMF', '', $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
            $a->username = $newuser->username;
            $a->password = $password;
            $productiondata->private = get_string('productiondata_private', 'TRAIN_AMF', $a, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
            $productiondata->salesadmin = get_string('productiondata_sales', 'TRAIN_AMF', $newuser->username, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
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
        
        // Assign Student role in course for the period
        if ($course = get_record('course', 'idnumber', $this->courseidnumber)){
            $context = get_context_instance(CONTEXT_COURSE, $course->id);
        } else {
			courseshop_trace("[{$data->transactionid}] Post Pay Process : PK_SECURITE Failure for course ID number {$this->courseidnumber}.");
            $productiondata->salesadmin .= "Bad target course for product identified by $courseidnumber \n";
            return;
        }
        
        $role = get_record('role', 'shortname', 'student');
        $now = time();
        role_assign($role->id, $data->user->id, 0, $context->id, $now, $now + DAYSECS * $this->opendays, true, 'manual', time());

        $productiondata->public = get_string('productiondata_assign_public', 'TRAIN_AMF', '', $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
        $productiondata->private = get_string('productiondata_assign_private', 'TRAIN_AMF', $course->id, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
        $productiondata->salesadmin = get_string('productiondata_assign_sales', 'TRAIN_AMF', $course->id, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');

        // Add individual assessments
        $cm = get_record('course_modules', 'idnumber', $this->userquizidnumber);
        if ($assessquiz = get_record('userquiz', 'id', $cm->instance)){
            
            $userid = (empty($data->foruser)) ? $USER->id : $data->foruser ;
    
            $userdata = get_record('userquiz_userdata', 'userquizid', $assessquiz->id, 'userid', $userid);
            $userdata->attempts = $this->initialattempts;
            
            if (update_record('userquiz_userdata', $userdata)){
                $maildata->extension = $this->initialattempts;
                $productiondata->public .= get_string('productiondata_public', 'SET_AMF_ATTEMPTS', $maildata, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
                $productiondata->private .= get_string('productiondata_private', 'SET_AMF_ATTEMPTS', $maildata, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
                $productiondata->salesadmin .= get_string('productiondata_sales', 'SET_AMF_ATTEMPTS', $maildata, $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
            }
        }

    	courseshop_trace("[{$data->transactionid}] Post Pay Process : PK_SECURITE Completed.");
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