<?php
/**
 * Project: coremodule
 * File: CarrierModule.php
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 14/11/2014
 * Time: 12:25 πμ
 * Since: 141110
 * Copyright: 2014 Panagiotis Vagenas
 */

namespace XDaRk;

if (!defined('_PS_VERSION_'))
	exit;

if(!class_exists('\XDaRk\CarrierModule')){
	abstract class CarrierModule extends Module {
		public $id_carrier;

		abstract public function getOrderShippingCost($params, $shipping_cost);

		abstract public function getOrderShippingCostExternal($params);
	}
}
