<?php
/**
 * Project: coremodule
 * File: String.php
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 19/11/2014
 * Time: 8:36 πμ
 * Since: 141110
 * Copyright: 2014 Panagiotis Vagenas
 */

namespace XDaRk;

if (!defined('_PS_VERSION_'))
	exit;

class String extends Core{
	/**
	 * Escapes JS line breaks (removes "\r"); and escapes single quotes.
	 *
	 * @param string  $string A string value.
	 * @param integer $times Number of escapes. Defaults to `1`.
	 *
	 * @return string Escaped string, ready for JavaScript.
	 *
	 * @throws exception If invalid types are passed through arguments list.
	 */
	public function esc_js_sq($string, $times = 1)
	{
		$this->check_arg_types('string', 'integer', func_get_args());

		return $this->esc_js_sq_deep($string, $times);
	}

	/**
	 * Escapes JS; and escapes single quotes deeply.
	 *
	 * @note This is a recursive scan running deeply into multiple dimensions of arrays/objects.
	 * @note This routine will usually NOT include private, protected or static properties of an object class.
	 *    However, private/protected properties *will* be included, if the current scope allows access to these private/protected properties.
	 *    Static properties are NEVER considered by this routine, because static properties are NOT iterated by `foreach()`.
	 *
	 * @note This follows {@link http://www.json.org JSON} standards, with TWO exceptions.
	 *    1. Special handling for line breaks: `\r\n` and `\r` are converted to `\n`.
	 *    2. This does NOT escape double quotes; only single quotes.
	 *
	 * @param mixed   $value Any value can be converted into an escaped string.
	 *    Actually, objects can't, but this recurses into objects.
	 *
	 * @param integer $times Number of escapes. Defaults to `1`.
	 *
	 * @param boolean $___recursion Internal use only.
	 *
	 * @return string|array|object Escaped string, array, object (ready for JavaScript).
	 *
	 * @throws exception If invalid types are passed through arguments list.
	 */
	public function esc_js_sq_deep($value, $times = 1, $___recursion = FALSE)
	{
		if(!$___recursion) // Only for the initial caller.
			$this->check_arg_types('', 'integer', 'boolean', func_get_args());

		if(is_array($value) || is_object($value))
		{
			foreach($value as &$_value)
				$_value = $this->esc_js_sq_deep($_value, $times, TRUE);
			return $value;
		}
		$value = str_replace(array("\r\n", "\r", '"'), array("\n", "\n", '%%!dq!%%'), (string)$value);
		$value = str_replace(array('%%!dq!%%', "'"), array('"', "\\'"), trim(json_encode($value), '"'));

		return str_replace('\\', str_repeat('\\', abs($times) - 1).'\\', $value);
	}
} 