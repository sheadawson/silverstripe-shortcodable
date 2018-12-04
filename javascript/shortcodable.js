String.prototype.replaceAll = function(search, replacement) {
    var target = this;
    return target.replace(new RegExp(search, 'g'), replacement);
};

(function($) {
    $.entwine('ss', function($) {

        // handle change on shortcode-type field
        $('select.shortcode-type').entwine({
            onchange: function(){
                this.parents('form:first').reloadForm('type', this.val());
            }
        });

        // open shortcode dialog
        $('textarea.htmleditor').entwine({
            getPlaceholderClasses: function() {
                var classes = $(this).data('placeholderclasses');
                if (classes) {
                    return classes.split(',');
                }
            },
            openShortcodeDialog: function() {
                var capitalize = function(text) {
                    return text.charAt(0).toUpperCase() + text.slice(1).toLowerCase();
                };

                var type = 'shortcode';

                var self = this, url = 'ShortcodableController/ShortcodeForm/forTemplate',
                    dialog = $('.htmleditorfield-' + type + 'dialog');

                if(dialog.length) {
                    dialog.getForm().setElement(this);
                    dialog.open();
                } else {
                    // Show a placeholder for instant feedback. Will be replaced with actual
                    // form dialog once its loaded.
                    dialog = $('<div class="htmleditorfield-dialog htmleditorfield-' + type + 'dialog loading">');
                    $('body').append(dialog);
                    $.ajax({
                        url: url,
                        complete: function() {
                            dialog.removeClass('loading');
                        },
                        success: function(html) {
                            dialog.html(html);
                            dialog.getForm().setElement(self);
                            dialog.trigger('ssdialogopen');
                        }
                    });
                }
            },
            /**
             * Make sure the editor has flushed all it's buffers before the form is submitted.
             */
            'from .cms-edit-form': {
                onbeforesubmitform: function(e) {
                    var shortcodable = tinyMCE.activeEditor.plugins.shortcodable;
                    var ed = this.getEditor();
                    var newContent = shortcodable.replacePlaceholdersWithShortcodes($(this).val(), ed);
                    $(this).val(newContent);
                }
            },
        });

        $('.htmleditorfield-dialog').entwine({
            onadd: function() {
                // Create jQuery dialog
                if (!this.is('.ui-dialog-content')) {
                    this.ssdialog({autoOpen: true});
                }

                this._super();
            },

            getForm: function() {
                return this.find('form');
            },
            open: function() {
                this.ssdialog('open');
            },
            close: function() {
                this.ssdialog('close');
            },
            toggle: function(bool) {
                if(this.is(':visible')) this.close();
                else this.open();
            }
        });

        $('form.htmleditorfield-shortcodable').entwine({
            Selection: null,

            // Implementation-dependent serialization of the current editor selection state
            Bookmark: null,

            // DOMElement pointing to the currently active textarea
            Element: null,

            setSelection: function(node) {
                return this._super($(node));
            },

            onremove: function() {
                this.setSelection(null);
                this.setBookmark(null);
                this.setElement(null);

                this._super();
            },

            // load the shortcode form into the dialog
            reloadForm: function(from, data) {
                var postdata = {};
                if(from == 'type'){
                    postdata.ShortcodeType = data;
                }else if(from =='shortcode'){
                    postdata.Shortcode = data;
                }

                this.addClass('loading');

                var url = 'ShortcodableController/ShortcodeForm/forTemplate';

                $.post(url, postdata, function(data){
                    var form = $('form.htmleditorfield-shortcodable')
                    form.find('fieldset').replaceWith($(data).find('fieldset')).show();
                    form.removeClass('loading');
                });
                return this;
            },
            // shortcode form submit handler
            onsubmit: function(e) {
                this.insertShortcode();
                this.getDialog().close();
                return false;
            },
            // insert shortcode into editor
            insertShortcode: function() {
                var shortcode = this.getHTML();
                if (shortcode.length) {
                    this.modifySelection(function(ed){
                        var shortcodable = tinyMCE.activeEditor.plugins.shortcodable;
                        ed.replaceContent(shortcode);

                        if (this.getElement().getPlaceholderClasses()) {
                            var newContent = shortcodable.replaceShortcodesWithPlaceholders(ed.getContent(), ed.getInstance());
                            ed.setContent(newContent);
                        }
                    });
                }
            },
            modifySelection: function(callback) {
                var ed = this.getEditor();

                ed.moveToBookmark(this.getBookmark());
                callback.call(this, ed);

                this.setSelection(ed.getSelectedNode());
                this.setBookmark(ed.createBookmark());

                ed.blur();
            },
            getEditor: function(){
                return this.getElement().getEditor();
            },
            getDialog: function() {
                return this.closest('.htmleditorfield-dialog');
            },
            // get the html to insert
            getHTML: function(){
                var data = this.getAttributes();
                var html = data.shortcodeType;

                for (var key in data.attributes) {
                    html += ' ' + key + '="' + data.attributes[key] + '"';
                }

                if (html.length) {
                    return "[" + html + "]";
                } else {
                    return '';
                }
            },
            // get shortcode attributes from shortcode form
            getAttributes: function() {
                var attributes = {};
                var id = this.find(':input[name=id]').val();
                if (id) {
                    attributes['id'] = id;
                }
                var data = JSON.stringify(this.serializeArray());

                var attributesComposite = this.find('.attributes-composite');
                if (attributesComposite.length) {
                    attributesComposite.find(":input").each(function(){
                        var attributeField = $(this);
                        var attributeVal = attributeField.val();
                        var attributeName = attributeField.prop('name');

                        if(attributeField.is('.checkbox') && !attributeField.is(':checked')) {
                            return true; // skip unchecked checkboxes
                        }

                        if(attributeVal !== ''){
                            if (attributeName.indexOf('[') > -1) {
                                var key = attributeName.substring(0, attributeName.indexOf('['));
                                if (typeof attributes[key] != 'undefined') {
                                    attributes[key] += ',' + attributeVal;
                                } else {
                                    attributes[key] = attributeVal;
                                }
                            } else {
                                if(attributeField.is('.checkbox')) {
                                    attributes[attributeField.prop('name')] = attributeField.is(':checked') ? 1 : 0;
                                } else {
                                    attributes[attributeField.prop('name')] = attributeVal;
                                }
                            }
                        }
                    });
                }

                return {
                    'shortcodeType' : this.find(':input[name=ShortcodeType]').val().replaceAll(/\\/g, '_'),
                    'attributes' : attributes
                };
            },


            resetFields: function() {
                this._super();
                // trigger a change on the shortcode type field to reload all fields
                this.find(':input[name=ShortcodeType]').val('');
                this.find('.attributes-composite').hide();
                this.find('#id.field').hide();
            },
            /**
             * Updates the state of the dialog inputs to match the editor selection.
             * If selection does not contain a shortcode, resets the fields.
             */
            updateFromEditor: function() {
                var shortcode = this.getCurrentShortcode().trim();
                this.reloadForm('shortcode', shortcode)
            },
            getCurrentShortcode: function() {
                var selection = $(this.getSelection()), selectionText = selection.text();
                if (selection.attr('title') !== undefined) {
                    return '[' + selection.attr('title') + ']';
                }
                return selectionText;
            }
        });
    });
})(jQuery);
