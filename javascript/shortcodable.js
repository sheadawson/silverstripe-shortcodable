(function($) {
    $.entwine('ss', function($) {

        // handle change on shortcode-type field
        $('select.shortcode-type').entwine({
            onchange: function(){
                this.parents('form:first').reloadForm('type', this.val());
            }
        });

        // add shortcode controller url to cms-editor-dialogs
        $('#cms-editor-dialogs').entwine({
            onmatch: function(){
                this.attr('data-url-shortcodeform', 'ShortcodableController/ShortcodeForm/forTemplate');
            }
        });

        // open shortcode dialog
        $('textarea.htmleditor').entwine({
            openShortcodeDialog: function() {
                this.openDialog('shortcode');
            },
            getPlaceholderClasses: function() {
                var classes = $(this).data('placeholderclasses');
                if (classes) {
                    return classes.split(',');
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

        $('form.htmleditorfield-shortcodable').entwine({
            // load the shortcode form into the dialog
            reloadForm: function(from, data) {
                var postdata = {};
                if(from == 'type'){
                    postdata.ShortcodeType = data;
                }else if(from =='shortcode'){
                    postdata.Shortcode = data;
                }

                this.addClass('loading');

                var url = $('#cms-editor-dialogs').attr('data-url-shortcodeform');

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
                        var newContent = shortcodable.replaceShortcodesWithPlaceholders(ed.getContent(), ed.getInstance());
                        console.log(newContent);
                        ed.setContent(newContent);
                    });
                }
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
                var shortcodeType = this.find(':input[name=ShortcodeType]').val();
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
                    'shortcodeType' : this.find(':input[name=ShortcodeType]').val(),
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
