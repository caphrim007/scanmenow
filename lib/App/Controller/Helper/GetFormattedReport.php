<?php

/**
* @author Tim Rupp
*/
class App_Controller_Helper_GetFormattedReport extends Zend_Controller_Action_Helper_Abstract {
	const IDENT = __CLASS__;

	public function direct(Zend_Http_Client $client, $scanId, $format) {
		$filename = $this->_generateFormattedReport($client, $scanId, $format);
		if ($filename === null) {
			throw new Exception('Failed to correctly generate the report');
		}

		$result = $this->_downloadFormattedReport($client, $filename);
		if ($result === null) {
			throw new Exception('Failed to download the generated report');
		}

		return $result;
	}

	protected function _generateFormattedReport($client, $scanId, $format) {
		$url = $client->getUri();

		$uri = Zend_Uri::factory($url);
		$uri->setPath('/file/xslt');
		$url = $uri->getUri();

		try {
			$client->setUri($url);
			$client->resetParameters();

			$client->setParameterPost(array(
				'report' => $scanId,
				'xslt' => $format
			));

			$response = $client->request('POST');

			/**
			* A valid response looks like this
			*
			*  <html>
			*    <meta http-equiv="refresh" content="3;url=/file/xslt/download/?fileName=e5c113b1b4aef9507fcf096976d2e90b.html">
			*    <body bgcolor="#2b4e67">
			*      <br><br><br><br>
			*      <center>
			*        <font color="#ffffff" face="Verdana" size="+2">Nessus is formatting the report. Please wait...</font>
			*        <br><br>
			*        <img src="/loading.gif" border="0">
			*      </center>
			*    </body>
			*  </html>
			*/
			$response = $response->getBody();

			/**
			* Nessus does a meta-refresh after 3 seconds. I guess
			* this is done to give the server adequate time to
			* generate the report.
			*
			* So I am going to do like they do in Rome and sleep
			* for for 3 seconds too.
			*/
			sleep(3);

			$pattern = '/filename=(?<filename>[^"]+)/i';
			$findings = preg_match($pattern, $response, $matches);

			if ($findings == 0) {
				return null;
			} else {
				$filename = $matches['filename'];
				return $filename;
			}
		} catch (Exception $error) {
			return null;
		}
	}

	protected function _downloadFormattedReport($client, $filename) {
		$url = $client->getUri();

		$uri = Zend_Uri::factory($url);
		$uri->setPath('/file/xslt/download');
		$url = $uri->getUri();

		try {
			$client->setUri($url);
			$client->resetParameters();

			$client->setParameterGet(array(
				'fileName' => $filename
			));

			$response = $client->request('GET');
			$response = $response->getBody();

			return $response;
		} catch (Exception $error) {
			return null;
		}
	}
}

?>
