<?php
/**
 * ShortcodableController.
 *
 * @author shea@livesource.co.nz
 **/
class ShortcodableController extends LeftAndMain
{
    private static $url_segment = 'shortcodes';
    private static $menu_title = 'Shortcodes';
    private static $required_permission_codes = 'CMS_ACCESS_AssetAdmin';

    /**
     * @var array
     */
    private static $allowed_actions = array(
        'ShortcodeForm' => 'CMS_ACCESS_AssetAdmin',
        'handleEdit' => 'CMS_ACCESS_AssetAdmin',
        'shortcodePlaceHolder' => 'CMS_ACCESS_AssetAdmin'
    );

    /**
     * @var array
     */
    private static $url_handlers = array(
        'edit/$ShortcodeType!/$Action//$ID/$OtherID' => 'handleEdit'
    );

    /**
     * @var string
     */
    protected $shortcodableclass;

    /**
     * @var boolean
     */
    protected $isnew = true;

    /**
     * @var array
     */
    protected $shortcodedata;

    /**
     * Get the shortcodable class by whatever means possible.
     * Determine if this is a new shortcode, or editing an existing one.
     */
    public function init()
    {
        parent::init();
        if ($data = $this->getShortcodeData()) {
            $this->isnew = false;
            $this->shortcodableclass = $data['name'];
        } elseif ($type = $this->request->requestVar('ShortcodeType')) {
            $this->shortcodableclass = $type;
        } else {
            $this->shortcodableclass = $this->request->param('ShortcodeType');
        }
    }

    /**
     * Point to edit link, if shortcodable class exists.
     */
    public function Link($action = null)
    {
        if ($this->shortcodableclass) {
            return Controller::join_links(
                $this->config()->url_base,
                $this->config()->url_segment,
                'edit',
                $this->shortcodableclass
            );
        }

        return Controller::join_links($this->config()->url_base, $this->config()->url_segment, $action);
    }

    /**
     * handleEdit
     */
    public function handleEdit(SS_HTTPRequest $request)
    {
        $this->shortcodableclass = $request->param('ShortcodeType');
        return $this->handleAction($request, $action = $request->param('Action'));
    }

    /**
     * Get the shortcode data from the request.
     * @return array shortcodedata
     */
    protected function getShortcodeData()
    {
        if ($this->shortcodedata) {
            return $this->shortcodedata;
        }
        $data = false;
        if ($shortcode = $this->request->requestVar('Shortcode')) {
            //remove BOM inside string on cursor position...
            $shortcode = str_replace("\xEF\xBB\xBF", '', $shortcode);
            $data = singleton('ShortcodableParser')->the_shortcodes(array(), $shortcode);
            if (isset($data[0])) {
                $this->shortcodedata = $data[0];
                return $this->shortcodedata;
            }
        }
    }

    /**
     * Provides a GUI for the insert/edit shortcode popup.
     *
     * @return Form
     **/
    public function ShortcodeForm()
    {
        Config::inst()->update('SSViewer', 'theme_enabled', false);
        $classes = Shortcodable::get_shortcodable_classes_fordropdown();
        $classname = $this->shortcodableclass;

        if ($this->isnew) {
            $headingText = _t('Shortcodable.EDITSHORTCODE', 'Edit Shortcode');
        } else {
            $headingText = sprintf(
                _t('Shortcodable.EDITSHORTCODE', 'Edit %s Shortcode'),
                singleton($this->shortcodableclass)->singular_name()
            );
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
            DropdownField::create('ShortcodeType', _t('Shortcodable.SHORTCODETYPE', 'Shortcode type'), $classes, $classname)
                ->setHasEmptyDefault(true)
                ->addExtraClass('shortcode-type')
        ));

        // attribute and object id fields
        if ($classname && class_exists($classname)) {
            $class = singleton($classname);
            if (is_subclass_of($class, 'DataObject')) {
                if (singleton($classname)->hasMethod('getShortcodableRecords')) {
                    $dataObjectSource = singleton($classname)->getShortcodableRecords();
                } else {
                    $dataObjectSource = $classname::get()->map()->toArray();
                }
                $fields->push(
                    DropdownField::create('id', $class->singular_name(), $dataObjectSource)
                        ->setHasEmptyDefault(true)
                );
            }
            if (singleton($classname)->hasMethod('getShortcodeFields')) {
                if ($attrFields = singleton($classname)->getShortcodeFields()) {
                    $fields->push(
                        CompositeField::create($attrFields)
                            ->addExtraClass('attributes-composite')
                            ->setName('AttributesCompositeField')
                    );
                }
            }
        }

        // actions
        $actions = FieldList::create(array(
            FormAction::create('insert', _t('Shortcodable.BUTTONINSERTSHORTCODE', 'Insert shortcode'))
                ->addExtraClass('ss-ui-action-constructive')
                ->setAttribute('data-icon', 'accept')
                ->setUseButtonTag(true)
        ));

        // form
        $form = Form::create($this, 'ShortcodeForm', $fields, $actions)
            ->loadDataFrom($this)
            ->addExtraClass('htmleditorfield-form htmleditorfield-shortcodable cms-dialog-content');

        $this->extend('updateShortcodeForm', $form);

        $fields->push(LiteralField::create('shortcodablefieldsend', '</div>'));

        if ($data = $this->getShortcodeData()) {
            $form->loadDataFrom($data['atts']);

            // special treatment for setting value of UploadFields
            foreach ($form->Fields()->dataFields() as $field) {
                if (is_a($field, 'UploadField') && isset($data['atts'][$field->getName()])) {
                    $field->setValue(array('Files' => explode(',', $data['atts'][$field->getName()])));
                }
            }
        }

        return $form;
    }

    /**
     * Generates shortcode placeholder to display inside TinyMCE instead of the shortcode.
     *
     * @return void
     */
    public function shortcodePlaceHolder($request)
    {
        if (!Permission::check('CMS_ACCESS_CMSMain')) {
            return;
        }

        $classname = $request->param('ID');
        $id = $request->param('OtherID');

        if (!class_exists($classname)) {
            return;
        }

        if ($id) {
            $object = $classname::get()->byID($id);
        } else {
            $object = singleton($classname);
        }

        if ($object->hasMethod('getShortcodePlaceHolder')) {
            $attributes = null;
            if ($shortcode = $request->requestVar('Shortcode')) {
                $shortcode = str_replace("\xEF\xBB\xBF", '', $shortcode); //remove BOM inside string on cursor position...
                $shortcodeData = singleton('ShortcodableParser')->the_shortcodes(array(), $shortcode);
                if (isset($shortcodeData[0])) {
                    $attributes = $shortcodeData[0]['atts'];
                }
            }

            $link = $object->getShortcodePlaceholder($attributes);
            return $this->redirect($link);
        }
    }
}
