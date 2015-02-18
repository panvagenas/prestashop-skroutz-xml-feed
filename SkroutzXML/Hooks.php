<?php
/**
 * Project: skroutzxmlfeed
 * File: Hooks.php
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 13/2/2015
 * Time: 10:24 πμ
 * Since: 150213
 * Copyright: 2015 Panagiotis Vagenas
 */

namespace SkroutzXML;

/**
 * Class Hooks
 * @package SkroutzXML
 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
 * @since 150213
 *
 * @property \SkroutzXML\Skroutz    Skroutz
 * @property \SkroutzXML\XML        XML
 */
class Hooks extends \XDaRk_v141110\Hooks{
	/**
	 * @param $p
	 *
	 * @throws \Exception
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since TODO ${VERSION}
	 */
	public function hookDisplayHeader( $p ) {
		$queryVars = $this->Vars->getQueryVars();
		if(isset($queryVars[$this->Options->getValue('request_var')]) && $queryVars[$this->Options->getValue('request_var')] === $this->Options->getValue('request_var_value')){
			$this->Skroutz->printXMLFile();
		}
	}
}