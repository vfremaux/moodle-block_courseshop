<?php
/**
 * Moodle - Modular Object-Oriented Dynamic Learning Environment
 *          http://moodle.org
 * Copyright (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    block-courseshop
 * @subpackage shop admin
 * @author     Valery Fremaux <valery@valeisti.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 *
 * allows editing catalog instances.
 *
 */

    include "../../../config.php";
    include_once $CFG->dirroot.'/blocks/courseshop/locallib.php';
    include_once $CFG->dirroot.'/blocks/courseshop/forms/form_catalog.class.php';

/// get the block reference and key context

	$id = required_param('id', PARAM_INT); // the blockid
	$pinned = optional_param('pinned', 0, PARAM_INT);
    $theBlock = courseshop_get_block_instance($id, $pinned);

/// Security

    $context = get_context_instance(CONTEXT_BLOCK, $id);
    require_capability('block/courseshop:salesadmin', $context);
    
    $catalogid = optional_param('catalogid', null, PARAM_INT);
    
    $context = get_context_instance(CONTEXT_BLOCK, $id);
    
    require_capability('block/courseshop:salesadmin', $context);
    
    $cmd = optional_param('cmd', PARAM_TEXT);

    if ($cmd != ''){
        $return = include $CFG->dirroot.'/blocks/courseshop/catalogs/catalogs.controller.php';
        if ($return == -1){
            redirect($CFG->wwwroot."/blocks/courseshop/index.php?id={$id}&pinned={$pinned}");
        }
    }

/// make page header and navigation

    $navlinks = array();
    $navigation = build_navigation('');
    print_header(get_string('blockname', 'block_courseshop'), get_string('blockname', 'block_courseshop'), '', $navigation);


    if ($catalogid){
        $catalog = get_record('courseshop_catalog', 'id', $catalogid);
        $mform = new Catalog_Form('edit', $CFG->wwwroot."/blocks/courseshop/catalogs/edit_catalogue.php?id={$id}&pinned={$pinned}");
        $catalog->catalogid = $catalogid;
    	$mform->set_data($catalog);
    } else {
        $mform = new Catalog_Form('new', $CFG->wwwroot."/blocks/courseshop/catalogs/edit_catalogue.php?id={$id}&pinned={$pinned}");
    }

    if ($data = $mform->get_data()){

    	unset($data->id);
        if (empty($data->linked)) $data->linked = 'free';
        
        if ($data->linked != 'free'){
            if ($data->linked == 'master'){
                $data->groupid = 0;
            }
        } else {
            $data->groupid = 0;
        }
        
        if (empty($data->catalogid)){
            $newid = insert_record('courseshop_catalog', $data);
            if($data->linked == 'master'){
	            set_field('courseshop_catalog', 'groupid', $newid, 'id', $newid);
	        }
        } else {
        	$data->id = $data->catalogid;
        	// we need to release all old slaves if this catalog changes from master to standalone
        	if ($oldcatalog = get_record('courseshop_catalog', 'id', $data->id)){
	        	if (($oldcatalog->id == $oldcatalog->groupid) && $data->linked == 'free'){
	        		// get all slaves but not me
	        		// TODO : may have further side effects, but we'll see later.
	        		if($oldslaves = get_records_select('courseshop_catalog', " groupid = $oldcatalog->id AND groupid != id ")){
		        		foreach($oldslaves as $oldslave){
		        			$oldslave->groupid = 0;
		        			update_record('courseshop_catalog', $oldslave);
		        		}
		        	}
	        	}
	        }
        	
            $updateid = update_record('courseshop_catalog', $data);
            if($data->linked == 'master'){
	            set_field('courseshop_catalog', 'groupid', $newid, 'id', $newid);
	        }
        }
        redirect($CFG->wwwroot."/blocks/courseshop/index.php?id={$id}&pinned={$pinned}");
    } else {
        $mform->display();
    }

?>