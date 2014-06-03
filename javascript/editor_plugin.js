(function() {
	var each = tinymce.each;

	tinymce.create('tinymce.plugins.shortcodable', {
		getInfo : function() {
			return {
				longname : 'Shortcodable - Shortcode UI plugin for SilverStripe',
				author : 'Shea Dawson',
				authorurl : 'http://www.livesource.co.nz/',
				infourl : 'http://www.livesource.co.nz/',
				version : "1.0"
			};
		},

		init : function(ed, url) {
			ed.addButton('shortcodable', {title : 'Insert Shortcode', cmd : 'shortcodable', 'class' : 'mce_shortcode'}); 

			ed.addCommand('shortcodable', function(ed) {
				jQuery('#' + this.id).entwine('ss').openShortcodeDialog();
			});
		}
	});

	// Adds the plugin class to the list of available TinyMCE plugins
	tinymce.PluginManager.add("shortcodable", tinymce.plugins.shortcodable);
})();
