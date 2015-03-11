<?php
/**
 * Project: skroutzxmlfeed
 * File: XML.php
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 13/2/2015
 * Time: 10:37 μμ
 * Since: 150213
 * Copyright: 2015 Panagiotis Vagenas
 */

namespace SkroutzXML;

class XML extends \XDaRk_v150216\XML{
	/**
	 * @var array
	 */
	protected $skzXMLFields = array(
		'id',
		'name',
		'link',
		'image',
		'category',
		'price_with_vat',
		'instock',
		'availability',
		'manufacturer',
		'mpn',
		'size',
		'color',
	);
	/**
	 * @var array
	 */
	protected $skzXMLFieldsLengths = array(
		'id'             => 200,
		'name'           => 300,
		'link'           => 1000,
		'image'          => 400,
		'category'       => 250,
		'price_with_vat' => 0,
		'instock'        => 0,
		'availability'   => 60,
		'manufacturer'   => 100,
		'mpn'            => 80,
		'size'           => 500,
		'color'          => 100,
	);
	/**
	 * @var array
	 */
	protected $skzXMLRequiredFields = array(
		'id',
		'name',
		'link',
		'image',
		'category',
		'price_with_vat',
		'instock',
		'availability',
		'manufacturer',
		'mpn',
	);

	/**
	 * @var SimpleXMLExtended
	 */
	public $simpleXML = null;

	/**
	 * Absolute file path
	 * @var string
	 */
	public $fileLocation = '';

	public $createdAt = null;
	public $createdAtName = 'created_at';

	protected $rootElemName = 'mywebstore';
	protected $productsElemWrapper = 'products';
	protected $productElemName = 'product';

	/**
	 * @param array $array
	 *
	 * @return bool|mixed
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	public function parseArray( Array $array ) {
		// init simple xml if is not initialized already
		if ( ! $this->simpleXML ) {
			$this->initSimpleXML();
		}

		// get products node
		$products = $this->simpleXML->children();

		// parse array
		foreach ( $array as $k => $v ) {
			$validated = $this->validateArrayKeys( $v );

			if ( empty( $validated ) ) {
				unset( $array[ $k ] );
			} else {
				/* @var SimpleXMLExtended $product */
				$product = $products->addChild( $this->productElemName );

				foreach ( $validated as $key => $value ) {
					if ( $this->isValidXmlName( $value ) ) {
						$product->addChild( $key, $value );
					} else {
						$product->$key = null;
						$product->$key->addCData( $value );
					}
				}
			}
		}

		return $this->saveXML();
	}

	/**
	 * @return $this
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	protected function initSimpleXML() {
		if ( ! $this->loadXML() ) {
			$this->simpleXML = new SimpleXMLExtended( '<?xml version="1.0" encoding="UTF-8"?><' . $this->rootElemName . '></' . $this->rootElemName . '>' );
		}

		// TODO We must update the created at child
		/**
		 * For now we recreate the XML file everytime so no need to check for updates
		 */
//		$createdAt =  $this->attribute( $this->simpleXML, $this->createdAtName );

		// check for child nodes
		$products = $this->attribute( $this->simpleXML, $this->productsElemWrapper );

		if ( empty( $products ) ) {
			$this->simpleXML->addChild( $this->productsElemWrapper );
		};

		return $this;
	}

	/**
	 * @param array $array
	 *
	 * @return array
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	protected function validateArrayKeys( Array $array ) {
		foreach ( $this->skzXMLRequiredFields as $fieldName ) {
			if ( ! isset( $array[ $fieldName ] ) ) {
				return array();
			} else {
				$array[ $fieldName ] = $this->trimField( $array[ $fieldName ], $fieldName );
				if ( is_string( $array[ $fieldName ] ) ) {
					$array[ $fieldName ] = mb_convert_encoding( $array[ $fieldName ], "UTF-8" );
				}
			}
		}

		foreach ( $array as $k => $v ) {
			if ( ! in_array( $k, $this->skzXMLFields ) ) {
				unset( $array[ $k ] );
			}
		}

		return $array;
	}

	protected function isValidXmlName( $name ) {
		try {
			new \DOMElement( $name );

			return true;
		} catch ( \DOMException $e ) {
			return false;
		}
	}

	/**
	 * @param $value
	 * @param $fieldName
	 *
	 * @return bool|string
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	protected function trimField( $value, $fieldName ) {
		if ( ! isset( $this->skzXMLFieldsLengths[ $fieldName ] ) ) {
			return false;
		}

		if ( $this->skzXMLFieldsLengths[ $fieldName ] === 0 ) {
			return $value;
		}

		return substr( (string) $value, 0, $this->skzXMLFieldsLengths[ $fieldName ] );
	}


	/**
	 * @return bool
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	protected function loadXML() {
		/**
		 * For now we write it from scratch EVERY TIME
		 */
		$this->fileLocation = $this->getFileLocation();

		@unlink( $this->fileLocation );

		return false;
	}

	/**
	 * @return bool|mixed
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	protected function saveXML() {
		$dir = dirname($this->fileLocation);
		if(!file_exists($dir)){
			mkdir($dir, 0755, true);
		}

		if ( $this->simpleXML && ! empty( $this->fileLocation ) && (is_writable( $this->fileLocation ) || is_writable($dir) ) ) {
			$this->simpleXML->addChild( $this->createdAtName, date( 'Y-m-d H:i' ) );
			return $this->simpleXML->asXML( $this->fileLocation ); // TODO Will this create the dir path?
		}

		return false;
	}

	/**
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	public function printXML() {
		if(headers_sent()) return;

		if ( ! ( $this->simpleXML instanceof SimpleXMLExtended )) {
			$fileLocation = $this->getFileLocation();
			if ( !$this->existsAndReadable( $fileLocation ) ) {
				die('EW:X:P');
			}
			$this->simpleXML = simplexml_load_file( $fileLocation );
		}

		header ("Content-Type:text/xml");

		echo $this->simpleXML->asXML();

		exit(0);
	}

	/**
	 * @return string
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	public function getFileLocation() {
		$location =  trim($this->Options->getValue( 'xml_location' ), '\\/ ' );
		$fileName = $this->Options->getValue( 'xml_fileName' );

		return rtrim( _PS_ROOT_DIR_, '\\/' ) . '/' . (empty($location) ? '' : $location . '/' ) . rtrim(ltrim($fileName, '\\/'), '\\/');
	}

	/**
	 * @return array|null
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	public function getFileInfo() {
		$fileLocation = $this->getFileLocation();

		$info = array();
		if ( $this->existsAndReadable( $fileLocation ) ) {

			$sXML = simplexml_load_file( $fileLocation );
			if($sXML){
				$cratedAtName = $this->createdAtName;
				$info[ $this->createdAtName ] = array(
					'value' => end( $sXML->$cratedAtName ),
					'label' => 'Cached File Creation Datetime'
				);
				$info['cachedFilePath']       = array( 'value' => $fileLocation, 'label' => 'Cached File Path' );

				$info['url']                  = array(
					'value' => \Tools::getHttpHost(true).__PS_BASE_URI__ . trim(str_replace(_PS_ROOT_DIR_, '', $fileLocation), '/'),
					'label' => 'File Url'
				);
				$info['size']                 = array( 'value' => filesize( $fileLocation ), 'label' => 'Cached File Size' );
			}
		} else {
			$info[ $this->createdAtName ] = array(
				'value' => 'no data',
				'label' => 'Cached File Creation Datetime'
			);
			$info['cachedFilePath']       = array( 'value' => 'no data', 'label' => 'Cached File Path' );
			$info['url']                  = array(
				'value' => 'no data',
				'label' => 'File Url'
			);
			$info['size']                 = array( 'value' => 'no data', 'label' => 'Cached File Size' );
		}
		return $info;
	}

	/**
	 * @param $file
	 *
	 * @return int
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	public function countProductsInFile( $file ) {
		if ( $this->existsAndReadable( $file ) ) {
			$sXML = simplexml_load_file( $file );
		} elseif ( $file instanceof \SimpleXMLElement || $file instanceof SimpleXMLExtended ) {
			$sXML = &$file;
		} else {
			return 0;
		}

		if ( $sXML->getName() == $this->productsElemWrapper ) {
			return $sXML->count();
		}elseif ( $sXML->getName() == $this->rootElemName ) {
			return $sXML->children( )->children()->count();
		}

		return 0;
	}

	/**
	 * @param $file
	 *
	 * @return bool
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	protected function existsAndReadable( $file ) {
		return is_string( $file ) && file_exists( $file ) && is_readable( $file );
	}

	/**
	 * @param \SimpleXMLElement $xml
	 * @param $attribute
	 *
	 * @return string
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	public function attribute(\SimpleXMLElement $xml, $attribute)
	{
		foreach($xml->attributes() as $_attribute => $_value)
			if(strcasecmp($_attribute, $attribute) === 0)
				return (string)$_value;
		unset($_attribute, $_value);

		return '';
	}
}