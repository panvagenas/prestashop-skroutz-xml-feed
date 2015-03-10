<?php
/**
 * Project: skroutzxmlfeed
 * File: Module.php
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 13/2/2015
 * Time: 10:10 πμ
 * Since: 150213
 * Copyright: 2015 Panagiotis Vagenas
 */

namespace SkroutzXML;

use SkroutzXML\Panels\Info;
use SkroutzXML\Panels\MainOptions;
use SkroutzXML\Panels\MapOptions;

if ( ! defined( '_PS_VERSION_' ) ) {
	exit;
}
require_once dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'Core.php';

class Module extends \XDaRk_v150216\Module{
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
	public $version = '150213';
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
	 * @var bool
	 */
	public $bootstrap = true;

	/**
	 * @throws \Exception
	 */
	public function __construct()
	{
		parent::__construct();

		$requestVarValue = $this->Options->getValue('request_var_value');
		if(empty($requestVarValue)){
			$this->Options->saveOptions(array('request_var_value' => uniqid().uniqid()));
		}
	}

	/**
	 * @return string
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150202
	 */
	protected function xdGetContent() {
		return $this->Form
			->registerPanel(new MainOptions($this))
			->registerPanel(new MapOptions($this))
			->registerPanel(new Info($this))
			->initialize($this)
			->generateForm($this->Options->getOptionsArray());
	}
}
/***********************************************
* Init core module specs
***********************************************/
$GLOBALS['skroutzxmlfeed'] = array(
	'root_ns' => __NAMESPACE__,
	'var_ns'  => 'skz',
	'dir'     => dirname( dirname( __FILE__ ) )
);