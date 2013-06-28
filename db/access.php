<?php
$capabilities = array(

    'block/export_forum:addinstance' => array(
            'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'teacher' => CAP_ALLOW
        ),
    'clonepermissionsfrom' => 'moodle/site:manageblocks'
    ),
);
    
?>
