<?php

require_once($CFG->dirroot . '/mod/forum/lib.php');

function block_export_forum_pluginfile($course, $record, $context, $filearea, $args, $forcedownload) {
    global $DB;

    $fs = get_file_storage();

    list($component, $area, $itemid, $filename) = $args;

    if ($component != 'mod_forum') {
        send_file_not_found();
    }

    $params = array(
        'contextid' => $context->id,
        'component' => $component,
        'filearea' => $area,
        'itemid' => $itemid,
        'filename' => $filename
    );

    $instanceid = $DB->get_field('files', 'id', $params);

    if (empty($instanceid)) {
        send_file_not_found();
    } else {
        $file = $fs->get_file_by_id($instanceid);
        send_stored_file($file);
    }
}

abstract class ExportForumPDF {
    abstract public function get_content($id);

    function __construct($id, $anonymize=false) {
        list($content, $filename) = $this->get_content($id);

        $this->generate($content, $filename, $anonymize);
    }

    function generate($content, $filename, $anonymize) {
        global $CFG;

        require_once($CFG->dirroot . '/blocks/export_forum/mpdf/mpdf.php');

        $css = file_get_contents($CFG->dirroot . '/blocks/export_forum/pdf.css');

        $mpdf = new mPDF('UTF-8-s');
        $mpdf->SetAutoFont(AUTOFONT_ALL);
        $mpdf->WriteHTML($css, 1);

        foreach ($content as $n => $page) {
            if ($n > 0) {
                $mpdf->AddPage();
            }

            $page = $this->sanitize_content($page);

            if ($anonymize) {
                $page = $this->anonymize($page);
            }

            $mpdf->WriteHTML($page);
        }

        $mpdf->Output($filename, 'D');
    }

    function sanitize_content($content) {
        global $CFG;

        $content = htmlspecialchars_decode(stripslashes($content));

        $c_id = context_module::instance($this->forum_id)->id;

        $search = '%2F' . $c_id . '%2Fmod_forum';
        $replace = '%2F' . $c_id . '%2Fblock_export_forum%2Fexport%2Fmod_forum';

        $content = str_replace($search, $replace, $content);

        return $content;
    }

    function get_discussion_content($discussion, $forum=NULL) {
        global $DB, $OUTPUT;

        if (!is_object($discussion)) {
            $params = array('id' => $discussion);
            $discussion = $DB->get_record('forum_discussions', $params);
        }

        if (!$forum) {
            $params = array('id' => $discussion->forum);
            $forum = $DB->get_record('forum', $params);
        }

        $this->forum_id = $forum->id;

        $params = array('id' => $discussion->course);
        $course = $DB->get_record('course', $params);

        $cm = get_coursemodule_from_instance('forum', $forum->id, $course->id);

        $params = array('id' => $discussion->firstpost);
        $post = $DB->get_record('forum_posts', $params);

        $mode = FORUM_MODE_NESTED;

        ob_start();

        try {
            forum_print_discussion($course, $cm, $forum, $discussion, $post, $mode);
        } catch (Exception $e) {
            $msg = get_string('output_failed', 'block_export_forum');
            echo $OUTPUT->notification($msg);
        }

        $content = ob_get_contents();

        ob_end_clean();

        $pdf_header = "
            <h2>$course->fullname &raquo; $forum->name &raquo; $discussion->name</h2>
        ";

        $filename = "$course->shortname-$forum->name-$discussion->name";

        $filename = str_replace(' ', '_', $filename);

        $all_content = $pdf_header . $content;

        return array($all_content, $filename);
    }

    function anonymize($content) {
        // User pictures
        $pattern = '/pluginfile\.php\/\d+[\?file=]*\/user\/icon\/\w+\/\w\d[\?[\w=\d]*]*/';
        $replace = 'blocks/export_forum/pix/user.png';

        $content = preg_replace($pattern, $replace, $content);

        // User names
        $pattern = '/by <a href=".+?user\/view.php\?id=.+?">(.+?)<\/a> -?/';

        preg_match_all($pattern, $content, $matches);

        $names = array_merge(array_unique($matches[1]));

        foreach ($names as $n => $name) {
            $content = str_replace($name, 'User ' . ($n +1), $content);
        }

        return $content;
    }
}

class ExportForumDiscussionPDF extends ExportForumPDF {
    function get_content($id) {
        list($content, $filename) = $this->get_discussion_content($id);

        $content = array($content);

        return array($content, $filename);
    }
}

class ExportForumForumPDF extends ExportForumPDF {
    function get_content($id) {
        global $DB;

        $forum = $DB->get_record('forum', array('id' => $id));

        $params = array('forum' => $id);
        $discussions = $DB->get_records('forum_discussions', $params);

        $content = array();

        foreach ($discussions as $d) {
            list($page, $filename) = $this->get_discussion_content($d);

            $content[] = $page;
        }

        $exploded_tmp = explode('-', $filename);
        $filename = str_replace('-' . end($exploded_tmp), '', $filename);

        return array($content, $filename);
    }
}
