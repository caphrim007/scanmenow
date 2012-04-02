<?php

/**
* Normalizes a URL.
*
* Borrows heavily from the similar functionality available
* in the PEAR Net_URL2 package.
*
* @author Tim Rupp <caphrim007@gmail.com>
*/
class App_View_Helper_NormalizeUrl extends Zend_View_Helper_Abstract {
	/**
	* @var  string|bool
	*/
	private $_scheme = false;

	/**
	* @var  string|bool
	*/
	private $_userinfo = false;

	/**
	* @var  string|bool
	*/
	private $_host = false;

	/**
	* @var  string|bool
	*/
	private $_port = false;

	/**
	* @var  string
	*/
	private $_path = '';

	/**
	* @var  string|bool
	*/
	private $_query = false;

	/**
	* @var  string|bool
	*/
	private $_fragment = false;

	public function normalizeUrl($url) {
		// The regular expression is copied verbatim from RFC 3986, appendix B.
		// The expression does not validate the URL but matches any string.
		preg_match('!^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?!', $url, $matches);

		// "path" is always present (possibly as an empty string); the rest
		// are optional.
		$this->_scheme = !empty($matches[1]) ? $matches[2] : false;
		$this->setAuthority(!empty($matches[3]) ? $matches[4] : false);
		$this->_path = $matches[5];
		$this->_query = !empty($matches[6]) ? $matches[7] : false;
		$this->_fragment = !empty($matches[8]) ? $matches[9] : false;

		$this->normalize();
		return $this->getUrl();
	}

	/**
	* Magic Setter.
	*
	* This method will magically set the value of a private variable ($var)
	* with the value passed as the args
	*
	* @param  string $var      The private variable to set.
	* @param  mixed  $arg      An argument of any type.
	* @return void
	*/
	public function __set($var, $arg) {
		$method = 'set' . $var;
		if (method_exists($this, $method)) {
			$this->$method($arg);
		}
	}
    
	/**
	* Magic Getter.
	*
	* This is the magic get method to retrieve the private variable 
	* that was set by either __set() or it's setter...
	* 
	* @param  string $var         The property name to retrieve.
	* @return mixed  $this->$var  Either a boolean false if the
	*                             property is not set or the value
	*                             of the private property.
	*/
	public function __get($var) {
		$method = 'get' . $var;
		if (method_exists($this, $method)) {
			return $this->$method();
		}
        
		return false;
	}
    
	/**
	* Returns the authority part, i.e. [ userinfo "@" ] host [ ":" port ], or
	* false if there is no authority.
	*
	* @return string|bool
	*/
	public function getAuthority() {
		if (!$this->_host) {
			return false;
		}

		$authority = '';

		if ($this->_userinfo !== false) {
			$authority .= $this->_userinfo . '@';
		}

		$authority .= $this->_host;

		if ($this->_port !== false) {
			$authority .= ':' . $this->_port;
		}

		return $authority;
	}

	/**
	* Sets the authority part, i.e. [ userinfo "@" ] host [ ":" port ]. Specify
	* false if there is no authority.
	*
	* @param string|false $authority a hostname or an IP addresse, possibly
	*                                with userinfo prefixed and port number
	*                                appended, e.g. "foo:bar@example.org:81".
	*
	* @return void
	*/
	public function setAuthority($authority) {
		$this->_userinfo = false;
		$this->_host     = false;
		$this->_port     = false;
		if (preg_match('@^(([^\@]*)\@)?([^:]+)(:(\d*))?$@', $authority, $reg)) {
			if ($reg[1]) {
				$this->_userinfo = $reg[2];
			}

			$this->_host = $reg[3];
			if (isset($reg[5])) {
				$this->_port = $reg[5];
			}
		}
	}

	/**
	* Returns a string representation of this URL.
	*
	* @return  string
	*/
	public function getURL() {
		// See RFC 3986, section 5.3
		$url = '';
		$remainder = '';

		if ($this->_scheme !== false) {
			$url .= $this->_scheme . ':';
		}

		$authority = $this->getAuthority();
		if ($authority !== false) {
			if (substr($authority, -1) == '/') {
				$url .= '//' . substr($authority, 0, -1);
			} else {
				$url .= '//' . $authority;
			}
		}

		$remainder = $this->_path;

		if ($this->_query !== false) {
			$remainder .= '?' . $this->_query;
		}

		if ($this->_fragment !== false) {
			$remainder .= '#' . $this->_fragment;
		}

		$remainder = preg_quote($remainder, '|');
		$remainder = preg_replace('|\/\/+|', ' ', $remainder);

		$remainder = trim($remainder);

		// preg_quote adds slashes back. This removes them
		$remainder = stripslashes($remainder);

		$url .= '/' . $remainder;

		return $url;
	}

	/** 
	* Returns a normalized Net_URL2 instance.
	*
	* @return  Net_URL2
	*/
	public function normalize() {
		// See RFC 3886, section 6

		// Schemes are case-insensitive
		if ($this->_scheme) {
			$this->_scheme = strtolower($this->_scheme);
		}

		// Hostnames are case-insensitive
		if ($this->_host) {
			$this->_host = strtolower($this->_host);
		}

		// Remove default port number for known schemes (RFC 3986, section 6.2.3)
		if ($this->_port && $this->_scheme && $this->_port == getservbyname($this->_scheme, 'tcp')) {
			$this->_port = false;
		}

		// Normalize case of %XX percentage-encodings (RFC 3986, section 6.2.2.1)
		foreach (array('_userinfo', '_host', '_path') as $part) {
			if ($this->$part) {
				$this->$part = preg_replace('/%[0-9a-f]{2}/ie', 'strtoupper("\0")', $this->$part);
			}
		}

		// Path segment normalization (RFC 3986, section 6.2.2.3)
		$this->_path = self::removeDotSegments($this->_path);

		// Scheme based normalization (RFC 3986, section 6.2.3)
		if ($this->_host && !$this->_path) {
			$this->_path = '/';
		}
	}

	/**
	* Removes dots as described in RFC 3986, section 5.2.4, e.g.
	* "/foo/../bar/baz" => "/bar/baz"
	*
	* @param string $path a path
	*
	* @return string a path
	*/
	public static function removeDotSegments($path) {
		$output = '';

		// Make sure not to be trapped in an infinite loop due to a bug in this
		// method
		$j = 0; 
		while ($path && $j++ < 100) {
			if (substr($path, 0, 2) == './') {
				// Step 2.A
				$path = substr($path, 2);
			} elseif (substr($path, 0, 3) == '../') {
				// Step 2.A
				$path = substr($path, 3);
			} elseif (substr($path, 0, 3) == '/./' || $path == '/.') {
				// Step 2.B
				$path = '/' . substr($path, 3);
			} elseif (substr($path, 0, 4) == '/../' || $path == '/..') {
				// Step 2.C
				$path   = '/' . substr($path, 4);
				$i      = strrpos($output, '/');
				$output = $i === false ? '' : substr($output, 0, $i);
			} elseif ($path == '.' || $path == '..') {
				// Step 2.D
				$path = '';
			} else {
				// Step 2.E
				$i = strpos($path, '/');
				if ($i === 0) {
					$i = strpos($path, '/', 1);
				}
				if ($i === false) {
					$i = strlen($path);
				}
				$output .= substr($path, 0, $i);
				$path = substr($path, $i);
			}
		}

		return $output;
	}
}

?>
