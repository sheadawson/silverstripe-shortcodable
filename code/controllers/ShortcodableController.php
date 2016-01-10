<?php
/**
 * ShortcodableController.
 *
 * @author shea@livesource.co.nz
 **/
class ShortcodableController extends Controller
{
    /**
     * @var array
     */
    private static $allowed_actions = array(
        'ShortcodeForm',
    );

    /**
     * Provides a GUI for the insert/edit shortcode popup.
     *
     * @return Form
     **/
    public function ShortcodeForm()
    {
        if (!Permission::check('CMS_ACCESS_CMSMain')) {
            return;
        }

        Config::inst()->update('SSViewer', 'theme_enabled', false);

        // create a list of shortcodable classes for the ShortcodeType dropdown
        $classList = ClassInfo::implementorsOf('Shortcodable');
        $classes = array();
        foreach ($classList as $class) {
            $classes[$class] = singleton($class)->singular_name();
        }

        // load from the currently selected ShortcodeType or Shortcode data
        $classname = false;
        $shortcodeData = false;
        if ($shortcode = $this->request->requestVar('Shortcode')) {
            $shortcode = str_replace("\xEF\xBB\xBF", '', $shortcode); //remove BOM inside string on cursor position...
            $shortcodeData = singleton('ShortcodableParser')->the_shortcodes(array(), $shortcode);
            if (isset($shortcodeData[0])) {
                $shortcodeData = $shortcodeData[0];
                $classname = $shortcodeData['name'];
            }
        } else {
            $classname = $this->request->requestVar('ShortcodeType');
        }

        if ($shortcodeData) {
            $headingText = _t('Shortcodable.EDITSHORTCODE', 'Edit Shortcode');
        } else {
            $headingText = _t('Shortcodable.INSERTSHORTCODE', 'Insert Shortcode');
        }

        // essential fields
        $fields = FieldList::create(array(
            CompositeField::create(
                LiteralField::create(
                    'Heading',
                    sprintf('<h3 class="htmleditorfield-shortcodeform-heading insert">%s</h3>', $headingText)
                )
            )->addExtraClass('CompositeField composite cms-content-header nolabel'),
            LiteralField::create('shortcodablefields', '<div class="ss-shortcodable content">'),
            DropdownField::create('ShortcodeType', 'ShortcodeType', $classes, $classname)
                ->setHasEmptyDefault(true)
                ->addExtraClass('shortcode-type'),

        ));

        // attribute and object id fields
        if ($classname) {
            if (class_exists($classname)) {
                $class = singleton($classname);
                if (is_subclass_of($class, 'DataObject')) {
                    if (singleton($classname)->hasMethod('get_shortcodable_records')) {
                        $dataObjectSource = $classname::get_shortcodable_records();
                    } else {
                        $dataObjectSource = $classname::get()->map()->toArray();
                    }
                    $fields->push(
                        DropdownField::create('id', $class->singular_name(), $dataObjectSource)
                            ->setHasEmptyDefault(true)
                    );
                }
                if ($attrFields = $classname::shortcode_attribute_fields()) {
                    $fields->push(CompositeField::create($attrFields)->addExtraClass('attributes-composite'));
                }
            }
        }

        // actions
        $actions = FieldList::create(array(
            FormAction::create('insert', _t('Shortcodable.BUTTONINSERTSHORTCODE', 'Insert shortcode'))
                ->addExtraClass('ss-ui-action-constructive')
                ->setAttribute('data-icon', 'accept')
                ->setUseButtonTag(true),
        ));

        // form
        $form = Form::create($this, 'ShortcodeForm', $fields, $actions)
            ->loadDataFrom($this)
            ->addExtraClass('htmleditorfield-form htmleditorfield-shortcodable cms-dialog-content');

        if ($shortcodeData) {
            $form->loadDataFrom($shortcodeData['atts']);
        }

        $this->extend('updateShortcodeForm', $form);

        return $form;
    }
}
