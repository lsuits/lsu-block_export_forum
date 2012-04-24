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

        $_s = function($key) { return get_string($key, 'block_export_forum'); };

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;

        $this->content->icons  = array();

        $params = array('id' => $COURSE->id);
        $url = new moodle_url('/blocks/export_forum/export.php', $params);
        $link = html_writer::link($url, $_s('pluginname'));

        $this->content->icons[] = $OUTPUT->pix_icon(
            'icon', $_s('pluginname'),
            'block_export_forum', array('class' => 'icon')
        );
        $items = array($link);

        $this->content->items = $items;
        $this->content->footer = '';

        return $this->content;
    }
}
