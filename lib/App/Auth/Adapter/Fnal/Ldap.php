<?php

/**
* @author Tim Rupp
*/
class App_Auth_Adapter_Fnal_Ldap extends Zend_Auth_Adapter_Ldap {
	const IDENT = __CLASS__;

	/**
	* Authenticate the user
	*
	* @throws Zend_Auth_Adapter_Exception
	* @return Zend_Auth_Result
	*/
	public function authenticate() {
		$options = array();
		$config = Ini_Config::getInstance();
		$log = App_Log::getInstance(self::IDENT);

		$params = $this->getOptions();
		foreach ($params[0] as $key => $val) {
			if ($key == 'create') {
				continue;
			} else {
				$options[0][$key] = $val;
			}
		}

		$this->setOptions($options);

		$result = parent::authenticate();
		$messages = $result->getMessages();

		$principal = $this->getUsername();

		if (isset($params['create'])) {
			if (empty($params['create'])) {
				$log->debug(sprintf('Authentication successful for subject "%s"', $principal));
				return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $principal, $messages);
			}
		}

		if ($result->isValid()) {
			$log->debug('Creating XML-RPC proxy to get principal name from CSTAPI');

			$rpc = new Zend_XmlRpc_Client($config->ws->api->cstapi->uri);
			$client = $rpc->getProxy();

			$username = $config->ws->api->cstapi->username;
			$password = $config->ws->api->cstapi->password;

			// CST API doesnt currently do authentication.
			// Change this code when it finally does
			// $token = $client->authorize->getToken($username, $password);

			$accountId = Account_Util::getId($principal);

			if ($accountId == 0) {
				$log->debug(sprintf('Account "%s" does not exist in the database; creating it', $principal));
				$accountId = Account_Util::create($principal);

				$account = new Account($accountId);
				$random = Zend_OpenId::randomBytes(32);
				$account->setPassword(md5($random));

				$log->debug('Creating new role for account based off of account name');
				$roleId = Role_Util::create($account->getUsername(), 'Default account role');
				$account->role->addRole($roleId);
				$account->setPrimaryRole($roleId);

				try {
					$log->debug('Looking up your KCA DN so that I can map it to your account');
					$kcaDn = $client->acct->getDnTranslation($principal);
					if ($kcaDn == null) {
						$log->debug('Could not find your KCA DN in the CSTAPI translation table');
					} else {
						$result = $account->createAccountMapping($kcaDn);
					}

					$servicePrincipals = $client->acct->getServicePrincipals($principal);
					if (empty($servicePrincipals)) {
						$log->debug('Did not find your Service realm principals via the CSTAPI');
					} else {
						foreach($servicePrincipals as $service) {
							$result = $account->createAccountMapping($service);
						}
					}
				} catch (Exception $error) {
					$log->err($error->getMessage());
					$log->debug($rpc->getHttpClient()->getLastResponse()->getBody());
				}

				$account = new Account($accountId);

				$log->debug(sprintf('Authentication successful for subject "%s"', $principal));
				return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $principal, $messages);
			} else {
				return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $principal, $messages);
			}
		} else {
			$log->debug(sprintf('Authentication failed for subject "%s"', $principal));
			return new Zend_Auth_Result(Zend_Auth_Result::FAILURE, $principal, $messages);
		}
	}
}

?>
