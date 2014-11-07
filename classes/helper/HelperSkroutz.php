<?php
/**
 * skroutzxmlfeed
 * ${FILE_NAME}
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 6/11/2014
 * Time: 2:02 μμ
 * Copyright: 2014 Panagiotis Vagenas
 */
if (!defined('_PS_VERSION_'))
  exit;
  
class HelperSkroutz extends Helper{
	protected $progress = 0;
	protected $progressUpdateInterval = 5;

	public function update_woo_product( $post_id ) {
		// TODO Implement in feature releases
	}

	/**
	 *
	 */
	public function do_your_woo_stuff() {
		// Todo check what is active prooducts. Maybe we should use an option.
		$products = Product::getProducts(Configuration::get('PS_LANG_DEFAULT'), 0, 0, 'id_product', 'ASC', false, true);

		$productsArray = array();
		foreach ( (array)$products as $key => $product ) {
			$p = new Product($product['id_product']);
			// TODO Check for product avail etc

			$pushArray = $this->getProductArray($p);
			if(!empty($pushArray)){
				array_push($productsArray, $pushArray);
			}
		}

		if(!empty($productsArray)){
			$loader = new HelperSkroutzLoader();
			$loader->loadHelper('XML');
			$xmlHelper = new HelperXML();
			$xmlHelper->parseArray($productsArray);
		}

		return count( $products );
	}


	protected function getProductArray( \WC_Product &$product ) {
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

		if ( $product->product_type == 'variable' && (bool) $this->©option->get( 'is_fashion_store' ) ) {
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
				'product_attr_size'  => $product->get_attribute( 'size' ),
				'_product_attr_size' => isset( $out['size'] ) ? $out['size'] : null,
				'product_attr_brand' => $product->get_attribute( 'brands' ),
				'product get attr'   => $product->get_attributes(),
			);
		}

		return $out;
	}

	protected function getProductColors( \WC_Product_Variable &$product ) {
		if ( ! (bool) $this->©option->get( 'map_color_use' ) ) {
			return null;
		}

		$map = $this->©option->get( 'map_color' );

		foreach ( $map as $attrId ) {
			$taxonomy = $this->getTaxonomyById( $attrId );

			if ( ! $taxonomy ) {
				return '';
			}

			$colors = array();
			foreach ( $product->get_available_variations() as $variation ) {
				$key = 'attribute_' . wc_attribute_taxonomy_name( $taxonomy->attribute_name );
				if ( isset( $variation['attributes'][ $key ] ) && $variation['is_in_stock'] && $variation['is_purchasable'] ) {
					$color = $this->sanitizeVariationString( $variation['attributes'][ $key ] );
					if ( ! empty( $color ) ) {
						$colors[] = $color;
					}
				}
			}
		}

		$colors = array_unique( $colors );

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

	protected function getProductSizes( \WC_Product &$product ) {
		if ( ! (bool) $this->©option->get( 'map_size_use' ) ) {
			return null;
		}

		$map = $this->©option->get( 'map_size' );

		foreach ( $map as $attrId ) {
			$taxonomy = $this->getTaxonomyById( $attrId );

			if ( ! $taxonomy ) {
				return '';
			}

			$sizes = array();
			foreach ( $product->get_available_variations() as $variation ) {
				$key = 'attribute_' . wc_attribute_taxonomy_name( $taxonomy->attribute_name );
				if ( isset( $variation['attributes'][ $key ] ) && $variation['is_in_stock'] && $variation['is_purchasable'] ) {
					$size = $this->sanitizeVariationString( $variation['attributes'][ $key ] );
					if ( $this->isValidSizeString( $size ) ) {
						$sizes[] = $size;
					}
				}
			}
		}
		$sizes = array_unique( $sizes );

		return implode( ', ', $sizes );
	}

	protected function sanitizeVariationString( $string ) {
		$string = preg_replace( "/[^A-Za-z0-9 ]/", '.', strip_tags( trim( $string ) ) );
		$string = strtoupper( $string );

		return $string;
	}

	protected function getProductManufacturer( \WC_Product &$product ) {
		$option = $this->©option->get( 'map_manufacturer' );

		$manufacturer = '';
		if ( is_numeric( $option ) ) {
			$manufacturer = $this->getProductAttrValue( $option, '' );
		}
		if ( empty( $manufacturer ) ) {
			$manufacturer = $this->getFormatedTextFromTerms( $product, $option );
		}

		return $manufacturer;
	}

	protected function isInStock( \WC_Product &$product ) {
		return $product->is_in_stock() ? 'Y' : 'N';
	}

	protected function getProductPrice( \WC_Product &$product ) {
		$option = $this->©option->get( 'map_price_with_vat' );

		switch ( $option ) {
			case 1:
				$price = $product->get_sale_price();
				break;
			case 2:
				$price = $product->get_price_excluding_tax();
				break;
			default:
				$price = $product->get_price();
				break;
		}

		return $price;
	}

	protected function getProductCategories( \WC_Product &$product ) {
		$option     = $this->©option->get( 'map_category' );
		$categories = '';
		if ( is_numeric( $option ) ) {
			$categories = $this->getProductAttrValue( $option, '' );
		}
		if ( empty( $categories ) ) {
			$categories = $this->getFormatedTextFromTerms( $product, $option );
		}

		return $categories;
	}

	protected function getProductImageLink( \WC_Product &$product ) {
		$option = $this->©option->get( 'map_image' );

		// Maybe we will implement some additional functionality in the future
		if ( true || $option == 0 ) {
			$imageLink = wp_get_attachment_image_src( $product->get_image_id() );
			$imageLink = is_array( $imageLink ) ? $imageLink[0] : '';
		}

		return urldecode( $imageLink );
	}

	protected function getProductId( \WC_Product &$product ) {
		$option = $this->©option->get( 'map_id' );
		if ( $option == 0 ) {
			return $product->get_sku();
		} else {
			return $product->id;
		}
	}

	protected function getProductMPN( \WC_Product &$product ) {
		$option = $this->©option->get( 'map_mpn' );

		if ( $option == 0 ) {
			return $product->get_sku();
		}

		return $this->getProductAttrValue( $option, $product->get_sku() );
	}

	protected function getProductLink( \WC_Product &$product ) {
		$option = $this->©option->get( 'map_link' );

		// Maybe we will implement some additional functionality in the future
		if ( true || $option == 0 ) {
			$link = $product->get_permalink();
		}


		return urldecode( $link );
	}

	protected function getProductName( \WC_Product &$product ) {
		$option    = $this->©option->get( 'map_name' );
		$appendSKU = $this->©option->get( 'map_name_append_sku' );
		$name      = '';

		if ( $option != 0 ) {
			$name = $this->getProductAttrValue( $option );
		}

		if ( empty( $name ) ) {
			$name = $product->get_title();
		}

		$name = trim( $name );

		if ( ! is_numeric( strpos( $product->get_title(), $product->get_sku() ) ) && $appendSKU ) {
			$name .= ' ' . $this->getProductId( $product );
		}

		return $name;
	}

	protected function getProductAttrValue( $attrId, $defaultValue = null ) {
		foreach ( wc_get_attribute_taxonomies() as $taxonomy ) {
			if ( $taxonomy->attribute_id == $attrId ) {
				return trim( $taxonomy->attribute_name );
			}
		}

		return $defaultValue;
	}

	protected function isSizeVariation( $variationArray ) {
		foreach ( $variationArray['attributes'] as $k => $v ) {
			if ( is_numeric( strpos( $k, 'size' ) ) ) {
				return $k;
			}
		}

		return false;
	}

	protected function isColorVariation( $variationArray ) {
		foreach ( $variationArray['attributes'] as $k => $v ) {
			if ( is_numeric( strpos( $k, 'color' ) ) ) {
				return $k;
			}
		}

		return false;
	}

	protected function isBrandVariation( $variationArray ) {
		foreach ( $variationArray['attributes'] as $k => $v ) {
			if ( is_numeric( strpos( $k, 'brand' ) ) ) {
				return $k;
			}
		}

		return false;
	}

	protected function getAvailabilityString( \WC_Product &$product ) {
		// If product is in stock
		if ( $product->is_in_stock() ) {
			return $this->©option->availOptions[ $this->©option->get( 'avail_inStock' ) ];
		} elseif ( $product->backorders_allowed() ) {
			// if product is out of stock and no backorders then return false
			if ( $this->©option->get( 'avail_backorders' ) == count( $this->©option->availOptions ) ) {
				return false;
			}

			// else return value
			return $this->©option->availOptions[ $this->©option->get( 'avail_backorders' ) ];
		} elseif ( $this->©option->get( 'avail_outOfStock' ) != count( $this->©option->availOptions ) ) {
			// no stock, no backorders but must include product. Return value
			return $this->©option->availOptions[ $this->©option->get( 'avail_outOfStock' ) ];
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

	protected function getFormatedTextFromTerms( \WC_Product &$product, $term ) {
		$terms = get_the_terms( $product->id, $term );
		$out   = array();
		if ( is_array( $terms ) ) {
			foreach ( $terms as $k => $term ) {
				$name  = rtrim( ltrim( $term->name ) );
				$out[] = $name;
			}
		}

		return implode( ' - ', array_unique( $out ) );
	}

	public function debug() {
		/* @var WooCommerce $woocommerce */
		global $woocommerce;

		$args = array(
			'post_type'      => 'product',
			'posts_per_page' => - 1
		);
		$loop = new \WP_Query( $args );

		if ( $loop->have_posts() ) {
			$products = array();
			$oneSize  = array();
			while ( $loop->have_posts() ) {
				$loop->the_post();
				$product = wc_setup_product_data( $loop->post ); //new \WC_Product( $loop->post->ID );

				if ( ! $product->is_purchasable() || ! $product->is_visible() || ! $product->is_in_stock() ) {
					continue;
				}

				$p = $this->getProductArray( $product );
//				$products[] = $p;
				$oneSize[] = $p;
			}
			echo "<strong>not real mem usage: </strong>" . ( memory_get_peak_usage( false ) / 1024 / 1024 ) . " MiB<br>";
			echo "<strong>real mem usage: </strong>" . ( memory_get_peak_usage( true ) / 1024 / 1024 ) . " MiB<br><br>";
			var_dump( $oneSize );
			die;
		}
		var_dump( 'No Products' );
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