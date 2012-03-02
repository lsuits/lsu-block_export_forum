<?php

require_once($CFG->libdir.'/formslib.php');

class export_forum_export_form extends moodleform {
    function definition() {
        global $COURSE, $DB;

        $_s = function($key) { return get_string($key, 'block_export_forum'); };

        $mform =& $this->_form;

        $id = $COURSE->id;

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('header', 'general', $_s('export'));

        $params = array('course' => $id);
        $fields = 'id, forum, name';
        $discussions = $DB->get_records('forum_discussions', $params, '', $fields);

        $d_options = array(0 => $_s('all_discussions'));

        $valid_forums = array();

        foreach ($discussions as $d) {
            $valid_forums[] = $d->forum;

            $key = $d->forum . '_' . $d->id;

            $d_options[$key] = $d->name;
        }

        $params = array('course' => $id);
        $forums = $DB->get_records('forum', $params, '', 'id, name');

        $f_options = array();

        foreach ($forums as $forum_id => $obj) {
            if (in_array($forum_id, $valid_forums)) {
                $f_options[$forum_id] = $obj->name;
            }
        }

        $mform->addElement('select', 'forum_id', $_s('forum'), $f_options);

        $mform->addElement('select', 'discussion_id', $_s('discussion'), $d_options);

        $mform->addElement('checkbox', 'anonymize', $_s('anonymize'));

        $this->add_action_buttons(false, $_s('export'));
    }
}
