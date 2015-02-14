<?php
/**
 * Project: skroutzxmlfeed
 * File: Skroutz.php
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 13/2/2015
 * Time: 6:06 μμ
 * Since: 150213
 * Copyright: 2015 Panagiotis Vagenas
 */

namespace SkroutzXML;


use XDaRk\Core;

/**
 * Class Skroutz
 * @package SkroutzXML
 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
 * @since 150213
 */
class Skroutz extends Core {
	private $defaultLang;

	public function __construct( \Module &$moduleInstance ) {
		parent::__construct( $moduleInstance );
		$this->defaultLang = \Configuration::get( 'PS_LANG_DEFAULT' );
	}

	public function generateXMLFile() {
		$productsArray = $this->createProductsArray();
		if ( ! $this->XML->parseArray( $productsArray ) ) {
			return false;
		}

		return count( $productsArray );
	}

	/**
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since TODO ${VERSION}
	 */
	public function printXMLFile() {
		$interval         = $this->getGenerationInterval();
		$xmlCreation      = $this->XML->getFileInfo();
		$createdTime      = strtotime( $xmlCreation['File Creation Datetime'] );
		$nextCreationTime = $interval + $createdTime;
		$time             = time();
		if ( $time > $nextCreationTime ) {
			$this->generateXMLFile();
		}

		$this->XML->printXML();
		exit( 0 );
	}

	public function createProductsArray() {
		$products = \Product::getProducts( $this->defaultLang, 0, 0, 'id_product', 'ASC', false, $this->Options->getValue( 'include_disabled' ) );

		$backOrdersInclude = $this->Options->getValue( 'avail_backorders' ) > 0;
		$outOfStockInclude = $this->Options->getValue( 'avail_outOfStock' ) > 0;

		foreach ( (array) $products as $key => $product ) {
			$p = new \Product( $product['id_product'] );

			// TODO Check for product avail etc

			$hasStock = $p->checkQty( 1 );

			if ( $p->getType() != 0
			     || $p->visibility == 'none'
			     || $p->available_for_order == 0
			) {
				unset( $products[ $key ] );
				// TODO Log skipped product
				continue;
			}

			if(!$hasStock
			   && (!$outOfStockInclude
			       || (!$backOrdersInclude && $p->quantity < 0)
				)
			){
					var_dump( $backOrdersInclude, $outOfStockInclude, ( ( ! $backOrdersInclude || ! $outOfStockInclude ) && ! $hasStock ) );
					d( $products[ $key ] );

					unset( $products[ $key ] );
					// TODO Log skipped product
					continue;
			}

			$pushArray = $this->getProductArray( $p );

			if ( ! empty( $pushArray ) ) {
				$products[ $key ] = $pushArray;
			} else {
				// TODO Log skipped product
				unset( $products[ $key ] );
			}
		}

		return $products;
	}

	protected function getProductArray( \Product &$product ) {
		$out = array();

		$out['id']             = $this->getProductId( $product );
		$out['mpn']            = $this->getProductMPN( $product );
		$out['name']           = $this->getProductName( $product );
		$out['link']           = $this->getProductLink( $product );
		$out['image']          = $this->getProductImageLink( $product );
		$out['category']       = $this->getProductCategories( $product );
		$out['price_with_vat'] = $this->getProductPrice( $product );
		$out['instock']        = $this->isInStock( $product );
		$out['availability']   = $this->getAvailabilityString( $product );
		$out['manufacturer']   = $this->getProductManufacturer( $product );

		if ( count( (array) $this->Options->getValue( 'map_size' ) ) ) {
			$out['size'] = $this->getProductSizes( $product );
		}

		if ( count( (array) $this->Options->getValue( 'map_color' ) ) ) {
			$out['color'] = $this->getProductColors( $product );
		}

		return $out;
	}

	protected function getProductColors( \Product &$product ) {
		$colorList = \Product::getAttributesColorList( array( $product->id ) );

		if ( ! $colorList || empty( $colorList ) || ! isset( $colorList[ $product->id ] ) || empty( $colorList[ $product->id ] ) ) {
			return '';
		}

		$colors = array();
		foreach ( $colorList[ $product->id ] as $k => $color ) {
			if ( (int) $product->isColorUnavailable( $color['id_attribute'], \Context::getContext()->shop->id ) === $color['id_product_attribute'] && ! $this->backOrdersAllowed( $product ) ) {
				continue;
			}

			array_push( $colors, $color['name'] );
		}

		return implode( ', ', $colors );
	}

	protected function getProductSizes( \Product &$product ) {
		$mapSizes = $this->Options->getValue( 'map_size' );

		if ( empty( $mapSizes ) ) {
			return null;
		}

		$sizes = array();
		foreach ( $product->getAttributeCombinations( $this->defaultLang ) as $key => $comp ) {
			if (
				$comp['is_color_group']
				|| ! in_array( $comp['id_attribute'], $mapSizes )
				|| ( $comp['quantity'] < 1 && ! $this->backOrdersAllowed( $product ) )
			) {
				continue;
			}

			$size = $this->sanitizeVariationString( $comp['attribute_name'] );
			if ( $this->isValidSizeString( $size ) ) {
				array_push( $sizes, $size );
			}
		}

		return implode( ', ', array_unique( $sizes ) );
	}

	protected function sanitizeVariationString( $string ) {
		$string = preg_replace( "/[^A-Za-z0-9 ]/", '.', strip_tags( trim( $string ) ) );
		$string = strtoupper( $string );

		return $string;
	}

	protected function getProductManufacturer( \Product &$product ) {
		$option = $this->Options->getValue( 'map_manufacturer' );

		return $option == 0 ? $product->getWsManufacturerName() : \Supplier::getNameById( $product->id_supplier );
	}

	protected function isInStock( \Product &$product ) {
		return ( $product->checkQty( 1 ) || $this->backOrdersAllowed( $product ) ) ? 'Y' : 'N';
	}

	protected function getProductPrice( \Product &$product ) {
		$option = $this->Options->getValue( 'map_price_with_vat' );

		switch ( $option ) {
			case 1:
				$price = round( $product->price, 2 );
				break;
			case 2:
				$price = round( $product->wholesale_price, 2 );
				break;
			default:
				$price = $product->getPrice( true, null, 2 );
				break;
		}

		return $price;
	}

	protected function getProductCategories( \Product &$product ) {
		$categories = array();
		if ( $this->Options->getValue( 'map_category' ) == 1 ) {
			$info = \Tag::getProductTags( $product->id );
			if ( $info && ! isset( $info[ $this->defaultLang ] ) ) {
				$categories = (array) $info[ $this->defaultLang ];
			}
		} else {
			$info = \Category::getCategoryInformations( $product->getCategories() );
			if ( ! is_array( $info ) || empty( $info ) ) {
				return '';
			}
			foreach ( (array) $info as $cat ) {
				// Todo is there a better way to check for home category?
				if ( $cat['id_category'] == 2 ) {
					continue;
				}
				array_push( $categories, $cat['name'] );
			}

		}

		return implode( ' - ', (array) $categories );
	}

	protected function getProductImageLink( \Product &$product ) {
		$link = new \Link();

		$imageLink = null;
		if ( $this->Options->getValue( 'map_image' ) == 1 ) {
			$images = $product->getImages( $this->defaultLang );
			if ( ! empty( $images ) ) {
				shuffle( $images );
				$imageLink = $link->getImageLink( $product->link_rewrite, end( $images )['id_image'] );
			}
		} else {
			$coverImage = \Image::getCover( $product->id );
			if ( $coverImage && ! empty( $coverImage ) && isset( $coverImage['id_image'] ) ) {
				$imageLink = $link->getImageLink( $product->link_rewrite, $coverImage['id_image'] );
			}
		}

		return empty( $imageLink ) ? '' : urldecode( $this->addHttp( $imageLink ) );
	}

	protected function getProductId( \Product &$product ) {
		$option = $this->Options->getValue( 'map_id' );

		switch ( $option ) {
			case 1:
				$id = $product->ean13;
				break;
			case 2:
				$id = $product->upc;
				break;
			default:
				$id = $product->reference;
				break;
		}

		return $id;
	}

	protected function getProductMPN( \Product &$product ) {
		$option = $this->Options->getValue( 'map_mpn' );

		switch ( $option ) {
			case 1:
				$id = $product->ean13;
				break;
			case 2:
				$id = $product->upc;
				break;
			case 3:
				$id = $product->supplier_reference;
				break;
			default:
				$id = $product->reference;
				break;
		}

		return $id;
	}

	protected function getProductLink( \Product &$product ) {
		$link = new \Link();

		$pLink = $link->getProductLink( $product );

		return urldecode( $this->addHttp( $pLink ) );
	}

	protected function getProductName( \Product &$product ) {
		$name = is_array( $product->name ) && isset( $product->name[ $this->defaultLang ] ) ? $product->name[ $this->defaultLang ] : ( is_string( $product->name ) ? $product->name : 0 );

		return $name . ' ' . ( $this->Options->getValue( 'map_name_append_sku' ) ? $this->getProductId( $product ) : '' );
	}

	protected function addHttp( $url ) {
		if ( ! preg_match( "~^(?:f|ht)tps?://~i", $url ) ) {
			$url = "http://" . $url;
		}

		return $url;
	}

	protected function getAvailabilityString( \Product &$product ) {
		$hasStock = $product->checkQty( 1 );

		// If product is in stock
		if ( $hasStock ) {
			return $this->Options->availOptions[ $this->Options->getValue( 'avail_inStock' ) ];
		} elseif ( ! $hasStock && $this->backOrdersAllowed( $product ) ) {
			// if product is out of stock and no backorders then return false
			if ( $this->Options->getValue( 'avail_backorders' ) == 0 ) {
				return false;
			}

			// else return value
			return $this->Options->availOptions[ $this->Options->getValue( 'avail_backorders' ) - 1 ];
		} elseif ( $this->Options->getValue( 'avail_outOfStock' ) > 0 ) {
			// no stock, no backorders but must include product. Return value
			return $this->Options->availOptions[ $this->Options->getValue( 'avail_outOfStock' ) - 1 ];
		}

		return false;
	}

	protected function formatSizeColorStrings( $string ) {
		if ( is_array( $string ) ) {
			array_walk( $string, function ( $item, $key ) {
				return $this->formatSizeColorStrings( $item );
			} );

			return implode( ',', $string );
		}

		$patterns        = array();
		$patterns[0]     = '/\|/';
		$patterns[1]     = '/\s+/';
		$replacements    = array();
		$replacements[2] = ',';
		$replacements[1] = '';

		return preg_replace( $patterns, $replacements, $string );
	}

	public function debug() {
		echo "<strong>not real mem usage: </strong>" . ( memory_get_peak_usage( false ) / 1024 / 1024 ) . " MiB<br>";
		echo "<strong>real mem usage: </strong>" . ( memory_get_peak_usage( true ) / 1024 / 1024 ) . " MiB<br>";
		$sTime     = microtime( true );
		$prodArray = $this->createProductsArray();
		echo "<strong>time: </strong>" . ( microtime( true ) - $sTime ) . " sec<br><br>";
		var_dump( $prodArray );
		die;
	}

	protected function backOrdersAllowed( \Product $product ) {
		return \Product::isAvailableWhenOutOfStock( $product->out_of_stock ) == 1;
	}

	protected function isValidSizeString( $string ) {
		if ( is_numeric( $string ) ) {
			return true;
		}

		$validStrings = array(
			'XXS',
			'XS',
			'S',
			'M',
			'L',
			'XL',
			'XXL',
			'XXXL',
			'Extra Small',
			'Small',
			'Medium',
			'Large',
			'Extra Large'
		);

		return in_array( $string, $validStrings );
	}

	protected function getGenerationInterval() {
		return 86400;
	}
}