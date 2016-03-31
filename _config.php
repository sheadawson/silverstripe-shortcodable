<?php

if (!defined('SHORTCODABLE_DIR')) {
    define('SHORTCODABLE_DIR', rtrim(basename(dirname(__FILE__))));
}
if (SHORTCODABLE_DIR != 'shortcodable') {
    throw new Exception('The edit shortcodable module is not installed in correct directory. The directory should be named "shortcodable"');
}

// enable shortcodable buttons and add to HtmlEditorConfig
HtmlEditorConfig::get('cms')->enablePlugins(array('shortcodable' => sprintf('../../../%s/javascript/editor_plugin.js', SHORTCODABLE_DIR)));
HtmlEditorConfig::get('cms')->addButtonsToLine(1, 'shortcodable');

// register classes added via yml config
$classes = Config::inst()->get('Shortcodable', 'shortcodable_classes');
Shortcodable::register_classes($classes);
