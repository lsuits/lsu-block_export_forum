<?php

// Written at Louisiana State University

require_once($CFG->libdir.'/formslib.php');

class block_export_forum extends block_list {
    function init() {
        $this->title = get_string('pluginname', 'block_export_forum');
    }

    function applicable_formats() {
        return array('site' => false, 'my' => false, 'course' => true);
    }

    function get_content() {
        global $COURSE, $OUTPUT;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = [];
        $this->content->icons = [];
        $this->content->footer = '';

        $label = get_string('pluginname', 'block_export_forum');
        
        $icon = $OUTPUT->pix_icon('i/export', $label, 'moodle', ['class' => 'icon']);

        $this->content->items = [
            html_writer::link(
                new moodle_url('/blocks/export_forum/export.php', ['id' => $COURSE->id]),
                $icon . $label
            )
        ];

        return $this->content;
    }
}
