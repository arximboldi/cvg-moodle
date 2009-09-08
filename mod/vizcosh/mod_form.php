<?php

require_once($CFG->dirroot.'/mod/vizcosh/lib.php');
require_once ($CFG->dirroot.'/course/moodleform_mod.php');

class mod_vizcosh_mod_form extends moodleform_mod {

    function definition() {

        global $CFG;
        $mform =& $this->_form;

//-------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), array('size'=>'64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('htmleditor', 'summary', get_string('summary'));
        $mform->setType('summary', PARAM_RAW);
        $mform->addRule('summary', null, 'required', null, 'client');
        $mform->setHelpButton('summary', array('writing', 'questions', 'richtext'), false, 'editorhelpbutton');

        $mform->addElement('select', 'numbering', get_string('numbering', 'vizcosh'), vizcosh_get_numbering_types());
        $mform->setHelpButton('numbering', array('numberingtype', get_string('numbering', 'vizcosh'), 'vizcosh'));

        $mform->addElement('checkbox', 'disableprinting', get_string('disableprinting', 'vizcosh'));
        $mform->setHelpButton('disableprinting', array('disableprinting', get_string('disableprinting', 'vizcosh'), 'vizcosh'));
        $mform->setDefault('disableprinting', 0);

        $mform->addElement('checkbox', 'disableemargo', get_string('disableemargo', 'vizcosh'));
        $mform->setHelpButton('disableemargo', array('disableemargo', get_string('disableemargo', 'vizcosh'), 'vizcosh'));
        $mform->setDefault('disableemargo', 0);

        $mform->addElement('checkbox', 'enablegroupfunction', get_string('enablegroupfunction', 'vizcosh'));
        $mform->setHelpButton('enablegroupfunction', array('enablegroupfunction', get_string('enablegroupfunction', 'vizcosh'), 'vizcosh'));
        $mform->setDefault('enablegroupfunction', 0);
        
        $this->standard_coursemodule_elements();

//-------------------------------------------------------------------------------
        // buttons
        $this->add_action_buttons();
    }


}
?>