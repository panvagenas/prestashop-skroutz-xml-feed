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

include_once dirname(__FILE__) . '/classes/helper/HelperSkroutzLoader.php';

class SkroutzXmlFeed extends Module {
	/**
	 * @var string Name of this plugin
	 */
	public $name = 'skroutzxmlfeed';
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
	public $ps_versions_compliancy = array( 'min' => '1.5' );
	/**
	 * @var array
	 */
	public $dependencies = array();
	/**
	 * @var string
	 */
	public $displayName = 'Skroutz XML Feed';

	/**
	 * @var HelperSkroutzLoader
	 */
	protected $loader;

	/**
	 *
	 */
	public function __construct() {
		parent::__construct();

		$this->displayName = $this->l( $this->displayName );
		$this->description = $this->l( $this->description );

		$this->confirmUninstall = $this->l( 'Are you sure you want to uninstall?' );

		$this->loader = new HelperSkroutzLoader();
	}

	/**
	 * Module options page
	 *
	 * @return string
	 *
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since ${VERSION}
	 */
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
		if (parent::install() == false)
			return false;
		return true;
	}

	/**
	 * @return bool
	 *
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since ${VERSION}
	 */
	public function uninstall() {
		if (!parent::uninstall() ||
		    !Configuration::deleteByName('MYMODULE_NAME'))
			return false;
		return true;
	}
}