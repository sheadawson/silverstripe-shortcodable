# SilverStripe Shortcodable 4

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sheadawson/silverstripe-shortcodable/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sheadawson/silverstripe-shortcodable/?branch=master)

![Screenshot](https://raw.github.com/sheadawson/silverstripe-shortcodable/master/images/screenshot.png)

Provides a GUI for CMS users to insert Shortcodes into the HTMLEditorField + an API for developers to define Shortcodable DataObjects and Views. This allows CMS users to easily embed and customise DataObjects and templated HTML snippets anywhere amongst their page content. Shortcodes can optionally be represented in the WYSIWYG with a custom placeholder image.

## Requirements
* SilverStripe 4 +

See 3.x branch/releases for SilverStripe SS 3.5 compatibility
See 2.x branch/releases for SilverStripe SS 3.1 - 3.4 compatibility

## Installation
Install via composer, run dev/build
```
composer require sheadawson/silverstripe-shortcodable
```

## Configuration
See [this gist](https://gist.github.com/sheadawson/12c5e5a2b42272bd90f703941450d677) for a well documented example of a Shortcodable ImageGallery to get you started. This example is for a subclass of DataObject. If your shortcodable object doesn't need it's own database record, you can use the same example but use ViewableData as the parent class.

#### TinyMCE block elements
In SilverStripe 3 shortcodes tend to get wrapped in paragraph elements, which is a problem if your shortcode will be rendered as a block element. To get around this you can flag shortcodable classes as block elements with a config setting. If you don't want to replace the paragraph tag with a div this can be disabled as well.

```yml
MyShortcodableClass:
  shortcodable_is_block: true
  disable_wrapper: true
```

## CMS Usage
Once installed a new icon will appear in the CMS HTMLEditor toolbar. It looks like this:
![icon](https://raw.github.com/sheadawson/silverstripe-shortcodable/master/images/shortcodable.png)

Clicking the toolbar will open a popup that allows you to insert a shortcode into the editor.

Highlighting an existing shortcode tag in the editor before clicking the shortcode icon will open the popup to allow editing of the selected shortcode tag.

Double clicking a shortcode placeholder in the editor will also open the popup to allow editing of the shortcode.

## Upgrading from 1.x
Shortcodable 2.0 has an improved method for applying Shortcodable to DataObjects. We no longer use an interface, as this didn't allow for Shortcodable to be applied to core classes such as File, Member, Page etc without changing core code. Instead, Shortcodable is applied to your Objects via yml config. Some methods have also changed from statics to normal methods. See updated examples below.
