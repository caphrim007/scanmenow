<?php

/**
* Returns a URL that only includes the scheme and authority
*
* @author Tim Rupp <caphrim007@gmail.com>
*/
class App_View_Helper_GetSchemeAuthority extends Zend_View_Helper_Abstract {
	public function getSchemeAuthority() {
		$result = '';

		if (isset($_SERVER['HTTPS'])) {
			$uri = 'https://';
		} else {
			$uri = 'http://';
		}

		$uri .= $_SERVER['HTTP_HOST']  . $_SERVER['REQUEST_URI'];

		$url = Zend_Uri::factory($uri);
		$url->setPath('');
		$url->setQuery('');
		$url->setFragment('');

		return $url->getUri();
	}
}

?>
