<?php
/**
 * ShortcodableController
 *
 * @package Shortcodable
 * @author shea@livesource.co.nz
 **/
class ShortcodableController extends Controller{

	private static $allowed_actions = array(
		'ShortcodeForm' => 'ADMIN',
		'index' => 'ADMIN',
		'handleEdit' => 'ADMIN'
	);

	private static $url_handlers = array(
		'edit/$ShortcodeType!/$Action//$ID/$OtherID' => 'handleEdit'
	);

	protected $shortcodableclass;
	protected $isnew = true;
	protected $shortcodedata;

	/**
	 * Get the shortcodable class by whatever means possible.
	 * Determine if this is a new shortcode, or editing an existing one.
	 */
	function init(){
		parent::init();
		if($data = $this->getShortcodeData()){
			$this->isnew = false;
			$this->shortcodableclass = $data['name'];
		}elseif($type = $this->request->requestVar('ShortcodeType')){
			$this->shortcodableclass = $type;	
		}else{
			$this->shortcodableclass = $this->request->param('ShortcodeType');
		}
	}

	/**
	 * Point to edit link, if shortcodable class exists.
	 */
	public function Link(){
		if($this->shortcodableclass){
			return Controller::join_links(
				parent::Link(),
				'edit',
				$this->shortcodableclass
			);
		}
		return parent::Link();
	}

	public function handleEdit(SS_HTTPRequest $request){
		$this->shortcodableclass = $request->param('ShortcodeType');
		return $this->handleAction($request, $action = $request->param('Action'));
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

	/**
	 * Get the shortcode data from the request.
	 * @return array shortcodedata
	 */
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
		$classes = $this->getShortcodablesList();
		$classname = $this->shortcodableclass;

		$headingText = $this->isnew ?
			 _t('Shortcodable.INSERTSHORTCODE', 'Insert Shortcode') :
			 sprintf(
			 	_t('Shortcodable.EDITSHORTCODE', 'Edit %s Shortcode'),
			 	singleton($classname)->singular_name()
			 );

		// essential fields
		$fields = FieldList::create(array(
			$setupfields = CompositeField::create(
				LiteralField::create(
					'Heading', 
					sprintf(
						'<h3 class="htmleditorfield-shortcodeform-heading insert">%s</h3>',
						$headingText
					)
				)
			)->addExtraClass('CompositeField composite cms-content-header nolabel'),
			LiteralField::create('shortcodablefields', '<div class="ss-shortcodable content">')
		));

		if($this->isnew){
			$setupfields->push(DropdownField::create('ShortcodeType', 'ShortcodeType', $classes, $classname)
				->setHasEmptyDefault(true)
				->addExtraClass('shortcode-type'));
		}

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
					$fields->push(
						CompositeField::create($attrFields)
							->addExtraClass('attributes-composite')
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
