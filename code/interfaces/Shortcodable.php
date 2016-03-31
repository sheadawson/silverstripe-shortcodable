<?php
/**
 * Shortcodable
 * Manages shortcodable configuration and register shortcodable objects
 *
 * @author shea@livesource.co.nz
 **/
class Shortcodable extends Object
{
    private static $shortcodable_classes = array();

    public static function register_classes($classes)
    {
        if (is_array($classes) && count($classes)) {
            foreach ($classes as $class) {
                self::register_class($class);
            }
        }
    }

    public static function register_class($class)
    {
        if (class_exists($class)) {
            if (!singleton($class)->hasMethod('parseShortcode')) {
                user_error("Failed to register \"$class\" with shortcodable. $class must have the method parseShortcode(). See /shortcodable/README.md", E_USER_ERROR);
            }
            ShortcodeParser::get('default')->register($class, array($class, 'parseShortcode'));
            singleton('ShortcodableParser')->register($class);
        }
    }

    public static function get_shortcodable_classes()
    {
        return Config::inst()->get('Shortcodable', 'shortcodable_classes');
    }
}
