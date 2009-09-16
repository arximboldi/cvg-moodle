<?php 
/**
 * page class for vicosh
 *
 * @author Andreas Kothe
 **/

require_once($CFG->libdir . '/pagelib.php');

/**
 * define the page types
 *
 **/
define('PAGE_VIZCOSH_VIEW', 'mod-vizcosh-view');

/**
 * map the classes to the page types
 *
 **/
page_map_class(PAGE_VIZCOSH_VIEW, 'page_vizcosh');

/**
 * add the page types defined in this file
 *
 **/
$DEFINEDPAGES = array(PAGE_VIZCOSH_VIEW);

/**
 * class that models the behavior of the vizcosh module
 *
 * @author Andreas Kothe
 **/
class page_vizcosh extends page_generic_activity {
	
	/**
	 * Module name
	 **/
	var $activityname = 'vizcosh';
	
	/**
	 * Current vizcosh page ID
	 **/
	var $vizcoshpageid = NULL;
	
	/**
	 * Print a heading
	 * @return void
	 **/
	function print_header($title = '', $morenavlinks = array(), $chapter, $meta) {
		global $CFG;
		
		$this->init_full();
		
		// variable setup/check
		$context		= get_context_instance(CONTEXT_MODULE, $this->modulerecord->id);
		$activityname	= format_string($this->activityrecord->name);
		
		if ($this->vizcoshpageid === NULL) {
			error('Programmer error: must set the vizcosh page ID');
		}
		if (empty($title)) {
			$title = "{$this->courserecord->shortname}: $activityname";
		}
        
		// build the buttons
		if (has_capability('mod/vizcosh:edit', $context)) {
			$buttons = '<span class="edit_buttons">' . update_module_button($this->modulerecord->id, $this->courserecord->id, get_string('modulename', 'vizcosh'));
			if (!empty($this->vizcoshpageid)) {
				$buttons .= vizcosh_edit_button($this->modulerecord->id, $this->courserecord->id, $chapter->id);
			}
			$buttons .= '</span>';
		} else {
			$buttons = '&nbsp;';
		}

		$navigation = build_navigation($morenavlinks, $this->modulerecord);
		print_header($title, $this->courserecord->fullname, $navigation, '', $meta, true, $buttons, navmenu($this->courserecord, $this->modulerecord));
		
/*
		if (has_capability('mod/vizcosh:manage', $context)) {
			print_heading_with_help($activityname, 'index', 'vizcosh');
		} else {
			print_heading($activityname);
		}
*/
		  vizcosh_print_jsxaal_header ();
		vizcosh_print_messages();
	}
		
	function get_type() {
		return PAGE_VIZCOSH_VIEW;
	}
	
	function blocks_get_positions() {
		return array(BLOCK_POS_LEFT, BLOCK_POS_RIGHT);
	}
	
	function blocks_default_position() {
		return BLOCK_POS_RIGHT;
	}
	
	function blocks_move_position(&$instance, $move) {
		if($instance->position == BLOCK_POS_LEFT && $move == BLOCK_MOVE_RIGHT) {
			return BLOCK_POS_RIGHT;
		} else if ($instance->position == BLOCK_POS_RIGHT && $move == BLOCK_MOVE_LEFT) {
			return BLOCK_POS_LEFT;
		}
		return $instance->position;
	}
	
	/**
	 * needed to add the ID of the current vizcosh page
	 *
	 * @return array
	 **/
	function url_get_parameters() {
		$this->init_full();
		return array('id' => $this->modulerecord->id, 'pageid' => $this->vizcoshpageid);
	}
	
	/**
	 * set the current vizcosh page-ID
	 *
	 * @return void
	 **/
	function set_vizcoshpageid($pageid) {
		$this->vizcoshpageid = $pageid;
	}
}

?>
