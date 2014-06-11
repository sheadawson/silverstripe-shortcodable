# SilverStripe Shortcodable
Provides a GUI for CMS users to insert Shortcodes into the HTMLEditorField + an API for developers to define Shortcodable DataObjects and Views. This allows CMS users to easily embed and customise DataObjects and templated HTML snippets anywhere amongst their page content. 

## Requirements
* SilverStripe 3.1 +

## Installation
Install via composer, run dev/build
```
composer require sheadawson/silverstripe-shortcodable
```

## CMS Usage

Once installed a new icon will appear in the CMS HTMLEditor toolbar. It looks like this: 
![icon](https://raw.github.com/sheadawson/silverstripe-shortcodable/master/images/shortcodable.png)

Clicking the toolbar icon opens a popup that looks like this:
![Screenshot](https://raw.github.com/sheadawson/silverstripe-shortcodable/master/images/screenshot.png)

## API Usage

Implement the Shortcodable interface on the object you would like to make Shortcodable. This automatically registers the object with SilverStripe's shortcode parser and the Shortcodable module. In this example we'll create a shortcodable Image Gallery.

```php
class ImageGallery extends DataObject implements Shortcodable {
```

Implementors of Shortcodable require 2 static methods, the first being shortcode_attribute_fields(); For the image gallery example, we'll return a FieldList that allows the user to select a gallery "Style" from a dropdown list. The names and values of these fields will become the shortcode's attributes/values.

```php
/**
 * returns a list of fields for editing the shortcode's attributes
 * @return Fieldlist
 **/
public static function shortcode_attribute_fields(){
	return FieldList::create(
		DropdownField::create(
			'Style', 
			'Gallery Style', 
			array('Carousel' => 'Carousel', 'Lightbox' => 'Lightbox')
		)
	);
}
```

The second method required is parse_shortcode. This method is responsible for transforming and rendering the shortcode in the frontend. Because ImageGallery extends DataObject, the id attribute field is automatically added to the shortcode_attribute_fields. The following example code checks if the shortcode's "id" argument has been set and is valid, collects relevant data from the shortcode's other attributes and renders the ImageGallery with the appropriate template. 

```php
/**
 * Parse the shortcode and render as a string, probably with a template
 * @param array $arguments the list of attributes of the shortcode
 * @param string $content the shortcode content
 * @param ShortcodeParser $parser the ShortcodeParser instance
 * @param string $shortcode the raw shortcode being parsed
 * @return String
 **/
public static function parse_shortcode($arguments, $content, $parser, $shortcode){
	// check the gallery exists
	if(isset($arguments['id']) && $gallery = ImageGallery::get()->byID($arguments['id'])){
		// collect custom attributes
		$data = array();
		if(isset($arguments['Style'])){
			$data['Style'] = $arguments['Style'];
		}
		// render with template
		return $gallery->customise($data)->renderWith('ImageGallery');
	}
}
```

Create the ImageGallery.ss template then that's it, done!

