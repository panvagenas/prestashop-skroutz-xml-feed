<?php

/**
 * skroutzxmlfeed
 * skroutz_xml_feed.php
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 6/11/2014
 * Time: 12:16 μμ
 * Copyright: 2014 Panagiotis Vagenas
 */
if ( ! defined( '_PS_VERSION_' ) ) {
	exit;
}

class Skroutz_Xml_Feed extends Module {
	/**
	 * @var string Name of this plugin
	 */
	public $name = 'Skroutz XML Feed';
	/**
	 * @var string Description
	 */
	public $description = 'Generates XML feed for skroutz.gr';
	/**
	 * @var string
	 */
	public $tab = 'others';
	/**
	 * @var string
	 */
	public $version = '0.1';
	/**
	 * @var string
	 */
	public $author = 'Panagiotis Vagenas <pan.vagenas@gmail.com>';
	/**
	 * @var int
	 */
	public $need_instance = 0;
	/**
	 * @var array
	 */
	public $ps_versions_compliancy = array( 'min' => '1.5', 'max' => '1.6' );
	/**
	 * @var array
	 */
	public $dependencies = array();

	/**
	 *
	 */
	public function __construct() {
		$this->name = 'Skroutz XML Feed';
		$this->tab = 'Generates XML feed for skroutz.gr';
		$this->version = '0.1';
		$this->author = 'Panagiotis Vagenas';
		$this->need_instance = 0;
		$this->ps_versions_compliancy = array('min' => '1.5');
		$this->dependencies = array();

		parent::__construct();

		$this->displayName = $this->l( $this->name );
		$this->description = $this->l( $this->description );

		$this->confirmUninstall = $this->l( 'Are you sure you want to uninstall?' );

		if ( ! Configuration::get( 'MYMODULE_NAME' ) ) {
			$this->warning = $this->l( 'No name provided' );
		}
	}

	public function getContent() {
		$output = null;

		if ( Tools::isSubmit( 'submit' . $this->name ) ) {
			$my_module_name = strval( Tools::getValue( 'MYMODULE_NAME' ) );
			if ( ! $my_module_name || empty( $my_module_name ) || ! Validate::isGenericName( $my_module_name ) ) {
				$output .= $this->displayError( $this->l( 'Invalid Configuration value' ) );
			} else {
				Configuration::updateValue( 'MYMODULE_NAME', $my_module_name );
				$output .= $this->displayConfirmation( $this->l( 'Settings updated' ) );
			}
		}

		return $output . $this->displayForm();
	}

	public function displayForm()
	{
		// Get default Language
		$default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

		// Init Fields form array
		$fields_form[0]['form'] = array(
			'legend' => array(
				'title' => $this->l('Settings'),
			),
			'input' => array(
				array(
					'type' => 'text',
					'label' => $this->l('Configuration value'),
					'name' => 'MYMODULE_NAME',
					'size' => 20,
					'required' => true
				)
			),
			'submit' => array(
				'title' => $this->l('Save'),
				'class' => 'button'
			)
		);

		$helper = new HelperForm();

		// Module, t    oken and currentIndex
		$helper->module = $this;
		$helper->name_controller = $this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

		// Language
		$helper->default_form_language = $default_lang;
		$helper->allow_employee_form_lang = $default_lang;

		// Title and toolbar
		$helper->title = $this->displayName;
		$helper->show_toolbar = true;        // false -> remove toolbar
		$helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
		$helper->submit_action = 'submit'.$this->name;
		$helper->toolbar_btn = array(
			'save' =>
				array(
					'desc' => $this->l('Save'),
					'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
					          '&token='.Tools::getAdminTokenLite('AdminModules'),
				),
			'back' => array(
				'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
				'desc' => $this->l('Back to list')
			)
		);

		// Load current value
		$helper->fields_value['MYMODULE_NAME'] = Configuration::get('MYMODULE_NAME');

		return $helper->generateForm($fields_form);
	}

	/**
	 * @return bool
	 *
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since ${VERSION}
	 */
	public function install() {
		return (parent::install()) && Configuration::updateValue('MYMODULE_NAME', 'my friend');
	}

	/**
	 * @return bool
	 *
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since ${VERSION}
	 */
	public function uninstall() {
		if (!parent::uninstall())
			Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'mymodule`');
		parent::uninstall();
	}
}