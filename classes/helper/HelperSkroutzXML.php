<?php
/**
 * skroutzxmlfeed
 * ${FILE_NAME}
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 7/11/2014
 * Time: 1:55 μμ
 * Copyright: 2014 Panagiotis Vagenas
 */
if (!defined('_PS_VERSION_'))
  exit;
  
class HelperSkroutzXML {
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
	 * @var \SimpleXMLExtended
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

	public function __construct(){
		$skzLoader = new HelperSkroutzLoader();
		$skzLoader->loadInclude('SimpleXMLExtended');
		$skzLoader->loadHelper('SkroutzOptions');
	}

	/**
	 * @param array $array
	 *
	 * @return bool|mixed
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
				/* @var \SimpleXMLExtended $product */
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
	 */
	protected function initSimpleXML() {
		if ( ! $this->loadXML() ) {
			$this->simpleXML = new \SimpleXMLExtended( '<?xml version="1.0" encoding="UTF-8"?><' . $this->rootElemName . '></' . $this->rootElemName . '>' );
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
	 * @throws exception
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 0.1
	 */
	protected function loadXML() {
		/**
		 * For now we write it from scratch EVERY TIME
		 */
		$this->fileLocation = $this->getFileLocation();

		@unlink( $this->fileLocation );

		return false;

		try {
			$locate = $this->©dirs_files->locate( $fileLocation, get_home_path() );
		} catch ( exception $e ) {
			return false;
		}

		if ( ! empty( $locate ) && file_exists( $locate ) && is_readable( $locate ) ) {
			$this->simpleXML = simplexml_load_file( $locate );
			if ( $this->simpleXML !== false ) {
				$this->fileLocation = $locate;

				return true;
			}
		} else {
			// Assuming ABSPATH is writable
			$this->fileLocation = $this->getFileLocation;
		}

		return false;
	}

	/**
	 * @return bool|mixed
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
	 * @param $sxi \SimpleXmlIterator
	 * @param null $key
	 * @param null $tmp
	 *
	 * @return null
	 */
	protected function sxiToXpath( $sxi, $key = null, &$tmp = null ) {
		$keys_arr = array();
		//get the keys count array
		for ( $sxi->rewind(); $sxi->valid(); $sxi->next() ) {
			$sk = $sxi->key();
			if ( array_key_exists( $sk, $keys_arr ) ) {
				$keys_arr[ $sk ] += 1;
				$keys_arr[ $sk ] = $keys_arr[ $sk ];
			} else {
				$keys_arr[ $sk ] = 1;
			}
		}
		//create the xpath
		for ( $sxi->rewind(); $sxi->valid(); $sxi->next() ) {
			$sk = $sxi->key();
			if ( ! isset( $$sk ) ) {
				$$sk = 1;
			}
			if ( $keys_arr[ $sk ] >= 1 ) {
				$spk             = $sk . '[' . $$sk . ']';
				$keys_arr[ $sk ] = $keys_arr[ $sk ] - 1;
				$$sk ++;
			} else {
				$spk = $sk;
			}
			$kp = $key ? $key . '/' . $spk : '/' . $sxi->getName() . '/' . $spk;
			if ( $sxi->hasChildren() ) {
				$this->sxiToXpath( $sxi->getChildren(), $kp, $tmp );
			} else {
				$tmp[ $kp ] = strval( $sxi->current() );
			}
			$at = $sxi->current()->attributes();
			if ( $at ) {
				$tmp_kp = $kp;
				foreach ( $at as $k => $v ) {
					$kp .= '/@' . $k;
					$tmp[ $kp ] = $v;
					$kp         = $tmp_kp;
				}
			}
		}

		return $tmp;
	}

	/**
	 * Transform an SimpleXMLElement to Xpath and return it
	 *
	 * @param $xml
	 *
	 * @return null|array
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 0.1
	 */
	public function xmlToXpath( $xml ) {
		$sxi = new \SimpleXmlIterator( $xml );

		return $this->sxiToXpath( $sxi );
	}

	/**
	 * Print SimpleXMLElement $this->simpleXML to screen
	 *
	 * @throws exception
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 0.1
	 */
	public function printXML() {
		if ( ! ( $this->simpleXML instanceof \SimpleXMLExtended ) ) {
			return;
		}

//		$this->©header->content_encoding( 'text/xml' ); TODO we must set headers
		if(!headers_sent()){
			header('Content-Type: application/xml; charset=utf-8');
		}

		echo $this->simpleXML->asXML();
	}

	/**
	 * Returns the file location based on settings (even if it isn't exists)
	 *
	 * @return string
	 * @throws exception
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 0.1
	 */
	public function getFileLocation() {
		$opts = HelperSkroutzOptions::Instance();
		$location =  trim($opts->getValue( 'xml_location' ), '\\/ ' );
		$fileName = $opts->getValue( 'xml_fileName' );

		return rtrim( _PS_ROOT_DIR_, '\\/' ) . '/' . (empty($location) ? '' : $location . '/' ) . rtrim(ltrim($fileName, '\\/'), '\\/');
	}

	/**
	 * Get XML file info
	 *
	 * @return array|null
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 0.1
	 */
	public function getFileInfo() {
		$fileLocation = $this->getFileLocation();

		if ( $this->existsAndReadable( $fileLocation ) ) {
			$info = array();

			$sXML = simplexml_load_file( $fileLocation );
			$cratedAtName = $this->createdAtName;

			$info[ 'File Creation Datetime' ] = end( $sXML->$cratedAtName );
			$info['Products Count']       = $this->countProductsInFile( $sXML );
			$info['File Path']            = $fileLocation;
//			$info['File Url']             = $this->©url->to_wp_site_uri( str_replace( ABSPATH, '', $fileLocation ) ); TODO File URL
			$info['File Size']            = filesize( $fileLocation );

			return $info;
		} else {
			return null;
		}
	}

	/**
	 * Counts total products in file
	 *
	 * @param $file string|\SimpleXMLExtended|\SimpleXMLElement
	 *
	 * @return int Total products in file
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 0.1
	 */
	public function countProductsInFile( $file ) {
		if ( $this->existsAndReadable( $file ) ) {
			$sXML = simplexml_load_file( $file );
		} elseif ( $file instanceof \SimpleXMLElement || $file instanceof \SimpleXMLExtended ) {
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
	 * Checks if file exists and is readable
	 *
	 * @param $file string File location
	 *
	 * @return bool
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 0.1
	 */
	protected function existsAndReadable( $file ) {
		return is_string( $file ) && file_exists( $file ) && is_readable( $file );
	}

	/**
	 * Gets an XML attribute value.
	 *
	 * @param \SimpleXMLElement $xml A SimpleXMLElement object instance.
	 * @param string            $attribute The name of the attribute we're looking for.
	 *
	 * @return string The value of the attribute, else an empty string on failure.
	 *
	 * @throws exception If invalid types are passed through arguments list.
	 */
	public function attribute(SimpleXMLElement $xml, $attribute)
	{
		foreach($xml->attributes() as $_attribute => $_value)
			if(strcasecmp($_attribute, $attribute) === 0)
				return (string)$_value;
		unset($_attribute, $_value);

		return '';
	}
}