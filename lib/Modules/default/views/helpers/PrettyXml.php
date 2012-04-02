<?php

/**
* @author Tim Rupp
*/
class App_View_Helper_PrettyXml extends Zend_View_Helper_Abstract {
	/**
	* Prettifies an XML string into a human-readable and indented work of art
	*
	* @param string $xml The XML as a string
	* @param boolean $html True if the output should be escaped (for use in HTML)
	* @link http://gdatatips.blogspot.com/2008/11/xml-php-pretty-printer.html
	*/
	public function prettyXml($xml, $html = false) {
		$xml_obj = new SimpleXMLElement($xml);
		$level = 4;
		$indent = 0; // current indentation level
		$pretty = array();
    
		// get an array containing each XML element
		$xml = explode("\n", preg_replace('/>\s*</', ">\n<", $xml_obj->asXML()));

		// shift off opening XML tag if present
		if (count($xml) && preg_match('/^<\?\s*xml/', $xml[0])) {
			$pretty[] = array_shift($xml);
		}

		foreach ($xml as $el) {
			if (preg_match('/^<([\w])+[^>\/]*>$/U', $el)) {
				// opening tag, increase indent
				$pretty[] = str_repeat(' ', $indent) . $el;
				$indent += $level;
			} else {
				if (preg_match('/^<\/.+>$/', $el)) {            
					$indent -= $level;  // closing tag, decrease indent
				}
				if ($indent < 0) {
					$indent += $level;
				}
				$pretty[] = str_repeat(' ', $indent) . $el;
			}
		}   

		$xml = implode("\n", $pretty);

		if ($html === true) {
			return htmlentities($xml);
		} else {
			return $xml;
		}

	}
}

?>
