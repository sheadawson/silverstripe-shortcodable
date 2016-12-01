<?php

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
                return ($shortcodeName && $parser->registered($shortcodeName)
                    && Config::inst()->get($shortcodeName, 'shortcodable_is_block'))
                    ? "<div$matches[1]>[$matches[2]]</div>"
                    : $matches[0];
            },
            $content
        );
    }
}
