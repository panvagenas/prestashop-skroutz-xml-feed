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

namespace XDaRk_v150216;

if (!defined('_PS_VERSION_'))
	exit;

class String extends Core
{
	/**
	 * Escapes JS line breaks (removes "\r"); and escapes single quotes.
	 *
	 * @param string $string A string value.
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
	 * Should a variable be interpreted as TRUE?
	 *
	 * @param mixed $var Any value to test against here.
	 *
	 * @return boolean TRUE for: TRUE, 'TRUE', 'true', 1, '1', 'on', 'ON', 'yes', 'YES' — else FALSE.
	 *    Any resource/object/array is of course NOT one of these values, and will always return FALSE.
	 *    In other words, any value that is NOT scalar, is NOT TRUE.
	 */
	public function is_true($var)
	{
		return is_scalar($var) && filter_var($var, FILTER_VALIDATE_BOOLEAN);
	}

	/**
	 * Short version of `(isset() && is_string())`.
	 *
	 * @note Unlike PHP's `is_...` functions, this will NOT throw a NOTICE.
	 *
	 * @param mixed $var A variable by reference (no NOTICE).
	 *    If `$var` is NOT already set, it will be set to NULL by PHP, as a result of passing it by reference.
	 *
	 * @return boolean TRUE if the variable `(isset() && is_string())`.
	 */
	public function is(&$var)
	{
		return is_string($var);
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
	 * @param mixed $value Any value can be converted into an escaped string.
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
	public function esc_js_sq_deep($value, $times = 1, $___recursion = false)
	{
		if (!$___recursion) // Only for the initial caller.
			$this->check_arg_types('', 'integer', 'boolean', func_get_args());

		if (is_array($value) || is_object($value))
		{
			foreach ($value as &$_value)
				$_value = $this->esc_js_sq_deep($_value, $times, true);
			return $value;
		}
		$value = str_replace(array("\r\n", "\r", '"'), array("\n", "\n", '%%!dq!%%'), (string)$value);
		$value = str_replace(array('%%!dq!%%', "'"), array('"', "\\'"), trim(json_encode($value), '"'));

		return str_replace('\\', str_repeat('\\', abs($times) - 1).'\\', $value);
	}

	/**
	 * Short version of `(!empty() && is_string())`.
	 *
	 * @note Unlike PHP's `is_...` functions, this will NOT throw a NOTICE.
	 *
	 * @param mixed $var A variable by reference (no NOTICE).
	 *    If `$var` is NOT already set, it will be set to NULL by PHP, as a result of passing it by reference.
	 *
	 * @return boolean TRUE if the variable is `(!empty() && is_string())`.
	 */
	public function is_not_empty(&$var)
	{
		return !empty($var) && is_string($var);
	}

	/**
	 * Escapes regex backreference chars (i.e. `\\$` and `\\\\`).
	 *
	 * @param string $string A string value.
	 * @param integer $times Number of escapes. Defaults to `1`.
	 *
	 * @return string Escaped string.
	 *
	 * @throws exception If invalid types are passed through arguments list.
	 */
	public function esc_refs($string, $times = 1)
	{
		$this->check_arg_types('string', 'integer', func_get_args());

		return $this->esc_refs_deep($string, $times);
	}

	/**
	 * Escapes regex backreference chars deeply (i.e. `\\$` and `\\\\`).
	 *
	 * @note This is a recursive scan running deeply into multiple dimensions of arrays/objects.
	 * @note This routine will usually NOT include private, protected or static properties of an object class.
	 *    However, private/protected properties *will* be included, if the current scope allows access to these private/protected properties.
	 *    Static properties are NEVER considered by this routine, because static properties are NOT iterated by `foreach()`.
	 *
	 * @param mixed $value Any value can be converted into an escaped string.
	 *    Actually, objects can't, but this recurses into objects.
	 *
	 * @param integer $times Number of escapes. Defaults to `1`.
	 *
	 * @param boolean $___recursion Internal use only.
	 *
	 * @return string|array|object Escaped string, array, object.
	 *
	 * @throws exception If invalid types are passed through arguments list.
	 */
	public function esc_refs_deep($value, $times = 1, $___recursion = false)
	{
		if (!$___recursion) // Only for the initial caller.
			$this->check_arg_types('', 'integer', 'boolean', func_get_args());

		if (is_array($value) || is_object($value))
		{
			foreach ($value as &$_value)
				$_value = $this->esc_refs_deep($_value, $times, true);
			return $value;
		}
		return str_replace(array('\\', '$'), array(str_repeat('\\', abs($times)).'\\', str_repeat('\\', abs($times)).'$'), (string)$value);
	}

	/**
	 * Generates a random string with letters/numbers/symbols.
	 *
	 * @param integer $length Optional. Defaults to `12`.
	 *    Length of the random string.
	 *
	 * @param boolean $special_chars Defaults to TRUE.
	 *    If FALSE, special chars are NOT included.
	 *
	 * @param boolean $extra_special_chars Defaults to FALSE.
	 *    If TRUE, extra special chars are included.
	 *
	 * @return string A randomly generated string, based on configuration.
	 *
	 * @throws exception If invalid types are passed through arguments list.
	 */
	public function random($length = 12, $special_chars = true, $extra_special_chars = false)
	{
		$this->check_arg_types('integer', 'boolean', 'boolean', func_get_args());

		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$chars .= ($special_chars) ? '!@#$%^&*()' : '';
		$chars .= ($extra_special_chars) ? '-_ []{}<>~`+=,.;:/?|' : '';

		for ($i = 0, $random_str = ''; $i < abs($length); $i++)
			$random_str .= (string)substr($chars, mt_rand(0, strlen($chars) - 1), 1);

		return $random_str;
	}

	/**
	 * Check if string values are NOT empty.
	 *
	 * @note Unlike PHP's `is_...` functions, this will NOT throw a NOTICE.
	 *
	 * @note Max 26 arguments by reference. If vars are/were NOT already set,
	 *    they will be set to NULL by PHP, as a result of passing them by reference.
	 *    Starting with argument #27, vars cannot be passed by reference.
	 *
	 * @param mixed $a
	 * @param mixed $b
	 * @param mixed $c
	 * @param mixed $d
	 * @param mixed $e
	 * @param mixed $f
	 * @param mixed $g
	 * @param mixed $h
	 * @param mixed $i
	 * @param mixed $j
	 * @param mixed $k
	 * @param mixed $l
	 * @param mixed $m
	 * @param mixed $n
	 * @param mixed $o
	 * @param mixed $p
	 * @param mixed $q
	 * @param mixed $r
	 * @param mixed $s
	 * @param mixed $t
	 * @param mixed $u
	 * @param mixed $v
	 * @param mixed $w
	 * @param mixed $x
	 * @param mixed $y
	 * @param mixed $z
	 * @params-variable-length
	 *
	 * @return boolean TRUE if all arguments are strings, and they're NOT empty.
	 */
	public function are_not_empty(&$a, &$b = null, &$c = null, &$d = null, &$e = null, &$f = null, &$g = null, &$h = null, &$i = null, &$j = null, &$k = null, &$l = null, &$m = null, &$n = null, &$o = null, &$p = null, &$q = null, &$r = null, &$s = null, &$t = null, &$u = null, &$v = null, &$w = null, &$x = null, &$y = null, &$z = null)
	{
		foreach (func_get_args() as $_arg)
			if (empty($_arg) || !is_string($_arg))
				return false;

		return true;
	}
} 