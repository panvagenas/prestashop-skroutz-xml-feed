<?php
/**
 * Project: coremodule
 * File: Vars.php
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 19/11/2014
 * Time: 8:34 πμ
 * Since: 141110
 * Copyright: 2014 Panagiotis Vagenas
 */

namespace XDaRk;

if (!defined('_PS_VERSION_'))
	exit;


class Vars extends Core{
	/**
	 * Converts PHP variables into JavaScript.
	 *
	 * @note This follows JSON standards; except we use single quotes instead of double quotes.
	 *    Also, see {@link strings::esc_js_sq_deep()} for subtle differences when it comes to line breaks.
	 *    • Special handling for line breaks in strings: `\r\n` and `\r` are converted to `\n`.
	 *
	 * @param mixed   $var Any input variable (or an expression is fine also).
	 * @param boolean $___recursion Internal use only.
	 *
	 * @return string JavaScript value (w/ string representation).
	 *
	 * @throws exception If invalid types are passed through arguments list.
	 */
	public function to_js($var, $___recursion = FALSE)
	{
		if(!$___recursion) // Only for the initial caller.
			$this->check_arg_types('', 'boolean', func_get_args());

		switch(($type = gettype($var))) // Based on type.
		{
			case 'object': // Iterates each object property.
			case 'array': // Or, each array key (if this is an array).

				if($type === 'array' && (empty($var) || array_keys($var) === range(0, count($var) - 1)))
					$js_value = '['.implode(',', array_map(array($this, __FUNCTION__), $var, array_fill(0, count($var), TRUE))).']';

				else // It's an object; or an associative array (which is converted to an object).
				{
					$_nested_key_props = array(); // Initialize.

					foreach($var as $_nested_key_prop => &$_nested_value)
						$_nested_key_props[] = "'".$this->String->esc_js_sq((string)$_nested_key_prop)."':".$this->to_js($_nested_value, TRUE);
					$js_value = '{'.implode(',', $_nested_key_props).'}';

					unset($_nested_key_prop, $_nested_value, $_nested_key_props); // Housekeeping.
				}
				break; // Break switch.

			// Everything else is MUCH simpler to handle.

			case 'integer':
				$js_value = (string)$var;
				break; // Break switch.

			case 'real': // Alias for `float` type.
			case 'double': // Alias for `float` type.
			case 'float': // Standardized as `float` type.
				$js_value = (string)$var;
				break; // Break switch.

			case 'string':
				$js_value = "'".$this->String->esc_js_sq($var)."'";
				break; // Break switch.

			case 'boolean':
				$js_value = ($var) ? 'true' : 'false';
				break; // Break switch.

			case 'resource':
				$js_value = "'".$this->String->esc_js_sq((string)$var)."'";
				break; // Break switch.

			case 'NULL':
				$js_value = 'null';
				break; // Break switch.

			default: // Default case handler.
				$js_value = "'".$this->String->esc_js_sq((string)$var)."'";
				break; // Break switch.
		}
		return $js_value; // JavaScript value.
	}
} 