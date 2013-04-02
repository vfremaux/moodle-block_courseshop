<?php //$Id: block_courseshop.php,v 1.15 2012-06-18 15:16:12 vf Exp $

class block_courseshop extends block_base {

    function init() {

        $this->title = get_string('blocktitle', 'block_courseshop');
        $this->version = 2012012100;
    }

    function has_config() {
	    return true;
	}
	
	function specialization(){
        $this->title = isset($this->config->blocktitle) ? format_string($this->config->blocktitle) : $this->title;
	}

    function instance_allow_config() {
        return true;
    }
    
    function instance_allow_multiple(){
    	return true;
    }

    function applicable_formats() {
        // Default case: the block can be used in all course types
        return array('all' => true);
    }

    function get_content() {
        global $CFG;

        if (empty($CFG->block_courseshop_catalogue)){
            set_config('block_courseshop_catalogue', 1);
        }

        $context = get_context_instance(CONTEXT_BLOCK, $this->instance->id);

        $this->content = new Object;
        
        $this->content->text = '';
        $this->content->footer = '';
        
        // unlogged people can see 
        /*
        if (!isloggedin() || !has_capability('block/courseshop:hasglobaladminrole', $systemcontext)){
            return $this->content;
        }
        */

		$pinned = @$this->instance->pinned;
        $this->content->text .= '<div class="courseshop">';
        $this->content->text .= "<a href=\"{$CFG->wwwroot}/blocks/courseshop/shop/view.php?view=shop&id={$this->instance->id}&pinned={$pinned}\">".get_string('shop', 'block_courseshop').'</a>';
        $this->content->text .= '</div>';

        if (has_capability('block/courseshop:salesadmin', $context)){
            $this->content->footer = "<a href=\"{$CFG->wwwroot}/blocks/courseshop/index.php?id={$this->instance->id}&pinned={$pinned}\">".get_string('admin', 'block_courseshop').'</a>';
        }

        return $this->content;
    }

	function after_install(){
		// create the teacherowner role if absent
		$courseownerid = create_role(get_string('courseowner', 'block_courseshop'), 'courseowner', addslashes(get_string('courseownerdesc', 'block_courseshop')), 'editingteacher');
		// create the categoryowner role if absent
		$categoryownerid = create_role(get_string('categoryowner', 'block_courseshop'), 'categoryowner', addslashes(get_string('categoryownerdesc', 'block_courseshop')), 'coursecreator');

        $editingteacher   = get_record('role', 'shortname', 'editingteacher');
        role_cap_duplicate($editingteacher, $courseownerid);

        $coursecreator   = get_record('role', 'shortname', 'coursecreator');
        role_cap_duplicate($coursecreator, $categoryownerid);
	}

	function before_uninstall(){
		global $CFG;
		
        $editingteacherid   = get_field('role', 'id', 'shortname', 'editingteacher');
        $courseownerid   = get_field('role', 'id', 'shortname', 'courseowner');

        $coursecreatorid   = get_field('role', 'id', 'shortname', 'coursecreator');
        $categoryownerid   = get_field('role', 'id', 'shortname', 'categoryowner');

		// remap all teacherowner assignments to editingteacher
		$sql = "
			UPDATE 
				{$CFG->prefix}role_assignment
			SET
				roleid = $editingteacherid
			WHERE
				roleid = $courseownerid
		";
		execute_sql($sql, false);

		// remap all categoryowner assignments to coursecreator
		$sql = "
			UPDATE 
				{$CFG->prefix}role_assignment
			SET
				roleid = $coursecreatorid
			WHERE
				roleid = $categoryownerid
		";
		execute_sql($sql, false);
		
		// delete the teacherowner role if absent
		delete_role(get_string('teacherowner', 'block_courseshop'), 'teacherowner', addslashes(get_string('teacherownerdesc', 'block_courseshop')));
		delete_role(get_string('categoryowner', 'block_courseshop'), 'categoryowner', addslashes(get_string('categoryownerdesc', 'block_courseshop')));
	}
}
