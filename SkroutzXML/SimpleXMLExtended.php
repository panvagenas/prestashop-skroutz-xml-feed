<?php
/**
 * Project: skroutzxmlfeed
 * File: SimpleXMLExtended.php
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 14/2/2015
 * Time: 6:11 μμ
 * Since: 150213
 * Copyright: 2015 Panagiotis Vagenas
 */

namespace SkroutzXML;

/**
 * Class SimpleXMLExtended extends SimpleXMLElement so CDATA con be added without encoding
 */
class SimpleXMLExtended extends \SimpleXMLElement {
	/**
	 * @param $cdata_text
	 *
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	public function addCData($cdata_text) {
		$node = dom_import_simplexml($this);
		$no   = $node->ownerDocument;
		$node->appendChild($no->createCDATASection($cdata_text));
	}
}