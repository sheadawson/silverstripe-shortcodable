# SilverStripe Shortcodable 2.0
Provides a GUI for CMS users to insert Shortcodes into the HTMLEditorField + an API for developers to define Shortcodable DataObjects and Views. This allows CMS users to easily embed and customise DataObjects and templated HTML snippets anywhere amongst their page content.

# Upgrading from 1.x
Shortcodable 2.0 has an improved method for applying Shortcodable to DataObjects. We no longer use an interface, as this didn't allow for Shortcodable to be applied to core classes such as File, Member, Page etc without changing core code. Instead, Shortcodable is applied to your Objects via yml config. Some methods have also changed from statics to normal methods. See updated examples below.

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

Note that currently there is a small issue when editing shortcodes. If you want to edit an existing shortcode, just make sure you select the whole thing before clicking the ![icon](https://raw.github.com/sheadawson/silverstripe-shortcodable/master/images/shortcodable.png) icon.
## API Usage

Configure the Classes you'd like to make Shortcodable via yml config. This automatically registers the Class with SilverStripe's shortcode parser and the Shortcodable module. In this example we'll create a shortcodable Image Gallery.

### Register Shortcodable Classes

```
Shortcodable:
  shortcodable_classes:
    - ImageGallery
```

### Your Shortcodable class

Classes registered with Shortcodable must have the parseShortcode public method defined (either directly on the class or via an Extension). This method is responsible for transforming and rendering the shortcode in the frontend.

Because ImageGallery extends DataObject, the id attribute field is automatically added to the shortcode form. The following example code checks if the shortcode's "id" argument has been set and is valid, collects relevant data from the shortcode's other attributes and renders the ImageGallery with the appropriate template.

```php
class ImageGallery extends DataObject
{
    /**
     * Parse the shortcode and render as a string, probably with a template
     *
     * @param array $arguments the list of attributes of the shortcode
     * @param string $content the shortcode content
     * @param ShortcodeParser $parser the ShortcodeParser instance
     * @param string $shortcode the raw shortcode being parsed
     *
     * @return string
     **/
    public static function parseShortcode($arguments, $content, $parser, $shortcode)
    {
        // check the gallery exists
        if(isset($arguments['id']) && $gallery = ImageGallery::get()->byID($arguments['id'])) {
            // collect custom attributes
            $data = array();
    		if(isset($arguments['Style'])) {
    			$data['Style'] = $arguments['Style'];
    		}

    		// render with template
    		return $gallery->customise($data)->renderWith('ImageGallery');
    	}
    }
}
```

You can also add the getShortcodeFields method to your class. For the image gallery example, we'll return a FieldList that allows the user to select a gallery "Style" from a dropdown list. The names and values of these fields will become the shortcode's attributes/values.

```php
class ImageGallery extends DataObject
{
    ...

    /**
     * returns a list of fields for editing the shortcode's attributes
     *
     * @return FieldList
     **/
    public function getShortcodeFields()
    {
        return FieldList::create(
            DropdownField::create(
                'Style',
                'Gallery Style',
                array('Carousel' => 'Carousel', 'Lightbox' => 'Lightbox')
            )
        );
    }
```

Create the ImageGallery.ss template then that's it, done!

### Restricting data object selection

If you would like to customise or filter the list of available shortcodable DataObject records available in the dropdown, you can supply a custom getShortcodableRecords method on your shortcodable DataObject. The method should return an associative array suitable for the DropdownField. For example:

```php
/**
 * @return array
 */
public function getShortcodableRecords() {
	return ImageGallery::get()->filter('SomeField', 'SomeValue')->map()->toArray();
}
```
