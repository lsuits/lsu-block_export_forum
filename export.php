<?php

// Written at Louisiana State University

require_once('../../config.php');

require_once('forms.php');
require_once('lib.php');

$_s = function($key, $a=NULL) { return get_string($key, 'block_export_forum', $a); };

$id = required_param('id', PARAM_INT);

require_login($id);

// Page Setup
$pluginname= $_s('pluginname');

$context = context_course::instance($id);

if (!has_capability('moodle/course:update', $context)) {
    print_error('no_permission', 'block_export_forum');
}

$course = $DB->get_record('course', array('id' => $id));

$PAGE->set_context($context);
$PAGE->set_course($course);

$PAGE->navbar->add($pluginname);
$PAGE->set_title($pluginname);
$PAGE->set_heading($SITE->shortname . ': ' . $pluginname);
$PAGE->set_url('/blocks/export_forum/export.php');
$PAGE->set_pagetype($pluginname);

$PAGE->requires->js('/blocks/export_forum/js/jquery.js');
$PAGE->requires->js('/blocks/export_forum/js/export.js');

$export_form = new export_forum_export_form();

if ($form_data = $export_form->get_data()) {
    $f_id = $form_data->forum_id;
    $d_id = $form_data->discussion_id;
    $anonymize = isset($form_data->anonymize) ? $form_data->anonymize : false;

    if ($d_id) {
        list($f_id, $d_id) = explode('_', $d_id);
        $x = new ExportForumDiscussionPDF($d_id, $anonymize);
    } else if ($f_id) {
        $x = new ExportForumForumPDF($f_id, $anonymize);
    } else {
        print_error();
    }
} else {
    echo $OUTPUT->header();
    echo $OUTPUT->heading($pluginname);

    $export_form->display();

    echo $OUTPUT->footer();
}
