<?php // $Id: mod_form.php,v 1.1.2.3 2009/03/06 15:47:04 mudrd8mz Exp $
require_once ($CFG->dirroot.'/course/moodleform_mod.php');

class mod_groupselect_mod_form extends moodleform_mod {

    function definition() {
        global $COURSE;

        $mform    =& $this->_form;

        $mform->addElement('text', 'name', get_string('groupselectname', 'groupselect'),
			   array('size'=>'64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('htmleditor', 'intro', get_string('intro', 'groupselect'));
        $mform->setType('intro', PARAM_RAW);
        $mform->addRule('intro', get_string('required'), 'required', null, 'client');
        $mform->setHelpButton('intro', array('questions', 'richtext'), false, 'editorhelpbutton');

        $options = array();
        $options[0] = get_string('fromallgroups', 'groupselect');
        if ($groupings = groups_get_all_groupings($COURSE->id)) {
            foreach ($groupings as $grouping) {
                $options[$grouping->id] = format_string($grouping->name);
            }
        }
        $mform->addElement('select', 'targetgrouping', get_string('targetgrouping', 'groupselect'), $options);

        $mform->addElement('passwordunmask', 'password', get_string('password', 'groupselect'), 'maxlength="254" size="24"');
        $mform->setType('password', PARAM_RAW);
	
        $mform->addElement('date_time_selector', 'timeavailable', get_string('timeavailable', 'groupselect'), array('optional'=>true));
        $mform->setDefault('timeavailable', 0);
        $mform->addElement('date_time_selector', 'timedue', get_string('timedue', 'groupselect'), array('optional'=>true));
        $mform->setDefault('timedue', 0);

	/* Set max members per group */
	$mform->addElement('header', '', get_string('maxmembers', 'groupselect'), array ());
	$allgroups = groups_get_all_groups ($COURSE->id);
	foreach ($allgroups as $grp)
	  {
	    $element = "maxmembers_{$grp->id}";
	    $mform->addElement('text', $element,
			       get_string('maxmembers_group', 'groupselect', $grp->name),
			       array('size'=>'4'));
	    $mform->setType($element, PARAM_INT);
	    $mform->setDefault($element, 0);
	  }
	
        $features = array('groups'=>true, 'groupings'=>true, 'groupmembersonly'=>true,
                          'outcomes'=>false, 'gradecat'=>false, 'idnumber'=>false);
    
        $this->standard_coursemodule_elements($features);
        $this->add_action_buttons();
    }

    function validation($data, $files) {
        global $COURSE;
        $errors = parent::validation($data, $files);

        $mform =& $this->_form;

	$allgroups = groups_get_all_groups ($COURSE->id);
	foreach ($allgroups as $grp)
	  {
	    $element = "maxmembers_{$grp->id}";
	    if (array_key_exists ($element, $data))
	      {
		$maxmembers = $data[$element];
		
		if ($maxmembers < 0) {
		  $errors[$element] = get_string('error');
		}
	      }
	  }
        
        return $errors;
    }

    
    /* TODO: is this dirty?? */
    function set_data ($data, $slashed=false)
    {
      global $COURSE;
      
      if ($data->instance)
	{
	  $allgroups = groups_get_all_groups ($COURSE->id);
	  foreach ($allgroups as $grp)
	    {
	      $grpinfo = groupselect_get_groupinfo ($data->instance, $grp->id);
	      if ($grpinfo)
		$data->{'maxmembers_' . $grp->id} = $grpinfo->maxmembers;
	    }
	}
      
      parent::set_data ($data, $slashed);
    }
}
?>
