(function () {
    if (typeof tinymce !== 'undefined') {

        tinymce.create('tinymce.plugins.shortcodable', {
            getInfo: function () {
                return {
                    longname: 'Shortcodable - Shortcode UI plugin for SilverStripe',
                    author: 'Shea Dawson',
                    authorurl: 'http://www.livesource.co.nz/',
                    infourl: 'http://www.livesource.co.nz/',
                    version: "1.0"
                };
            },

            init: function (ed, url) {
                ed.addButton('shortcodable', {
                    title: 'Insert Shortcode',
                    cmd: 'shortcodable',
                    'class': 'mce_shortcode'
                });

                ed.addCommand('shortcodable', function (ed) {
                    jQuery('#' + this.id).entwine('ss').openShortcodeDialog();
                });

                // On load replace shorcode with placeholder.
                ed.on('SetContent', function (event) {
                    var me = tinyMCE.activeEditor.plugins.shortcodable;
                    if (!me) {
                        return;
                    }
                    var newContent = me.replaceShortcodesWithPlaceholders(event.content, ed);
                    ed.execCommand('setContent', false, newContent, false);
                });

                ed.on('DblClick', function (event) {
                    var dom = ed.dom, node = event.target;
                    if (node.nodeName === 'IMG' && dom.hasClass(node, 'shortcode-placeholder') && event.button !== 2) {
                        ed.execCommand('shortcodable');
                    }
                });
            },

            // replace shortcode strings with placeholder images
            replaceShortcodesWithPlaceholders: function (content, editor) {
                var plugin = tinyMCE.activeEditor.plugins.shortcodable;
                var placeholderClasses = jQuery('#' + editor.id).entwine('ss').getPlaceholderClasses();

                if (placeholderClasses) {
                    return content.replace(/\[([a-z]+)\s*([^\]]*)\]/gi, function (found, name, params) {
                        var id = plugin.getAttribute(params, 'id');
                        if (placeholderClasses.indexOf(name) != -1) {
                            var src = encodeURI('ShortcodableController/shortcodePlaceHolder/' + name + '/' + id + '?Shortcode=[' + name + ' ' + params + ']');
                            var img = jQuery('<img/>')
                                .attr('class', 'shortcode-placeholder mceItem')
                                .attr('title', name + ' ' + params)
                                .attr('src', src);
                            return img.prop('outerHTML');
                        }

                        return found;
                    });
                } else {
                    return content;
                }
            },

            // replace placeholder tags with shortcodes
            replacePlaceholdersWithShortcodes: function (co) {
                var content = jQuery(co);
                content.find('.shortcode-placeholder').each(function () {
                    var el = jQuery(this);
                    var shortCode = '[' + tinymce.trim(el.attr('title')) + ']';
                    el.replaceWith(shortCode);
                });
                var originalContent = '';
                content.each(function () {
                    if (this.outerHTML !== undefined) {
                        originalContent += this.outerHTML;
                    }
                });
                return originalContent;
            },

            // get an attribute from a shortcode string by it's key
            getAttribute: function (string, key) {
                var attr = new RegExp(key + '=\"([^\"]+)\"', 'g').exec(string);
                return attr ? tinymce.DOM.decode(attr[1]) : '';
            }
        });

        // Adds the plugin class to the list of available TinyMCE plugins
        tinymce.PluginManager.add("shortcodable", tinymce.plugins.shortcodable);
    }
})();
