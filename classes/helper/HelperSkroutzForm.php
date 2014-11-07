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
	public $fields_form;

	public function __construct() {
		parent::__construct();
	}

	public function init( SkroutzXmlFeed &$plugin ) {
		require_once dirname( __FILE__ ) . '/' . 'HelperSkroutzOptions.php';

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
			$availInStockOptions[]    = array(
				'id_option' => $key,
				'name'      => $availability
			);
			$availOutOfStockOptions[] = $availBackOrderOptions[] = array(
				'id_option' => $key + 1,
				'name'      => $availability
			);
		}

		$this->fields_form[0]['form'] = array(
			'legend' => array(
				'title' => $this->l( 'General Options' ),
				'image' => $plugin->getPathUri() . '/logo16x16.png'
			),
			'input'  => array(
				array(
					'type'     => 'text',
					'label'    => $this->l( 'XML file path' ),
					'name'     => 'xml_location',
					'class'    => 'lg',
					'required' => true,
					'hint'     => $this->l( 'File path relative to your PrestaShop install folder. eg "upload" is the PrestaShop upload dir.' ),
					'prefix'   => _PS_ROOT_DIR_ . '/'
				),
				array(
					'type'     => 'text',
					'label'    => $this->l( 'XML file name' ),
					'suffix'   => '.xml',
					'name'     => 'xml_fileName',
					'class'    => 'lg',
					'required' => true,
					'hint'     => $this->l( 'File name. eg "skroutz.xml"' )
				),
				array(
					'type'     => 'select',
					'label'    => $this->l( 'Product availability when on stock' ),
					'hint'     => $this->l( 'This the product availability when this is in stock. It should reflect store availability' ),
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
					'hint'     => $this->l( 'This the product availability when this is out stock. It should reflect store availability so default is "Do not include"' ),
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
					'hint'     => $this->l( 'This the product availability when this is out of stock and back-orders are allowed. Default is "Do not include"' ),
					'name'     => 'avail_backorders',
					'required' => true,
					'options'  => array(
						'query' => $availBackOrderOptions,
						'id'    => 'id_option',
						'name'  => 'name'
					)
				),
				array(
					'type'     => 'switch',
					'label'    => $this->l( 'Append Product SKU to Product Name' ),
					'hint'     => $this->l( 'If this option is enabled then the product SKU will be appended to Product Name' ),
					'name'     => 'map_name_append_sku',
					'required' => true,
					'class'    => 't',
					'is_bool'  => true,
					'values'   => array(                                 // $values contains the data itself.
						array(
							'id'    => 'map_name_append_sku',
							'value' => 1,
							'label' => $this->l( 'Yes' )
						),
						array(
							'id'    => 'map_name_do_not_append_sku',
							'value' => 0,
							'label' => $this->l( 'No' )
						)
					),
				),
				array(
					'type'     => 'switch',
					'label'    => $this->l( 'Fashion store' ),
					'hint'     => $this->l( 'Your store contains fashion items, eg shoes, clothes etc.' ),
					'name'     => 'is_fashion_store',
					'required' => true,
					'class'    => 't',
					'is_bool'  => true,
					'values'   => array(                                 // $values contains the data itself.
						array(
							'id'    => 'is_fashion_store',
							'value' => 1,
							'label' => $this->l( 'Yes' )
						),
						array(
							'id'    => 'is_not_fashion_store',
							'value' => 0,
							'label' => $this->l( 'No' )
						)
					),
				),
				array(
					'type'     => 'switch',
					'label'    => $this->l( 'Bookstore' ),
					'hint'     => $this->l( 'Enable this if you are selling books' ),
					'name'     => 'is_book_store',
					'required' => true,
					'is_bool'  => true,
					'values'   => array(                                 // $values contains the data itself.
						array(
							'id'    => 'is_book_store',
							'value' => 1,
							'label' => $this->l( 'Yes' )
						),
						array(
							'id'    => 'is_not_book_store',
							'value' => 0,
							'label' => $this->l( 'No' )
						)
					),
				),
			),
			'submit' => array(
				'title' => $this->l( '   Save   ' ),
				'class' => 'button pull-right'
			)
		);

		// Field mapping options

		$productIdOptions = array(
			array(
				'id_option' => 0,
				'name'      => 'Reference Code'
			),
			array(
				'id_option' => 1,
				'name'      => 'EAN-13 or JAN barcode'
			),
			array(
				'id_option' => 2,
				'name'      => 'UPC barcode'
			),
		);

		$productManufacturerOptions = array(
			array(
				'id_option' => 0,
				'name'      => 'Product Manufacturer'
			),
			array(
				'id_option' => 1,
				'name'      => 'Product Supplier'
			),
		);

		$productLinkOptions = array(
			array(
				'id_option' => 0,
				'name'      => 'Use Product Link'
			),
		);

		$productImageLinkOptions = array(
			array(
				'id_option' => 0,
				'name'      => 'Cover Image'
			),
			array(
				'id_option' => 1,
				'name'      => 'Random Image'
			),
		);

		$productCategoriesOptions = array(
			array(
				'id_option' => 0,
				'name'      => 'Categories'
			),
			array(
				'id_option' => 1,
				'name'      => 'Tags'
			),
		);

		$productPriceOptions = array(
			array(
				'id_option' => 0,
				'name'      => 'Retail price with tax'
			),
			array(
				'id_option' => 1,
				'name'      => 'Pre-tax retail price'
			),
			array(
				'id_option' => 2,
				'name'      => 'Pre-tax wholesale price'
			),
		);

		$productMPNOptions = array(
			array(
				'id_option' => 0,
				'name'      => 'Reference Code'
			),
			array(
				'id_option' => 1,
				'name'      => 'EAN-13 or JAN barcode'
			),
			array(
				'id_option' => 2,
				'name'      => 'UPC barcode'
			),
			array(
				'id_option' => 3,
				'name'      => 'Supplier Reference'
			),
		);

		$productISBNOptions = array(
			array(
				'id_option' => 0,
				'name'      => 'Reference Code'
			),
			array(
				'id_option' => 1,
				'name'      => 'EAN-13 or JAN barcode'
			),
			array(
				'id_option' => 2,
				'name'      => 'UPC barcode'
			),
			array(
				'id_option' => 3,
				'name'      => 'Supplier Reference'
			),
		);

		// Multiselect from attribute groups
		$productSizesOptions = array();
		$productColorOptions = array();

		$attributes = AttributeGroup::getAttributesGroups( $default_lang );

		foreach ( $attributes as $attribute ) {
			if ( $attribute['is_color_group'] ) {
				$productColorOptions[] = array(
					'id_option' => $attribute['id_attribute_group'],
					'name'      => $attribute['name'],
				);
			} else {
				$productSizesOptions[] = array(
					'id_option' => $attribute['id_attribute_group'],
					'name'      => $attribute['name'],
				);
			}
		}

		$this->fields_form[1]['form'] = array(
			'legend' => array(
				'title' => $this->l( 'Field Mapping Options' ),
				'image' => $plugin->getPathUri() . '/logo16x16.png'
			),
			'input'  => array(
				array(
					'type'     => 'select',
					'label'    => $this->l( 'Product ID' ),
					'hint'     => $this->l( 'Select the product reference group you are using in your store' ),
					'name'     => 'map_id',
					'required' => true,
					'options'  => array(
						'query' => $productIdOptions,
						'id'    => 'id_option',
						'name'  => 'name'
					)
				),
				array(
					'type'     => 'select',
					'label'    => $this->l( 'Product Manufacturer' ),
					'hint'     => $this->l( 'Select the field you are using to specify the manufacturer' ),
					'name'     => 'map_manufacturer',
					'required' => true,
					'options'  => array(
						'query' => $productManufacturerOptions,
						'id'    => 'id_option',
						'name'  => 'name'
					)
				),
				array(
					'type'     => 'select',
					'label'    => $this->l( 'Product Link' ),
					'hint'     => $this->l( 'URL that leads to product. For upcoming features.' ),
					'name'     => 'map_link',
					'required' => true,
					'options'  => array(
						'query' => $productLinkOptions,
						'id'    => 'id_option',
						'name'  => 'name'
					)
				),
				array(
					'type'     => 'select',
					'label'    => $this->l( 'Product Image' ),
					'hint'     => $this->l( 'Choose if you want to use cover image or some random image from product\'s gallery.' ),
					'name'     => 'map_image',
					'required' => true,
					'options'  => array(
						'query' => $productImageLinkOptions,
						'id'    => 'id_option',
						'name'  => 'name'
					)
				),
				array(
					'type'     => 'select',
					'label'    => $this->l( 'Product Categories' ),
					'hint'     => $this->l( 'Choose product tags if and only if no categories are set and instead product tags are present.' ),
					'name'     => 'map_category',
					'required' => true,
					'options'  => array(
						'query' => $productCategoriesOptions,
						'id'    => 'id_option',
						'name'  => 'name'
					)
				),
				array(
					'type'     => 'select',
					'label'    => $this->l( 'Product Prices' ),
					'hint'     => $this->l( 'This option specify the product price that will be used in XML. This should be left to "Retail price with tax"' ),
					'name'     => 'map_price_with_vat',
					'required' => true,
					'options'  => array(
						'query' => $productPriceOptions,
						'id'    => 'id_option',
						'name'  => 'name'
					)
				),
				array(
					'type'     => 'select',
					'label'    => $this->l( 'Product Manufacturer Reference Code' ),
					'hint'     => $this->l( 'This option should reflect product\' manufacturer SKU' ),
					'name'     => 'map_mpn',
					'required' => true,
					'options'  => array(
						'query' => $productMPNOptions,
						'id'    => 'id_option',
						'name'  => 'name'
					)
				),
				array(
					'type'     => 'select',
					'label'    => $this->l( 'Product ISBN' ),
					'hint'     => $this->l( 'This field will be used if you sell books in your store, to specify the ISBN of the book' ),
					'name'     => 'map_isbn',
					'options'  => array(
						'query' => $productISBNOptions,
						'id'    => 'id_option',
						'name'  => 'name'
					)
				),
				array(
					'type'   => 'select',
					'label'  => $this->l( 'Size Attributes' ),
					'hint'   => $this->l( 'Choose the attributes that you use to specify product sizes. This field is used only if Fashion Store option is enabled.' ),
					'name'   => 'map_size[]',
					'id'     => 'map_size',
					'multiple' => true,
					'options' => array(
						'query' => $productSizesOptions,
						'id'    => 'id_option',
						'name'  => 'name',
					),
				),
				array(
					'type'   => 'select',
					'label'  => $this->l( 'Color Attributes' ),
					'hint'   => $this->l( 'Choose the attributes that you use to specify product colors. This field is used only if Fashion Store option is enabled.' ),
					'name'   => 'map_color[]',
					'id'     => 'map_color',
					'multiple' => true,
					'options' => array(
						'query' => $productColorOptions,
						'id'    => 'id_option',
						'name'  => 'name',
					),
				),
			),
			'submit' => array(
				'title' => $this->l( '   Save   ' ),
				'class' => 'button pull-right'
			)
		);


		$this->module          = $plugin;
		$this->name_controller = $plugin->name;
		$this->token           = Tools::getAdminTokenLite( 'AdminModules' );
		$this->currentIndex    = AdminController::$currentIndex . '&configure=' . $plugin->name;
		$this->bootstrap       = true;

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
		$this->fields_value = $optionValues;
		$this->fields_value['map_color[]'] = $optionValues['map_color'];
		$this->fields_value['map_size[]'] = $optionValues['map_size'];

		return $this;
	}

	public function generateForm( $fields = array() ) {
		if ( empty( $fields ) ) {
			return parent::generate();
		} else {
			return parent::generateForm( $fields );
		}
	}
}