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
                var me = tinyMCE.activeEditor.plugins.shortcodable;

                ed.addButton('shortcodable', {
                    title: 'Insert Shortcode',
                    cmd: 'shortcodable',
                    'class': 'mce_shortcode'
                });

                ed.addCommand('shortcodable', function (ed) {
                    jQuery('#' + this.id).entwine('ss').openShortcodeDialog();
                });

                // On load replace shorcode with placeholder.
                ed.onBeforeSetContent.add(function (ed, o) {
                    o.content = me._replaceShortcodesWithPlaceholders(o.content, ed);
                });

                // On insert replace shortcode with placeholder.
                ed.onExecCommand.add(function (ed, cmd, ui, val) {
                    if (cmd === 'mceInsertContent') {
                        ed.setContent(me._replaceShortcodesWithPlaceholders(ed.getContent(), ed));
                    }
                });

                // On save replace placeholder with shortcode.
                ed.onPostProcess.add(function (e, o) {
                    o.content = me._replacePlaceholdersWithShortcodes(o.content, ed);
                });

                ed.onDblClick.add(function (ed, e) {
                    var dom = ed.dom, node = e.target;
                    if (node.nodeName === 'IMG' && dom.hasClass(node, 'shortcode-placeholder') && e.button !== 2) {
                        ed.execCommand('shortcodable');
                    }
                });
            },

            // replace shortcode strings with placeholder images
            _replaceShortcodesWithPlaceholders: function (content, editor) {
                var plugin = tinyMCE.activeEditor.plugins.shortcodable;
                var placeholderClasses = jQuery('#' + editor.id).entwine('ss').getPlaceholderClasses();
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
            },

            // replace placeholder tags with shortcodes
            _replacePlaceholdersWithShortcodes: function (co) {
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
