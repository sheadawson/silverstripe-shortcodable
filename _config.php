<?php

use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\HTMLEditor\HtmlEditorConfig;
use Silverstripe\Shortcodable;

if (!defined('SHORTCODABLE_DIR')) {
    define('SHORTCODABLE_DIR', rtrim(basename(dirname(__FILE__))));
}
if (SHORTCODABLE_DIR != 'shortcodable') {
    throw new \Exception('The edit shortcodable module is not installed in correct directory. The directory should be named "shortcodable"');
}

// enable shortcodable buttons and add to HtmlEditorConfig
$htmlEditorNames = Config::inst()->get('Shortcodable', 'htmleditor_names');
if (is_array($htmlEditorNames)) {
    foreach ($htmlEditorNames as $htmlEditorName) {
        // HtmlEditorConfig::get($htmlEditorName)->enablePlugins(array(
        //     'shortcodable' => sprintf('/resources/%s/javascript/editor_plugin.js', SHORTCODABLE_DIR)
        // ));
        HtmlEditorConfig::get($htmlEditorName)->addButtonsToLine(1, 'shortcodable');
    }
}

// register classes added via yml config
$classes = Config::inst()->get('Shortcodable', 'shortcodable_classes');
Shortcodable::register_classes($classes);
