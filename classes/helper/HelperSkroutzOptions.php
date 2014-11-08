<?php
/**
 * skroutzxmlfeed
 * ${FILE_NAME}
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 6/11/2014
 * Time: 2:19 μμ
 * Copyright: 2014 Panagiotis Vagenas
 */
if ( ! defined( '_PS_VERSION_' ) ) {
	exit;
}

final class HelperSkroutzOptions {
	public $defaults = array();
	protected $stored = array();
	protected $optionsArrayName = 'SkroutzXMLFeedOptions';

	/**
	 * @var array Availability options for skroutz.gr
	 */
	public $availOptions = array(
		'Available',
		'1 to 3 days',
		'4 to 7 days',
		'7+ days',
		'Upon order',
		'Pre-order'
	);

	/**
	 * @return $this
	 *
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since ${VERSION}
	 */
	protected function init() {
		$this->defaults = array(
			/*********************
			 * XML File relative
			 ********************/
			// Internal, indicates XML generation progress
			'xml.progress'        => 0,
			// File location
			'xml_location'        => '',
			// File name
			'xml_fileName'        => 'skroutz.xml',
			// Generation interval
			'xml_interval'        => 'daily',
			/*********************
			 * Products relative
			 ********************/
			// Availability when products in stock
			'avail_inStock'       => 1,
			// Availability when products out stock
			'avail_outOfStock'    => 0,
			// Availability when products out stock and backorders are allowed
			'avail_backorders'    => 5,
			// Include disabled products
			'include_disabled'    => 0,
			/*********************
			 * Custom fields
			 ********************/
			'map_id'              => 0,
			'map_name'            => 0,
			'map_name_append_sku' => 1,
			'map_link'            => 0,
			'map_image'           => 0,
			'map_category'        => 0,
			'map_price_with_vat'  => 0,
			'map_manufacturer'    => 0,
			'map_mpn'             => 0,
			'map_isbn'            => 0,
			'map_size'            => array(),
			'map_color'           => array(),
			/***********************************************
			 * Fashion store
			 ***********************************************/
			'is_fashion_store'    => 0,
			'is_book_store'       => 0
		);

		$this->stored = unserialize( Configuration::get( $this->optionsArrayName ) );

		if ( ! $this->stored || empty( $this->stored ) ) {
			$this->stored = $this->defaults;
			$this->saveOptions( $this->stored );
		}

		$this->stored = array_merge($this->defaults, $this->stored);

		return $this;
	}

	/**
	 * @param $key
	 * @param bool $default
	 *
	 * @return mixed
	 * @throws Exception
	 *
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since ${VERSION}
	 */
	public function getValue( $optionName, $default = false ) {
		if ( ! isset( $this->defaults[ $optionName ] ) ) {
			throw new Exception( 'No matching option' );
		}
		if ( $default ) {
			return $this->defaults[ $optionName ];
		}

		return isset( $this->stored[ $optionName ] ) ? $this->stored[ $optionName ] : $this->defaults[ $optionName ];
	}

	/**
	 * @param $newOptions
	 *
	 * @return bool
	 *
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since ${VERSION}
	 */
	public function saveOptions( $newOptions ) {
		$this->stored = $this->validateOptions( $newOptions );

		return Configuration::updateValue( $this->optionsArrayName, serialize( $this->stored ) );
	}

	/**
	 *
	 */
	private function __construct() {
		$this->init();
	}

	public function getOptionsArray( $defaults = false ) {
		return $defaults ? $this->defaults : $this->stored;
	}

	/**
	 * @param Array $newOptions
	 *
	 * @return mixed
	 *
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since ${VERSION}
	 */
	public function validateOptions( Array $newOptions ) {
		foreach ( $newOptions as $k => $v ) {
			if ( ! array_key_exists( $k, $this->defaults ) ) {
				unset( $newOptions[ $k ] );
			}
		}

		$newOptions['xml_location'] = isset( $newOptions['xml_location'] )
		                              && is_string( $newOptions['xml_location'] )
		                              && ( is_writable( _PS_ROOT_DIR_ . '/' . $newOptions['xml_location'] ) || ! is_dir( _PS_ROOT_DIR_ . '/' . $newOptions['xml_location'] ) )
			? pSQL( ltrim( $newOptions['xml_location'], '/\\' ) ) : $this->defaults['xml_location'];

		$newOptions['xml_fileName'] = isset( $newOptions['xml_fileName'] )
		                              && is_string( $newOptions['xml_fileName'] )
		                              && Validate::isFileName( $newOptions['xml_fileName'] )
			? pSQL( $newOptions['xml_fileName'] ) : $this->defaults['xml_fileName'];

		$newOptions['xml_interval'] = isset( $newOptions['xml_interval'] )
		                              && is_string( $newOptions['xml_interval'] )
			? pSQL( $newOptions['xml_interval'] ) : $this->defaults['xml_interval'];

		$newOptions['avail_inStock'] = isset( $newOptions['avail_inStock'] )
		                               && is_numeric( $newOptions['avail_inStock'] )
			? (int) $newOptions['avail_inStock'] : (int) $this->defaults['avail_inStock'];

		$newOptions['avail_outOfStock'] = isset( $newOptions['avail_outOfStock'] )
		                                  && is_numeric( $newOptions['avail_outOfStock'] )
			? (int) $newOptions['avail_outOfStock'] : (int) $this->defaults['avail_outOfStock'];

		$newOptions['avail_backorders'] = isset( $newOptions['avail_backorders'] )
		                                  && is_numeric( $newOptions['avail_outOfStock'] )
			? (int) $newOptions['avail_backorders'] : (int) $this->defaults['avail_backorders'];

		$newOptions['map_id'] = isset( $newOptions['map_id'] )
		                        && is_numeric( $newOptions['map_id'] )
			? (int) $newOptions['map_id'] : (int) $this->defaults['map_id'];

		$newOptions['map_name'] = isset( $newOptions['map_name'] )
		                          && is_numeric( $newOptions['map_name'] )
			? (int) $newOptions['map_name'] : (int) $this->defaults['map_name'];

		$newOptions['map_name_append_sku'] = isset( $newOptions['map_name_append_sku'] )
		                                     && is_numeric( $newOptions['map_name_append_sku'] )
			? (int) $newOptions['map_name_append_sku'] : (int) $this->defaults['map_name_append_sku'];

		$newOptions['map_link'] = isset( $newOptions['map_link'] )
		                          && is_numeric( $newOptions['map_link'] )
			? (int) $newOptions['map_link'] : (int) $this->defaults['map_link'];

		$newOptions['map_image'] = isset( $newOptions['map_image'] )
		                           && is_numeric( $newOptions['map_image'] )
			? (int) $newOptions['map_image'] : (int) $this->defaults['map_image'];

		$newOptions['map_price_with_vat'] = isset( $newOptions['map_price_with_vat'] )
		                                    && is_numeric( $newOptions['map_price_with_vat'] )
			? (int) $newOptions['map_price_with_vat'] : (int) $this->defaults['map_price_with_vat'];

		$newOptions['map_manufacturer'] = isset( $newOptions['map_manufacturer'] )
		                                  && is_numeric( $newOptions['map_manufacturer'] )
			? (int) $newOptions['map_manufacturer'] : (int) $this->defaults['map_manufacturer'];

		$newOptions['map_mpn'] = isset( $newOptions['map_mpn'] )
		                         && is_numeric( $newOptions['map_mpn'] )
			? (int) $newOptions['map_mpn'] : (int) $this->defaults['map_mpn'];

		$newOptions['map_isbn'] = isset( $newOptions['map_isbn'] )
		                          && is_numeric( $newOptions['map_isbn'] )
			? (int) $newOptions['map_isbn'] : (int) $this->defaults['map_isbn'];

		$newOptions['map_size'] = isset( $newOptions['map_size'] )
		                          && is_array( $newOptions['map_size'] )
			? $newOptions['map_size'] : $this->defaults['map_size'];

		$newOptions['map_color'] = isset( $newOptions['map_color'] )
		                           && is_array( $newOptions['map_color'] )
			? $newOptions['map_color'] : $this->defaults['map_color'];

		$newOptions['is_fashion_store'] = isset( $newOptions['is_fashion_store'] )
			? (int) $newOptions['is_fashion_store'] : (int) $this->defaults['is_fashion_store'];

		$newOptions['is_book_store'] = isset( $newOptions['is_book_store'] )
			? (int) $newOptions['is_book_store'] : (int) $this->defaults['is_book_store'];

		return $newOptions;
	}

	public function deleteAllOptions() {
		return Configuration::deleteByName( $this->optionsArrayName );
	}

	/**
	 * Call this method to get singleton
	 *
	 * @return HelperSkroutzOptions
	 */
	public static function Instance() {
		static $inst = null;
		if ( $inst === null ) {
			$inst = new HelperSkroutzOptions();
		}

		return $inst;
	}
}