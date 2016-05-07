# SilverStripe Shortcodable 2.0

![Screenshot](https://raw.github.com/sheadawson/silverstripe-shortcodable/master/images/screenshot.png)

Provides a GUI for CMS users to insert Shortcodes into the HTMLEditorField + an API for developers to define Shortcodable DataObjects and Views. This allows CMS users to easily embed and customise DataObjects and templated HTML snippets anywhere amongst their page content. Shortcodes can optionally be represented in the WYSIWYG with a custom placeholder image.

## Requirements
* SilverStripe 3.1 +

## Installation
Install via composer, run dev/build
```
composer require sheadawson/silverstripe-shortcodable
```

## Configuration
See [this gist](https://gist.github.com/sheadawson/12c5e5a2b42272bd90f703941450d677) for a well documented example of a Shortcodable ImageGallery to get you started.

## CMS Usage
Once installed a new icon will appear in the CMS HTMLEditor toolbar. It looks like this:
![icon](https://raw.github.com/sheadawson/silverstripe-shortcodable/master/images/shortcodable.png)

Clicking the toolbar will open a popup that allows you to insert a shortcode into the editor.

Highlighting an existing shortcode tag in the editor before clicking the shortcode icon will open the popup to allow editing of the selected shortcode tag.

Double clicking a shortcode placeholder in the editor will also open the popup to allow editing of the shortcode.

## Upgrading from 1.x
Shortcodable 2.0 has an improved method for applying Shortcodable to DataObjects. We no longer use an interface, as this didn't allow for Shortcodable to be applied to core classes such as File, Member, Page etc without changing core code. Instead, Shortcodable is applied to your Objects via yml config. Some methods have also changed from statics to normal methods. See updated examples below.

