<?php

require_once $CFG->dirroot.'/backup/backuplib.php';
require_once $CFG->dirroot.'/backup/restorelib.php';
include_once $CFG->dirroot.'/backup/lib.php';

define('PRODUCT_STANDALONE', 0);
define('PRODUCT_SET', 1);
define('PRODUCT_BUNDLE', 2);

function courseshop_get_categories(&$catalog){
    // get all master categories
    $mastercategories = array();
    if ($catalog->isslave){
        if (!$mastercategories = get_records_select('courseshop_catalogcategory', " catalogid = {$catalog->groupid} AND visible = 1 ", 'sortorder', '*,1 as masterrecord')){
            $mastercategories = array();
        }
    }
    
    // get all categories
    if (!$localcategories = get_records_select('courseshop_catalogcategory', " catalogid = {$catalog->id} AND visible = 1 ", 'sortorder', '*,0 as masterrecord')){
        $localcategories = array();
    }
    return array_merge($mastercategories, $localcategories);
}

/**
* gives all product status 
*
*/
function courseshop_get_status(){

    $status = array(
                'PREVIEW' => get_string('PREVIEW', 'block_courseshop'),
                'AVAILABLE' => get_string('AVAILABLE', 'block_courseshop'),
                'AVAILABLEINTERNAL' => get_string('AVAILABLEINTERNAL', 'block_courseshop'),
                'SUSPENDED' => get_string('SUSPENDED', 'block_courseshop'),
                'PROVIDING' => get_string('PROVIDING', 'block_courseshop'),
                'ABANDONNED' => get_string('ABANDONNED', 'block_courseshop')
    );

    return $status;
}

function print_order_call($fielname, $context = ''){
    global $CFG;
    
    echo " <a href=\"$context&order=$fielname\">v</a>";
}

/**
* get a bloc instance for the courseshop
*
*/
function courseshop_get_block_instance($instanceid, $pinned){
    $blocktable = ($pinned) ? 'block_pinned' : 'block_instance' ;
    if (!$instance = get_record($blocktable, 'id', $instanceid)){
        error('Invalid block');
    }
    if (!$theBlock = block_instance('courseshop', $instance)){
        error ("Bad courseshop block");
    }

    return $theBlock;
}

/**
* examines the handler list in implementation and get the 
* emerging standard handlers. Standard (generic) handlers are
* PHP clases that all start with STD_
* @return an array of options for a select.
*/
function courseshop_get_standard_handlers_options(){
	global $CFG;

	$stdhandlers = array();	
	$handlers = glob($CFG->dirroot.'/blocks/courseshop/datahandling/handlers/STD_*');
	foreach($handlers as $h){
		preg_match('/(.*)\.class.php/', basename($h), $matches);
		$canonicname = $matches[1];
		$handlername = get_string('handlername', $canonicname, '', $CFG->dirroot.'/blocks/courseshop/datahandling/handlers/lang/');
		$stdhandlers[$canonicname] = get_string('generic', 'block_courseshop').' '.$handlername;		
	}
	
	return $stdhandlers;
}

/**
* given an url encoded param input from a catalog item handler paramstring, make a clean object with it
* The object will be tranmitted to core handler callbacks to help product to be handled.
* @param string $catalogitemcode product code name in catalog.
* @return an object with params as object fields.
*/
function courseshop_decode_params($catalogitemcode){

	$paramstring = get_field('courseshop_catalogitem', 'handlerparams', 'code', "$catalogitemcode");
	if (empty($paramstring)){
		return null;
	} 

	$params = array();
	$paramelements = explode('&', $paramstring);
	foreach($paramelements as $elm){
		list($key, $value) = explode('=', $elm);
		if (empty($key)) continue; // ignore bad formed
		$params[$key] = $value;
	}
	
	return $params;
}

/**
* checks for an existing backup
* @see exists in other components (enrol/sync, publishflow) but no consensus yet to centralize
* @param int $courseid the course for which seeking for backup consistant file
* @return full path of the backyp file on disk.
*/
function courseshop_delivery_check_available_backup($courseid){
	global $CFG;

	$realpath = false;

	// calculate the archive pattern

	$course = get_record('course', 'id', $courseid);

	//Calculate the backup word
	$backup_word = backup_get_backup_string($course);

	//Calculate the date recognition/capture patterns
	$backup_date_pattern = '([0-9]{8}-[0-9]{4})';

	//Calculate the shortname
	$backup_shortname = clean_filename($course->shortname);
	if (empty($backup_shortname) or $backup_shortname == '_' ) {
		$backup_shortname = $course->id;
	} else {
		// get rid of all version information for searching archive
		$backup_shortname = preg_replace('/(_(\d+))+$/' , '', $backup_shortname);
	}
	
	//Calculate the final backup filename
	//The backup word
	$backup_pattern = $backup_word."-";
	//The shortname
	$backup_pattern .= preg_quote(moodle_strtolower($backup_shortname)).".*-";
	//The date format
	$backup_pattern .= $backup_date_pattern;
	//The extension
	$backup_pattern .= "\\.zip";
	
	// Get the last backup in the proper location
	// backup must have moodle backup filename format
	$realdir = $CFG->dataroot.'/'.$courseid.'/backupdata';

	if (!file_exists($realdir)) return false;

	if ($DIR = opendir($realdir)){
		$archives = array();
		while($entry = readdir($DIR)){
			if (preg_match("/^$backup_pattern\$/", $entry, $matches)){
				$archives[$matches[1]] = "{$realdir}/{$entry}";
			}
		}

		if (!empty($archives)){
			// sorts reverse the archives so we can get the latest.
			krsort($archives);
			$archnames = array_values($archives);
			$realpath->path = $archnames[0];
			$realpath->dir = $realdir;
		}
	}

	return $realpath;
}	

/**
* generates a usrname from given identity
*
*/
function courseshop_generate_username($firstname, $lastname){

    $firstname = strtolower($firstname);
    $firstname = str_replace('\'', '', $firstname);
    $firstname = preg_replace('/\s+/', '-', $firstname);
    $lastname = strtolower($lastname);
    $lastname = str_replace('\'', '', $lastname);
    $lastname = preg_replace('/\s+/', '-', $lastname);
    
    $username = $firstname.'.'.$lastname;
    
    $username = str_replace('é', 'e', $username);
    $username = str_replace('è', 'e', $username);
    $username = str_replace('ê', 'e', $username);
    $username = str_replace('ë', 'e', $username);
    $username = str_replace('ö', 'o', $username);
    $username = str_replace('ô', 'o', $username);
    $username = str_replace('ü', 'u', $username);
    $username = str_replace('û', 'u', $username);
    $username = str_replace('ù', 'u', $username);
    $username = str_replace('î', 'i', $username);
    $username = str_replace('ï', 'i', $username);
    $username = str_replace('à', 'a', $username);
    $username = str_replace('â', 'a', $username);
    $username = str_replace('ç', 'c', $username);
    $username = str_replace('ñ', 'n', $username);
    
    return $username;
}

/**
* generates a suitable shortname based on user's username
*/
function courseshop_generate_shortname($user){
	global $CFG;
	
	$username = str_replace('.', '', $user->username);
	$basename = strtoupper(substr($username, 0, 8));
	
	$sql = "
		SELECT
			shortname,
			shortname
		FROM
			{$CFG->prefix}course
		WHERE
			shortname REGEXP '^{$basename}_[[:digit:]]+$'
		ORDER BY
			shortname
	";
	if (!$used = get_records_sql($sql)){
		return $basename.'_1';
	} else {
		$last = array_pop($used);
		preg_match('/^$basename(\\d+)$/', $last, $matches);
		$lastid = $matches[1] + 1;
		return $basename.'_'.$lastid;
	}
}

/**
* create a course from a template
*/
function courseshop_create_course_from_template($templatepath, $courserec){
	
	if (empty($courserec->password)) $courserec->password = '';
	if (empty($courserec->fullname)) $courserec->fullname = '';
	if (empty($courserec->shortname)) error('Programming Error. Never let do'); // should NEVER happen... shortname needs to be resolved before creating
	if (empty($courserec->idnumber)) $courserec->idnumber = '';
	if (empty($courserec->lang)) $courserec->lang = '';
	if (empty($courserec->lang)) $courserec->lang = '';
	if (empty($courserec->theme)) $courserec->theme = '';
	if (empty($courserec->cost)) $courserec->cost = '';

	// first creation of record before restoring.	
	if (!$courserec->id = insert_record('course', addslashes_object($courserec))){
		return;
	}

	create_context(CONTEXT_COURSE, $courserec->id);

	import_backup_file_silently($templatepath, $courserec->id, true, false, array('restore_course_files' => 1));

	// this part forces some course attributes to override the given attributes in template
	// temptate attributes might come from the backup instant and are not any more consistant.
	// As importing a course needs a real course to exist before importing, it is not possible
	// to preset those attributes and expect backup will not overwrite them.
	// conversely, precreating the coure with some attributes setup might give useful default valies that
	// are not present in the backup.

	// override necessary attributes from original courserec.
	update_record('course', $courserec);
	
	return $courserec->id;
}

// Create category with the given name and parentID returning a category ID
function courseshop_fast_make_category($catname, $description = '', $catparent = 0){
    global $CFG;
    global $USER;
    
    $cname = mysql_real_escape_string($catname);
    
    // Check if a category with the same name and parent ID already exists
    if ($cat = get_field_select('course_categories', 'id', " name = '$cname' AND parent = $catparent ")){
    	return false;
    } else {
    	if (!$parent = get_record('course_categories', 'id', $catparent)){
    		$parent->path = '';
    		$parent->depth = 0;
    		$catparent = 0;
    	}
    	
		$cat = new StdClass;
		$cat->name = $cname;
		$cat->description = mysql_real_escape_string($description);
		$cat->parent = $catparent;
		$cat->sortorder = 999;
		$cat->coursecount = 0;
		$cat->visible = 1;
		$cat->depth = $parent->depth + 1;
		$cat->timemodified = time();
		if ($cat->id = insert_record('course_categories', $cat)){			
			// must post update 
			$cat->path = $parent->path.'/'.$cat->id;
			update_record('course_categories', $cat);
			
			// we must make category context
			create_context(CONTEXT_COURSECAT, $cat->id);			
			return $cat->id;
		} else {
			return false;
		}
    }
}

/**
* background style switch
*/
function courseshop_switch_style($reset = 0){
	static $style;
	
	if ($reset) $style = 'odd';
	
	if ($style == 'odd'){
		$style = 'even';
	} else {
		$style = 'odd';
	}
	return $style;
}

/**
* opens a trace file
* IMPORTANT : check very carefully the path and name of the file or it might destroy
* some piece of code. Do NEVER use in production systems unless hot case urgent tracking
*/
function courseshop_open_trace(){
    global $CFG, $MERCHANT_TRACE;

    if (is_null($MERCHANT_TRACE)){
        $MERCHANT_TRACE = fopen($CFG->dataroot.'/merchant_trace.log', 'a');
    }
    return !is_null($MERCHANT_TRACE);
}

/**
* closes an open trace
*/
function courseshop_close_trace(){
    global $MERCHANT_TRACE;

    if (!is_null($MERCHANT_TRACE)){
        @fclose($MERCHANT_TRACE);
        $MERCHANT_TRACE = null;
    }
}

/**
* outputs into an open trace (ligther than debug_trace)
*/
function courseshop_trace_open($str){
    global $MERCHANT_TRACE;

    if (!is_null($MERCHANT_TRACE)){
        fputs($MERCHANT_TRACE, "-- ".date('Y-n-d h:i:s u', time())." --  ".$str."\n");
    }
}

/**
* write to the trace
*/
function courseshop_trace($str){
    global $MERCHANT_TRACE;

    if (!is_null($MERCHANT_TRACE)){
        courseshop_trace_open($str);
    } else {
        if (courseshop_open_trace()){
            courseshop_trace_open($str);
            courseshop_close_trace();
        }
    }
}

function courseshop_calculate_taxed($htprice, $taxid){
	static $TAXCACHE;
	
	if (!isset($TAXCACHE)){
		$TAXCACHE = array();
	}
	
	if (!array_key_exists($taxid, $TAXCACHE)){
		if ($TAXCACHE[$taxid] = get_record('courseshop_tax', 'id', $taxid)){
			if (empty($TAXCACHE[$taxid]->formula)) $TAXCACHE[$taxid]->formula = '$TTC = $HT';
		} else {
			return $htprice;
		}
	}
	
    $HT = $htprice;
    $TR = $TAXCACHE[$taxid]->ratio;
    eval($TAXCACHE[$taxid]->formula.';');
	
	return $TTC;
}

/**
* International currencies
* from http://fr.wikipedia.org/wiki/ISO_4217
*/
function courseshop_get_supported_currencies(){
	static $CURRENCIES;
	
	if (!isset($CURRENCIES)){
	
		$CURRENCIES = array('EUR' => get_string('EUR', 'block_courseshop'), // Euro
							'CHF' => get_string('CHF', 'block_courseshop'), // Swiss franc
							'USD' => get_string('USD', 'block_courseshop'), // US dollar
							'CAD' => get_string('CAD', 'block_courseshop'), // Canadian dollar
							'AUD' => get_string('AUD', 'block_courseshop'), // Australian dollar
							'GPB' => get_string('GPB', 'block_courseshop'), // English pound
							'TRY' => get_string('TRY', 'block_courseshop'), // Turkish pound
							'PLN' => get_string('PLN', 'block_courseshop'), // Zloty (Poland)
							'RON' => get_string('RON', 'block_courseshop'), // Roman leu
							'ILS' => get_string('ILS', 'block_courseshop'), // Shekel
							'KRW' => get_string('KRW', 'block_courseshop'), // Won (corea)
							'JPY' => get_string('JPY', 'block_courseshop'), // Yen (japan)
							'TND' => get_string('TND', 'block_courseshop'), // Dinar (Tunisian, internal market)
							'MAD' => get_string('MAD', 'block_courseshop'), // Dinar (Marocco, internal market)
					  );
	}
	
	return $CURRENCIES;
}

function courseshop_currency($block, $long = false){
	global $CFG;
	
	if (!isset($block->config->currency)){
		$currencycode = (isset($CFG->block_courseshop_defaultcurrency)) ? $CFG->block_courseshop_defaultcurrency : 'EUR' ;
	} else {
		$currencycode = $block->config->currency;
	}
	if ($long){
		if ($long == 'symbol'){
			return get_string($currencycode.'_symb', 'block_courseshop');
		}
		return get_string($currencycode, 'block_courseshop');
	}
	return $currencycode;
}


function block_courseshop_print_currency_choice($cur, $url, $cgicontext = array(), $return = false){

	$str = '';
	
    $usedcurrencies = get_records('courseshop_bill', '', '', '', ' DISTINCT(currency), currency ');
    if (count($usedcurrencies) > 1){
    	$curmenu = array();
    	foreach($usedcurrencies as $curid => $void){
    		$curmenu[$curid] = get_string($curid, 'block_courseshop');
    	}
    	$str .= "<form name=\"currencyselect\" action=\"$url\" method=\"POST\">";
    	foreach($cgicontext as $key => $value){
    		$str .= "<input type=\"hidden\" name=\"$key\" value=\"$value\" />\n";
    	}
    	$str .= choose_from_menu($curmenu, 'cur', $cur, 'choose', 'document.forms[\'currencyselect\'].submit();', false, true);
    	$str .= '</form>';
    }
    
    if ($return) return $str;
    echo $str;
}

function courseshop_get_sales_managers($blockid){
	global $CFG;
	
	$salesrole = get_record('role', 'shortname', 'sales');
	$blockcontext = get_context_instance(CONTEXT_BLOCK, $blockid);
	
	$sql = "
		SELECT DISTINCT
			u.*
		FROM
			{$CFG->prefix}user u,
			{$CFG->prefix}role_assignments ra
		WHERE
			u.id = ra.userid AND
			roleid = $salesrole->id AND
			contextid = $blockcontext->id
	";
	return get_records_sql($sql);
}

?>