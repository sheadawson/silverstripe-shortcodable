(function () {
    if (typeof tinymce !== 'undefined') {
        // Replace placeholder image with the shortcode.
        function getAttribute(string, attr) {
            attr = new RegExp(attr + '=\"([^\"]+)\"', 'g').exec(string);
            return attr ? tinymce.DOM.decode(attr[1]) : '';
        }

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
                var me = this;
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
                    o.content = me._showPlaceholder(o.content);
                });

                // On insert replace shortcode with placeholder.
                ed.onExecCommand.add(function (ed, cmd, ui, val) {
                    if (cmd === 'mceInsertContent') {
                        ed.setContent(me._showPlaceholder(ed.getContent()));
                    }
                });

                // On save replace placeholder with shortcode.
                ed.onPostProcess.add(function (e, o) {
                    o.content = me._getShortcode(o.content);
                });

                ed.onDblClick.add(function (ed, e) {
                    var dom = ed.dom, node = e.target;
                    if (node.nodeName === 'IMG' && dom.hasClass(node, 'shortcode-placeholder') && e.button !== 2) {
                        ed.execCommand('shortcodable');
                    }
                });
            },
            // Replace shortcode with a placeholder.
            _showPlaceholder: function (co) {
                return co.replace(/\[([a-z]+)\s+([^\]]*)\]/gi, function (found, name, params) {
                    var id = getAttribute(params, 'id');
                    var hasPlaceholder = getAttribute(params, 'HasPlaceholder');
                    if (hasPlaceholder == 1) {
                        var img = jQuery('<img/>')
                            .attr('class', 'shortcode-placeholder mceItem')
                            .attr('title', name + ' ' + params)
                            .attr('src', 'ShortcodableController/shortcodePlaceHolder/' + id + '/' + name);
                        return img.prop('outerHTML');
                    }

                    return found;
                });
            },
            // Get the shortcode from the placeholder.
            _getShortcode: function (co) {
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
            }
        });

        // Adds the plugin class to the list of available TinyMCE plugins
        tinymce.PluginManager.add("shortcodable", tinymce.plugins.shortcodable);
    }
})();
