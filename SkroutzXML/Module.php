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

if ( ! defined( '_PS_VERSION_' ) ) {
	exit;
}
require_once dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'Module.php';

class Module extends \XDaRk\Module{
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
	 * @return string
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150202
	 */
	protected function xdGetContent() {
		return '';

	}
}

$GLOBALS['skroutzxmlfeed'] = array(
	'root_ns' => __NAMESPACE__,
	'var_ns'  => 'skz',
	'dir'     => dirname( dirname( __FILE__ ) )
);