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
		$this->bootstrap = true;
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
			$newOptions = $_POST;

			$this->loader->loadHelper('SkroutzOptions');
			$skzOpts = HelperSkroutzOptions::Instance();
			if($skzOpts->saveOptions($newOptions)) {
				$output .= $this->displayConfirmation( $this->l( 'Settings updated' ) );
			} else {
				$output .= $this->displayError($this->l('There was an error saving options'));
			}
		}

		return $output . $this->displayForm();
	}

	public function displayForm()
	{
		$this->loader->loadHelper('SkroutzForm');
		$form = new HelperSkroutzForm();

		return $form->init($this)->generateForm();
	}

	/**
	 * @return bool
	 *
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since ${VERSION}
	 */
	public function install() {
		if(!$this->loader->loadHelper('SkroutzOptions')){
			return false;
		}
		HelperSkroutzOptions::Instance();
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
		if(!$this->loader->loadHelper('SkroutzOptions')){
			return false;
		}
		if (!parent::uninstall() ||
		    !HelperSkroutzOptions::Instance()->deleteAllOptions())
			return false;
		return true;
	}
}