<?php

/**
* @author Tim Rupp
*/
class App_Auth_Adapter_Fnal_Cert implements Zend_Auth_Adapter_Interface {
	protected $params = array();
	protected $certificate = null;

	/**
	* Hold messages for logging
	*
	* @var array
	*/
	protected $_messages = array();

	const IDENT = __CLASS__;

	/**
	* @return void
	*/
	public function __construct($params, $certificate = null) {
		if ($params instanceof Zend_Config) {
			$this->params = $params->toArray();
		} elseif (is_array($params)) {
			$this->params = $params;
		} else {
			throw new Zend_Auth_Adapter_Exception(sprintf('The options for %s must be an array', __CLASS__));
		}

		$this->certificate = $certificate;

		$this->_messages = array();
		$this->_messages[0] = ''; // reserved
		$this->_messages[1] = ''; // reserved
	}

	/**
	* @return Zend_Auth_Result
	*/
	public function authenticate() {
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);

		$log->debug('"authenticate" method called');

		if (empty($this->certificate)) {
			$log->err('Client certificate information was not supplied');
			throw new Zend_Auth_Adapter_Exception('Client certificate information was not supplied');
		}

		if (!file_exists($this->params['cafile'])) {
			$log->err('The CA file does not exist');
			throw new Zend_Auth_Adapter_Exception('The CA file does not exist');
		}

		if (!is_readable($this->params['cafile'])) {
			$log->err('The CA file cannot be read');
			throw new Zend_Auth_Adapter_Exception('The CA file cannot be read');
		}

		$clientCert = openssl_x509_parse($_SERVER['SSL_CLIENT_CERT']);
		$tempFile = tempnam(_ABSPATH.'/tmp/', 'cert_');

		file_put_contents($tempFile, $this->certificate);

		$cmd = sprintf($this->params['openssl'],
			$this->params['cafile'],
			$tempFile
		);

		$log->debug(sprintf('Running command "%s"', $cmd));
		$output = shell_exec($cmd);

		$output = preg_split('/(: )|[\r\n]/', $output);

		array_shift($output);
		array_pop($output);

		unlink($tempFile);

		if (@$output[0] == 'OK') {
			$log->debug('OpenSSL check was successful');

			if (isset($this->params['create'])) {
				if (empty($this->params['create'])) {
					$log->debug(sprintf('Authentication successful for subject "%s"', $clientCert['name']));
					return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $clientCert['name'], $this->_messages);
				}
			}

			if (Account_Util::getId($clientCert['name']) == 0) {
				$log->debug('Creating XML-RPC proxy to get principal name from CSTAPI');
				$rpc = new Zend_XmlRpc_Client($config->ws->api->cstapi->uri);
				$client = $rpc->getProxy();
				$username = $config->ws->api->cstapi->username;
				$password = $config->ws->api->cstapi->password;

				// CST API doesnt currently do authentication.
				// Change this code when it finally does
				// $token = $client->authorize->getToken($username, $password);

				try {
					$principal = $client->acct->getPrincipal($clientCert['name']);
					$accountId = Account_Util::getId($principal);

					if ($accountId == 0) {
						$log->debug(sprintf('Account "%s" does not exist in the database; creating it', $clientCert['name']));
						$accountId = Account_Util::create($principal);

						$account = new Account($accountId);
						$random = Zend_OpenId::randomBytes(32);
						$account->setPassword(md5($random));

						$log->debug('Creating new role for account based off of account name');
						$roleId = Role_Util::create($account->getUsername(), 'Default account role');
						$account->role->addRole($roleId);
						$account->setPrimaryRole($roleId);
						$result = $account->createAccountMapping($clientCert['name']);

						$servicePrincipals = $client->acct->getServicePrincipals($principal);
						if (empty($servicePrincipals)) {
							$log->debug('Did not find your Service realm principals via the CSTAPI');
						} else {
							foreach($servicePrincipals as $service) {
								$result = $account->createAccountMapping($service);
							}
						}

						$account = new Account($accountId);
					}
				} catch (Exception $error) {
					$log->err($error->getMessage());
					$log->err('CSTAPI May be unreachable. If this is the case and your account has not already been created, you will be unable to log in');
					$log->debug($rpc->getHttpClient()->getLastResponse()->getBody());
				}
			}

			$log->debug(sprintf('Authentication successful for subject "%s"', $clientCert['name']));
			return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $clientCert['name'], $this->_messages);
		} else {
			$log->debug(sprintf('Authentication failed for subject "%s"', $clientCert['name']));
			return new Zend_Auth_Result(Zend_Auth_Result::FAILURE, $clientCert['name'], $this->_messages);
		}
	}
}

?>
