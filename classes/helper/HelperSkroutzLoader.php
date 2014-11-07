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

		if(class_exists('Helper' . ((string)$helperName))){
			return true;
		}

		if(!file_exists($path)){
			return null;
		}

		require_once $path;
		return true;
	}

	public function loadInclude($includedName){
		$path = dirname(dirname(__FILE__)) . '/includes/' . ((string)$includedName) . '.php';

		if(class_exists((string)$includedName)){
			return true;
		}

		if(!file_exists($path)){
			return null;
		}

		require_once $path;
		return true;
	}
}