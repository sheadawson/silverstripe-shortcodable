<?php

namespace Silverstripe\Shortcodable;

use SilverStripe\Core\Extension;
use SilverStripe\Core\Config\Config;

class ShortcodableShortcodeParserExtension extends Extension
{
    public function onBeforeParse(&$content)
    {
        $parser = $this->owner;
        // Check the shortcode type and convert wrapper to div if block type
        // Regex examples: https://regex101.com/r/bFtD9o/3
        $content = preg_replace_callback(
            '|<p( [^>]*?)?>\s*?\[((.*)([\s,].*)?)\]\s*?</p>|U',
            function ($matches) use($parser) {
                $shortcodeName = $matches[3];
                // Since we're only concerned with shortcodable objects we know the
                // shortcode name will be the class name so don't have to look it up
                if ($shortcodeName && $parser->registered($shortcodeName)) {
                    if (Config::inst()->get($shortcodeName, 'shortcodable_is_block') && Config::inst()->get($shortcodeName, 'disable_wrapper')) {
                        return "[$matches[2]]";
                    }
                    if (Config::inst()->get($shortcodeName, 'shortcodable_is_block')) {
                        return "<div$matches[1]>[$matches[2]]</div>";
                    }
                }
                return $matches[0];
            },
            $content
        );
    }
}
