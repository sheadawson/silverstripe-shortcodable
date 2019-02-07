<?php
/**
 * ShortcodableParser - temporary measure, based on wordpress parser
 * This parser is only used to parse tags in the html editor field for editing in the popup window.
 *
 * @todo update SS ShortcodeParser to offer a public api for converting a shortcode to a data array, and use that instead.
 */
class ShortcodableParser extends SS_Object
{
    /**
     * @var array
     */
    protected $shortcodes = array();

    /**
     * @param string $name
     */
    public function register($name)
    {
        $this->shortcodes[$name] = $name;
    }

    /**
     * @param string $text
     * @return array
     */
    public function get_pattern($text)
    {
        $pattern = $this->get_shortcode_regex();
        preg_match_all("/$pattern/s", $text, $c);

        return $c;
    }

    /**
     * @param string $content
     * @return array
     */
    public function parse_atts($content)
    {
        $content = preg_match_all('/([^ =]*)=(\'([^\']*)\'|\"([^\"]*)\"|([^ ]*))/', trim($content), $c);
        list($dummy, $keys, $values) = array_values($c);
        $c = array();
        foreach ($keys as $key => $value) {
            $value = trim($values[ $key ], "\"'");
            $type = is_numeric($value) ? 'int' : 'string';
            $type = in_array(strtolower($value), array('true', 'false')) ? 'bool' : $type;
            switch ($type) {
                case 'int': $value = (int) $value; break;
                case 'bool': $value = strtolower($value) == 'true'; break;
            }
            $c[ $keys[ $key ] ] = $value;
        }

        return $c;
    }

    /**
     * @param array $output
     * @param string $text
     * @param boolean $child
     * @return array
     */
    public function the_shortcodes($output, $text, $child = false)
    {
        $patts = $this->get_pattern($text);
        $t = array_filter($this->get_pattern($text));
        if (!empty($t)) {
            list($d, $d, $parents, $atts, $d, $contents) = $patts;
            $out2 = array();
            $n = 0;
            foreach ($parents as $k => $parent) {
                ++$n;
                $name = $child ? 'child'.$n : $n;
                $t = array_filter($this->get_pattern($contents[ $k ]));
                $t_s = $this->the_shortcodes($out2, $contents[ $k ], true);
                $output[ $name ] = array('name' => $parents[ $k ]);
                $output[ $name ]['atts'] = $this->parse_atts($atts[ $k ]);
                $output[ $name ]['original_content'] = $contents[ $k ];
                $output[ $name ]['content'] = !empty($t) && !empty($t_s) ? $t_s : $contents[ $k ];
            }
        }

        return array_values($output);
    }

    /**
     * @return string
     */
    public function get_shortcode_regex()
    {
        $shortcode_tags = $this->shortcodes;
        $tagnames = array_keys($shortcode_tags);
        $tagregexp = implode('|', array_map('preg_quote', $tagnames));

        // WARNING! Do not change this regex without changing do_shortcode_tag() and strip_shortcode_tag()
        // Also, see shortcode_unautop() and shortcode.js.
        return
            '\\['                              // Opening bracket
            .'(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
            ."($tagregexp)"                     // 2: Shortcode name
            .'(?![\\w-])'                       // Not followed by word character or hyphen
            .'('                                // 3: Unroll the loop: Inside the opening shortcode tag
            .'[^\\]\\/]*'                   // Not a closing bracket or forward slash
            .'(?:'
            .'\\/(?!\\])'               // A forward slash not followed by a closing bracket
            .'[^\\]\\/]*'               // Not a closing bracket or forward slash
            .')*?'
            .')'
            .'(?:'
            .'(\\/)'                        // 4: Self closing tag ...
            .'\\]'                          // ... and closing bracket
            .'|'
            .'\\]'                          // Closing bracket
            .'(?:'
            .'('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
            .'[^\\[]*+'             // Not an opening bracket
            .'(?:'
            .'\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
            .'[^\\[]*+'         // Not an opening bracket
            .')*+'
            .')'
            .'\\[\\/\\2\\]'             // Closing shortcode tag
            .')?'
            .')'
            .'(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]]
    }
}
