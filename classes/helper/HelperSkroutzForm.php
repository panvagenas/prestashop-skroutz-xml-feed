<?php
/**
 * skroutzxmlfeed
 * ${FILE_NAME}
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 6/11/2014
 * Time: 4:02 μμ
 * Copyright: 2014 Panagiotis Vagenas
 */
if ( ! defined( '_PS_VERSION_' ) ) {
	exit;
}

class HelperSkroutzForm extends HelperFormCore {
	public $fields_form = array();

	public function __construct() {
		parent::__construct();
	}

	public function init( SkroutzXmlFeed &$plugin ) {
		$default_lang = (int) Configuration::get( 'PS_LANG_DEFAULT' );
		$optionValues = HelperSkroutzOptions::Instance()->getOptionsArray();

		$availInStockOptions    = array();
		$availOutOfStockOptions = array();
		$availBackOrderOptions  = array();

		$availOutOfStockOptions[] = $availBackOrderOptions[] = array(
			'id_option' => 0,
			'name'      => 'Do not Include'
		);

		foreach ( HelperSkroutzOptions::Instance()->availOptions as $key => $availability ) {
			$availOutOfStockOptions[] = $availBackOrderOptions[] = $availInStockOptions[] = array(
				'id_option' => $key,
				'name'      => $availability
			);
		}


		$this->fields_form = array(
			'legend' => array(
				'title' => $this->l( 'Edit carrier' ),
				// This is the name of the fieldset, which can contain many option fields
				'image' => '../img/admin/icon_to_display.gif'
				// The icon must, if there is one, must be of the size 16*16
			),
			'input'  => array(
				array(
					'type'     => 'text',
					'label'    => $this->l( 'XML file path' ),
					'name'     => 'xml_location',
					'class'    => 'lg',
					'required' => true,
					'desc'     => $this->l( 'File path relative to your Prestashop install folder. eg "/" is the root Prestashop installation.' )
				),
				array(
					'type'     => 'text',
					'label'    => $this->l( 'XML file name' ),
					'name'     => 'xml_fileName',
					'class'    => 'lg',
					'required' => true,
					'desc'     => $this->l( 'File name. eg "skroutz.xml"' )
				),
				array(
					'type'     => 'select',
					'label'    => $this->l( 'Product availability when on stock' ),
					'desc'     => $this->l( 'This the product availability when this is in stock. It should reflect store availability' ),
					'name'     => 'avail_inStock',
					'required' => true,
					'options'  => array(
						'query' => $availInStockOptions,
						'id'    => 'id_option',
						'name'  => 'name'
					)
				),
				array(
					'type'     => 'select',
					'label'    => $this->l( 'Product availability when out of stock' ),
					'desc'     => $this->l( 'This the product availability when this is out stock. It should reflect store availability so default is "Do not include"' ),
					'name'     => 'avail_outOfStock',
					'required' => true,
					'options'  => array(
						'query' => $availOutOfStockOptions,
						'id'    => 'id_option',
						'name'  => 'name'
					)
				),
				array(
					'type'     => 'select',
					'label'    => $this->l( 'Product availability when out of stock and back-orders are allowed' ),
					'desc'     => $this->l( 'This the product availability when this is out of stock and back-orders are allowed. Default is "Do not include"' ),
					'name'     => 'avail_backorders',
					'required' => true,
					'options'  => array(
						'query' => $availInStockOptions,
						'id'    => 'id_option',
						'name'  => 'name'
					)
				),
				array(
					'type'   => 'checkbox',
					'label'  => $this->l( 'Fashion store' ),
					'desc'   => $this->l( 'Check this if your store contains fashion items, eg shoes, clothes etc.' ),
					'name'   => 'is_fashion_store',
					'values' => array(
						'query' => array(
							'id'   => 0,
							'name' => $this->l( 'Is this a fashion store' )
						),
						'id'    => 'id',
						'name'  => 'name'
					),
				),
				array(
					'type'   => 'checkbox',
					'label'  => $this->l( 'Bookstore' ),
					'desc'   => $this->l( 'Check this if you are selling books' ),
					'name'   => 'is_book_store',
					'values' => array(
						'query' => array(
							'id'   => 0,
							'name' => $this->l( 'Is this a Bookstore' )
						),
						'id'    => 'id',
						'name'  => 'name'
					),
				),
			),
			'submit' => array(
				'title' => $this->l( '   Save   ' ),
				'class' => 'button'
			)
		);

		// Module, t    oken and currentIndex
		$this->module          = $plugin;
		$this->name_controller = $plugin->name;
		$this->token           = Tools::getAdminTokenLite( 'AdminModules' );
		$this->currentIndex    = AdminController::$currentIndex . '&configure=' . $plugin->name;

		// Language
		$this->default_form_language    = $default_lang;
		$this->allow_employee_form_lang = $default_lang;

		// Title and toolbar
		$this->title          = $plugin->displayName;
		$this->show_toolbar   = true;        // false -> remove toolbar
		$this->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
		$this->submit_action  = 'submit' . $plugin->name;
		$this->toolbar_btn    = array(
			'save' =>
				array(
					'desc' => $plugin->l( 'Save' ),
					'href' => AdminController::$currentIndex . '&configure=' . $plugin->name . '&save' . $plugin->name .
					          '&token=' . Tools::getAdminTokenLite( 'AdminModules' ),
				),
			'back' => array(
				'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite( 'AdminModules' ),
				'desc' => $plugin->l( 'Back to list' )
			)
		);

		// Load current value
		$this->fields_value['MYMODULE_NAME'] = Configuration::get( 'MYMODULE_NAME' );
	}
}