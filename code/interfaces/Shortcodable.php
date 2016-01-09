<?php
/**
 * Shortcodable interface
 * Apply this interface to your DataObject or ViewableData subclasses that wish
 * to implement the Shortcodable functionaility.
 *
 * @author shea@livesource.co.nz
 **/
interface Shortcodable
{
    /**
     * Parse the shortcode and render as a string, probably with a template.
     *
     * @param array           $arguments the list of attributes of the shortcode
     * @param string          $content   the shortcode content
     * @param ShortcodeParser $parser    the ShortcodeParser instance
     * @param string          $shortcode the raw shortcode being parsed
     *
     * @return string
     **/
    public static function parse_shortcode($arguments, $content, $parser, $shortcode);

    /**
     * returns a list of fields for editing the shortcode's attributes.
     *
     * @return Fieldlist
     **/
    public static function shortcode_attribute_fields();
}
