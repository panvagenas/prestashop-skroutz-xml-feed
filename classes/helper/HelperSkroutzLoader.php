<?php
/**
 * skroutzxmlfeed
 * ${FILE_NAME}
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 6/11/2014
 * Time: 2:10 μμ
 * Copyright: 2014 Panagiotis Vagenas
 */
if (!defined('_PS_VERSION_'))
  exit;
  
class HelperSkroutzLoader extends Helper {
	public function loadHelper($helperName){
		$path = dirname(__FILE__) . '/Helper' . ((string)$helperName) . '.php';

		if(!file_exists($path)){
			return null;
		}

		require_once $path;
	}
}