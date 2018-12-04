<?php

use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Manifest\ModuleResourceLoader;
use SilverStripe\Forms\HTMLEditor\TinyMCEConfig;
use Silverstripe\Shortcodable\Shortcodable;

// enable shortcodable buttons and add to HtmlEditorConfig
$htmlEditorNames = Config::inst()->get(Shortcodable::class, 'htmleditor_names');

if (is_array($htmlEditorNames)) {
    $plugin = ModuleResourceLoader::singleton()
        ->resolveURL('sheadawson/silverstripe-shortcodable:javascript/editor_plugin.js');

    foreach ($htmlEditorNames as $htmlEditorName) {
        TinyMCEConfig::get($htmlEditorName)->enablePlugins(['shortcodable' => $plugin]);
        TinyMCEConfig::get($htmlEditorName)->addButtonsToLine(1, 'shortcodable');
    }
}

// register classes added via yml config
$classes = Config::inst()->get(Shortcodable::class, 'shortcodable_classes');
Shortcodable::register_classes($classes);
