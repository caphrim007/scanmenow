<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    App_Service
 * @subpackage Bitly
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Bitly.php 19096 2009-11-20 15:33:15Z sidhighwind $
 */
/**
 * @see Zend_Rest_Client
 */
require_once 'Zend/Rest/Client.php';
/**
 * @see Zend_Rest_Client_Result
 */
require_once 'Zend/Rest/Client/Result.php';
/**
 * @category   Zend
 * @package    App_Service
 * @subpackage Bitly
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class App_Service_Bitly extends Zend_Rest_Client
{
    /**
     * All APIs require authentication credentials supplied as query arguments
     */
    protected $_login = null;
    protected $_apiKey = null;
    /**
     * All APIs require a version identifier to be present.
     */
    protected $_version = '2.0.1';
    /**
     * All APIs support an optional return format specifier.
     * json is the default response format, xml is also available
     */
    protected $_format = 'xml';
    /**
     * Types of return formats
     */
    protected $_formatTypes = array('json', 'xml');
    /**
     * Whether or not authorization has been initialized for the current user.
     * @var bool
     */
    protected $_authInitialized = false;
    /**
     * @var Zend_Http_CookieJar
     */
    protected $_cookieJar;
    /**
     * Username
     * @var string
     */
    protected $_username;
    /**
     * Password
     * @var string
     */
    protected $_password;
    /**
     * Current method type (for method proxying)
     * @var string
     */
    protected $_methodType;
    /**
     * Types of API methods
     * @var array
     */
    protected $_methodTypes = array('shorten', 'expand', 'info', 'stats', 'errors');

    /**
     * Local HTTP Client cloned from statically set client
     * @var Zend_Http_Client
     */
    protected $_localHttpClient = null;

    /**
     * Holds list of parameters to be concatenated during an api call
     */
    protected $_params = array();

    /**
     * Constructor
     *
     * @param  string $username
     * @param  string $password
     * @return void
     */
    public function __construct($login = null, $apiKey = null)
    {
        $this->setLocalHttpClient(clone self::getHttpClient());
        if (is_array($login) && is_null($apiKey)) {
            if (isset($login['login']) && isset($login['apiKey'])) {
                $this->setLogin($login['login']);
                $this->setApiKey($login['apiKey']);
            } elseif (isset($login[0]) && isset($login[1])) {
                $this->setLogin($login[0]);
                $this->setApiKey($login[1]);
            }
        } else if (!is_null($login)) {
            $this->setLogin($login);
            $this->setApiKey($apiKey);
        }
        $this->setUri('http://api.bit.ly');
        $this->_localHttpClient->setHeaders('Accept-Charset', 'ISO-8859-1,utf-8');
    }

    /**
     * Set local HTTP client as distinct from the static HTTP client
     * as inherited from Zend_Rest_Client.
     *
     * @param Zend_Http_Client $client
     * @return self
     */
    public function setLocalHttpClient(Zend_Http_Client $client)
    {
        $this->_localHttpClient = $client;
        return $this;
    }

    public function getLocalHttpClient()
    {
        return $this->_localHttpClient;
    }

    /**
     * Retrieve login
     *
     * @return string
     */
    public function getLogin()
    {
        return $this->_login;
    }

    /**
     * Set login
     *
     * @param  string $value
     * @return App_Service_Bitly
     */
    public function setLogin($value)
    {
        $this->_login = $value;
        $this->_authInitialized = false;
        return $this;
    }

    /**
     * Retrieve apiKey
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->_apiKey;
    }

    /**
     * Set apiKey
     *
     * @param  string $value
     * @return App_Service_Bitly
     */
    public function setApiKey($value)
    {
        $this->_apiKey = $value;
        $this->_authInitialized = false;
        return $this;
    }

    /**
     * Proxy service methods
     *
     * @param  string $type
     * @return App_Service_Bitly
     * @throws App_Service_Bitly_Exception if method is not in method types list
     */
    public function __get($type)
    {
        if (!in_array($type, $this->_methodTypes)) {
            include_once 'Zend/Service/Bitly/Exception.php';
            throw new App_Service_Bitly_Exception('Invalid method type "' . $type . '"');
        }
        $this->_methodType = $type;
        return $this;
    }

    /**
     * Initialize HTTP authentication
     *
     * @return void
     */
    protected function _init()
    {
        $client = $this->_localHttpClient;
        $client->resetParameters();
        if (null == $this->_cookieJar) {
            $client->setCookieJar();
            $this->_cookieJar = $client->getCookieJar();
        } else {
            $client->setCookieJar($this->_cookieJar);
        }
        if (!$this->_authInitialized && $this->getApiKey() !== null) {
            $this->_authInitialized = true;
	    $this->_params = array(
                'format' => $this->_format,
                'version' => $this->_version,
		'login' => $this->_login,
		'apiKey' => $this->_apiKey
            );
        }
    }

    /**
     * Given a long URL, /shorten encodes it as a shorter one and returns it.
     *
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function shorten($longUrl)
    {
	$query = null;
        $this->_init();
        $path = '/shorten';

	$_params = $this->_params;
	$_params['longUrl'] = $longUrl;

        $response = $this->_get($path, $_params);
	if ($this->_format == 'json') {
		return $response->getBody();
	} else {
	        return new Zend_Rest_Client_Result($response->getBody());
	}
    }

    public function expand(array $params = array())
    {
	$query = null;
        $this->_init();
        $path = '/expand';

	$_params = $this->_params;
	$_params['longUrl'] = $longUrl;
        foreach ($params as $key => $value) {
            switch (strtolower($key)) {
                case 'shorturl':
                    $_params['shortUrl'] = $value;
                    break;
                case 'hash':
                    $_params['hash'] = $value;
                    break;
                default:
                    break;
            }
        }

        $response = $this->_get($path, $_params);
	if ($this->_format == 'json') {
		return $response->getBody();
	} else {
	        return new Zend_Rest_Client_Result($response->getBody());
	}
    }

    public function info(array $params = array())
    {
	$query = null;
        $this->_init();
        $path = '/info';

	$_params = $this->_params;
	$_params['longUrl'] = $longUrl;
        foreach ($params as $key => $value) {
            switch (strtolower($key)) {
                case 'shorturl':
                    $_params['shortUrl'] = $value;
                    break;
                case 'hash':
                    $_params['hash'] = $value;
                    break;
		case 'keys':
		    $_params['keys'] = $value;
                default:
                    break;
            }
        }

        $response = $this->_get($path, $_params);
	if ($this->_format == 'json') {
		return $response->getBody();
	} else {
	        return new Zend_Rest_Client_Result($response->getBody());
	}
    }

    public function stats(array $params = array())
    {
	$query = null;
        $this->_init();
        $path = '/stats';

	$_params = $this->_params;
	$_params['longUrl'] = $longUrl;
        foreach ($params as $key => $value) {
            switch (strtolower($key)) {
                case 'shorturl':
                    $_params['shortUrl'] = $value;
                    break;
                case 'hash':
                    $_params['hash'] = $value;
                    break;
                default:
                    break;
            }
        }

        $response = $this->_get($path, $_params);
	if ($this->_format == 'json') {
		return $response->getBody();
	} else {
	        return new Zend_Rest_Client_Result($response->getBody());
	}
    }

    public function errors()
    {
	$query = null;
        $this->_init();
        $path = '/errors';

        $response = $this->_get($path, $this->_params);
	if ($this->_format == 'json') {
		return $response->getBody();
	} else {
	        return new Zend_Rest_Client_Result($response->getBody());
	}
    }

    /**
     * Call a remote REST web service URI and return the Zend_Http_Response object
     *
     * @param  string $path            The path to append to the URI
     * @throws Zend_Rest_Client_Exception
     * @return void
     */
    protected function _prepare($path)
    {
        // Get the URI object and configure it
        if (!$this->_uri instanceof Zend_Uri_Http) {
            require_once 'Zend/Rest/Client/Exception.php';
            throw new Zend_Rest_Client_Exception('URI object must be set before performing call');
        }

        $uri = $this->_uri->getUri();

        if ($path[0] != '/' && $uri[strlen($uri) - 1] != '/') {
            $path = '/' . $path;
        }

        $this->_uri->setPath($path);

        /**
         * Get the HTTP client and configure it for the endpoint URI.  Do this each time
         * because the Zend_Http_Client instance is shared among all Zend_Service_Abstract subclasses.
         */
        $this->_localHttpClient->resetParameters()->setUri($this->_uri);
    }

    /**
     * Performs an HTTP GET request to the $path.
     *
     * @param string $path
     * @param array  $query Array of GET parameters
     * @throws Zend_Http_Client_Exception
     * @return Zend_Http_Response
     */
    protected function _get($path, array $query = null)
    {
        $this->_prepare($path);
        $this->_localHttpClient->setParameterGet($query);
        return $this->_localHttpClient->request('GET');
    }
}
