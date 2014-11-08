<?php
/**
 * skroutzxmlfeed
 * ${FILE_NAME}
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 6/11/2014
 * Time: 2:02 μμ
 * Copyright: 2014 Panagiotis Vagenas
 */
if ( ! defined( '_PS_VERSION_' ) ) {
	exit;
}

class HelperSkroutz extends Helper {
	/**
	 * @var HelperSkroutzOptions
	 */
	protected $options;
	protected $defaultLang;

	public function __construct() {
		$loader = new HelperSkroutzLoader();
		$loader->loadHelper( 'SkroutzOptions' );
		$this->options     = HelperSkroutzOptions::Instance();
		$this->defaultLang = Configuration::get( 'PS_LANG_DEFAULT' );
	}

	public function update_woo_product( $product_id ) {
		// TODO Implement in feature releases
	}

	/**
	 *
	 */
	public function createProductsArray() {
		$products = Product::getProducts( Configuration::get( 'PS_LANG_DEFAULT' ), 0, 0, 'id_product', 'ASC', false, $this->options->getValue( 'include_disabled' ) );

		$backOrdersInclude = $this->options->getValue( 'avail_backorders' );
		$outOfStockInclude = $this->options->getValue( 'avail_outOfStock' );

		foreach ( (array) $products as $key => $product ) {
			$p = new Product( $product['id_product'] );
			$backOrdersAllowed = StockAvailable::getQuantityAvailableByProduct($product->id) < 0;
			// TODO Check for product avail etc

			$hasStock = $p->checkQty( 1 );
			if ( ( (! $backOrdersInclude || ! $outOfStockInclude ) && ! $hasStock )
			     || $p->getType() != 0
			     || $p->visibility == 'none'
			     || $p->available_for_order == 0
			) {
				unset( $products[ $key ] );
				continue;
			}

			$pushArray = $this->getProductArray( $p );

			if ( ! empty( $pushArray ) ) {
				$products[ $key ] = $pushArray;
			} else {
				unset( $products[ $key ] );
			}
		}

		return $products;
	}


	protected function getProductArray( Product &$product ) {
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

		if ( $product->getType() == 2 && (bool) $this->©option->get( 'is_fashion_store' ) ) {
			$variableProduct = new \WC_Product_Variable( $product );

			$colors = $this->getProductColors( $variableProduct );
			$sizes  = $this->getProductSizes( $variableProduct );

			if ( ! empty( $colors ) ) {
				$out['color'] = $colors;
			}

			if ( ! empty( $sizes ) ) {
				$out['size'] = $sizes;
			}
		}
		if ( defined( 'SKROUTZ_DEBUG' ) ) {
			$out['debug'] = array(
				'product' => $product->getAttributesResume( Configuration::get( 'PS_LANG_DEFAULT' ) )
			);
		}

		return $out;
	}

	protected function getProductColors( Product &$product ) {
		$colorList = Product::getAttributesColorList( array( $product->id ) );

		if ( ! $colorList || empty( $colorList ) || ! isset( $colorList[ $product->id ] ) || empty( $colorList[ $product->id ] ) ) {
			return '';
		}

		$colors = array();
		foreach ( $colorList[ $product->id ] as $k => $color ) {
			array_push( $colors, $color['name'] );
		}

		return implode( ', ', $colors );
	}

	protected function getTaxonomyById( $taxonomyId ) {
		foreach ( wc_get_attribute_taxonomies() as $taxonomy ) {
			$options[] = array(
				'label' => $taxonomy->attribute_label,
				'value' => $taxonomy->attribute_id
			);
			if ( $taxonomyId == $taxonomy->attribute_id ) {
				return $taxonomy;
			}
		}

		return null;
	}

	protected function getProductSizes( Product &$product ) {
		$mapSizes = $this->options->getValue( 'map_size' );
		if ( empty( $mapSizes ) ) {
			return null;
		}

		$sizes = array();
		foreach ( $product->getAttributeCombinations( $this->defaultLang ) as $key => $comp ) {
			if (
				$comp['is_color_group']
				|| ! in_array( $comp['id_attribute'], $mapSizes )
				|| $comp['quantity'] < 1
			) {
				continue;
			}

			$size = $this->sanitizeVariationString( $comp['attribute_name']);
			if($this->isValidSizeString($size)){
				array_push($sizes, $size);
			}
		}

		return implode( ', ', $sizes );
	}

	protected function sanitizeVariationString( $string ) {
		$string = preg_replace( "/[^A-Za-z0-9 ]/", '.', strip_tags( trim( $string ) ) );
		$string = strtoupper( $string );

		return $string;
	}

	protected function getProductManufacturer( Product &$product ) {
		$option = $this->options->getValue( 'map_manufacturer' );

		return $option == 0 ? $product->getWsManufacturerName() :Supplier::getNameById($product->id_supplier);
	}

	protected function isInStock( Product &$product ) {
		return $product->checkQty(1) ? 'Y' : 'N';
	}

	protected function getProductPrice( Product &$product ) {
		$option = $this->options->getValue( 'map_price_with_vat' );

		switch ( $option ) {
			case 1:
				$price = round($product->price, 2);
				break;
			case 2:
				$price = round($product->wholesale_price, 2);
				break;
			default:
				$price = $product->getPrice(true, null, 2);
				break;
		}

		return $price;
	}

	protected function getProductCategories( Product &$product ) {
		$categories = array();
		if ( $this->options->getValue( 'map_category' ) == 1 ) {
			$info = Tag::getProductTags($product->id);
			if($info && !isset($info[$this->defaultLang])){
				$categories = $info[$this->defaultLang];
			}
		} else {
			$info = Category::getCategoryInformations($product->getCategories());
			foreach ( $info as $cat ) {
				// Todo is there a better way to check for home category?
				if($cat['id_category'] == 2) continue;
				array_push($categories, $cat['name']);
			}

		}

		return implode(' - ',(array)$categories);
	}

	protected function getProductImageLink( Product &$product ) {
		$link = new Link();

		$imageLink = null;
		if ( $this->options->getValue( 'map_image' ) == 1) {
			$images = $product->getImages($this->defaultLang);
			if(!empty($images)){
				shuffle($images);
				$imageLink = $link->getImageLink($product->link_rewrite, end($images)['id_image']);
			}
		} else {
			$coverImage = Image::getCover($product->id);
			if($coverImage && !empty($coverImage) && isset($coverImage['id_image'])){
				$imageLink = $link->getImageLink($product->link_rewrite, $coverImage['id_image']);
			}
		}

		return empty($imageLink) ? '' : urldecode( $this->addHttp($imageLink) );
	}

	protected function getProductId( Product &$product ) {
		$option = $this->options->getValue( 'map_id' );

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

	protected function getProductMPN( Product &$product ) {
		$option = $this->options->getValue( 'map_mpn' );

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

	protected function getProductLink( Product &$product ) {
		$link = new Link();

		$pLink = $link->getProductLink($product);

		return urldecode( $this->addHttp($pLink) );
	}

	protected function getProductName( Product &$product ) {
		$name = is_array($product->name) && isset($product->name[$this->defaultLang]) ? $product->name[$this->defaultLang] : (is_string($product->name) ? $product->name : 0);
		return $name . ' ' . ($this->options->getValue( 'map_name_append_sku' ) ? $this->getProductId($product) : '');
	}

	protected function addHttp($url) {
		if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
			$url = "http://" . $url;
		}
		return $url;
	}

	protected function getAvailabilityString( Product &$product ) {
		$hasStock = $product->checkQty(1);
$stock = new Stock();

		// If product is in stock
		if ( $hasStock ) {
			return $this->options->availOptions[ $this->options->getValue( 'avail_inStock' ) ];
		} elseif ( !$hasStock && StockAvailable::getQuantityAvailableByProduct($product->id) < 0 ) {
			// if product is out of stock and no backorders then return false
			if ( $this->options->getValue( 'avail_backorders' ) == 0 ) {
				return false;
			}

			// else return value
			return $this->options->availOptions[ $this->options->getValue( 'avail_backorders' )-1 ];
		} elseif ( $this->options->getValue( 'avail_outOfStock' ) > 0 ) {
			// no stock, no backorders but must include product. Return value
			return $this->options->availOptions[ $this->options->getValue( 'avail_outOfStock' ) - 1 ];
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
		echo "<strong>real mem usage: </strong>" . ( memory_get_peak_usage( true ) / 1024 / 1024 ) . " MiB<br><br>";
		var_dump( $this->createProductsArray() );
		die;
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
}