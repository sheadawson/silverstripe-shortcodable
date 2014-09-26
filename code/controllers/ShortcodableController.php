<?php
/**
 * ShortcodableController
 *
 * @package Shortcodable
 * @author shea@livesource.co.nz
 **/
class ShortcodableController extends Controller{

	#ShortcodableController/$ShortcodeType

	private static $allowed_actions = array(
		'ShortcodeForm'
	);

	protected $shortcodableclass;
	protected $isnew = true;
	protected $shortcodedata;

	function init(){
		parent::init();
		// figure out class of shortcodable
		if($data = $this->getShortcodeData()){
			$this->isnew = false;
			$this->shortcodableclass = $data['name'];
		}else{
			$this->shortcodableclass = $this->request->requestVar('ShortcodeType');	
		}

	}

	function Link(){
		//TODO: build link with embedded shortcodable type
		//edit/$ShortcodeType/$ID
		return parent::Link();
	}

	/**
	 * create a list of shortcodable classes for the ShortcodeType dropdown
	 * @return array shortcodables
	 */
	protected function getShortcodablesList(){
		$classList = ClassInfo::implementorsOf('Shortcodable');
		$classes = array();
		foreach ($classList as $class) {
			$classes[$class] = singleton($class)->singular_name();
		}
		return $classes;
	}

	protected function getShortcodeData(){
		if($this->shortcodedata){
			return $this->shortcodedata;
		}
		$data = false;
		if($shortcode = $this->request->requestVar('Shortcode')){
			//remove BOM inside string on cursor position...
			$shortcode = str_replace("\xEF\xBB\xBF", '', $shortcode);
			$data = singleton('ShortcodableParser')->the_shortcodes(array(), $shortcode);
			if(isset($data[0])){
				$this->shortcodedata = $data[0];
				return $this->shortcodedata;
			}
		}
	}

	/**
	 * Provides a GUI for the insert/edit shortcode popup 
	 * @return Form
	 **/
	public function ShortcodeForm(){
		if(!Permission::check('CMS_ACCESS_CMSMain')) return;

		$classes = $this->getShortcodablesList();
		$classname = $this->shortcodableclass;

		if($this->isnew){
			$headingText = _t('Shortcodable.INSERTSHORTCODE', 'Insert Shortcode');
		}else{
			$headingText = _t('Shortcodable.EDITSHORTCODE', 'Edit Shortcode');
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
				->addExtraClass('shortcode-type')
			
		));

		// attribute and object id fields
		if($classname){
			if (class_exists($classname)) {
				$class = singleton($classname);
				if (is_subclass_of($class, 'DataObject')) {
					if(singleton($classname)->hasMethod('get_shortcodable_records')){
						$dataObjectSource = $classname::get_shortcodable_records();
					}else{
						$dataObjectSource = $classname::get()->map()->toArray();	
					}
					$fields->push(
						DropdownField::create('id', $class->singular_name(), $dataObjectSource)
							->setHasEmptyDefault(true)
					);
				}
				if($attrFields = $classname::shortcode_attribute_fields()){
					$fields->push(CompositeField::create($attrFields)->addExtraClass('attributes-composite'));
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
		$form = Form::create($this, "ShortcodeForm", $fields, $actions)
			->loadDataFrom($this)
			->addExtraClass('htmleditorfield-form htmleditorfield-shortcodable cms-dialog-content');
		
		if($data = $this->getShortcodeData()){
			$form->loadDataFrom($data['atts']);
		}
		
		$this->extend('updateShortcodeForm', $form);

		return $form;
	}
}
