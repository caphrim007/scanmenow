<?php

if (!defined("_ABSPATH")) {
	define("_ABSPATH", dirname(dirname(__FILE__)));
}

class DefaultLanguage {
	public $lang;

	public function __construct() {
		$lang = array(
			'post_only' => array(
				'code' => 1,
				'mesg' => 'XML-RPC server accepts POST requests only.'
			),

			'not_well_formed' => array(
				'code' => 2,
				'mesg' => 'Parse error. Not well formed'
			),

			'invalid_rpc' => array(
				'code' => 3,
				'mesg' => 'Server error. Invalid XML-RPC. Not conforming to spec. Request must be a methodCall.'
			),

			'no_method' => array(
				'code' => 4,
				'mesg' => 'Server error. Requested method %s does not exist.'
			),

			'no_class_method' => array(
				'code' => 5,
				'mesg' => 'Server error. Requested class method %s does not exist.'
			),

			'no_obj_method' => array(
				'code' =>  6,
				'mesg' => 'Server error. Requested object method %s does not exist.'
			),

			'no_function' => array(
				'code' => 7,
				'mesg' => 'Server error. Requested function %s does not exist.'
			),

			'no_multi_call' => array(
				'code' => 8,
				'mesg' => 'Recursive calls to system.multicall are forbidden'
			),

			'no_class_def' => array(
				'code' => 9,
				'mesg' => 'Server error. Requested class %s is not defined in the API.'
			),

			'not_200' => array(
				'code' => 10,
				'mesg' => 'Transport error - HTTP status code was not 200'
			),

			'bad_class_spec' => array(
				'code' => 11,
				'mesg' => 'Parse error. Class spec not well formed'
			),

			'no_class_file' => array(
				'code' => 12,
				'mesg' => 'Server error. Class file does not exist'
			),

			'classpath_nf' => array(
				'code' => 13,
				'mesg' => 'Classpath not found'
			),

			'not_ca_cert' => array(
				'code' => 14,
				'mesg' => 'Could not open CA certificate: %s'
			),

			'set_cert' => array(
				'code' => 15,
				'mesg' => 'Setting certificate to %s'
			),

			'no_cert' => array(
				'code' => 16,
				'mesg' => 'Could not open certificate: %s'
			),

			'set_ca_cert' => array(
				'code' => 17,
				'mesg' => 'Setting CA certificate to %s'
			),

			'set_priv_key' => array(
				'code' => 18,
				'mesg' => 'Setting Private Key to %s'
			),

			'no_priv_key' => array(
				'code' => 19,
				'mesg' => 'Could not open private key: %s'
			),

			'handling_rpc' => array(
				'code' => 20,
				'mesg' => 'Handling RPC call for: %s'
			),

			'class_spec' => array(
				'code' => 21,
				'mesg' => 'Class spec looks good'
			),

			'class_exists' => array(
				'code' => 22,
				'mesg' => 'Class "%s" exists in callbacks'
			),

			'mapping_exist' => array(
				'code' => 23,
				'mesg' => 'Method mapping "%s" may exist for class "%s"'
			),

			'normalized_class' => array(
				'code' => 24,
				'mesg' => 'Normalized class file "%s" for requiring'
			),

			'requiring_class' => array(
				'code' => 25,
				'mesg' => 'Requiring class file'
			),

			'invoking_method' => array(
				'code' => 26,
				'mesg' => 'Invoking class method "%s" with supplied args'
			),

			'removing_decl' => array(
				'code' => 27,
				'mesg' => 'Removing XML declaration'
			),

			'xml_empty' => array(
				'code' => 28,
				'mesg' => 'XML message was empty'
			),

			'try_parse' =>  array(
				'code' => 29,
				'mesg' => 'Trying to parse: %s'
			),

			'xml_error' => array(
				'code' => 30,
				'mesg' => 'XML error: %s at line %d'
			),

			'try_raw' => array(
				'code' => 31,
				'mesg' => 'No data provided. Trying raw post data'
			),

			'try_stream' => array(
				'code' => 32,
				'mesg' => 'No raw post data. Trying php://input stream'
			),

			'no_raw' => array(
				'code' => 33,
				'mesg' => 'Still no raw post data found. Maybe was a GET?'
			),

			'got_post_data' => array(
				'code' => 34,
				'mesg' => 'Ok, got data: %s'
			),

			'sending_result' => array(
				'code' => 35,
				'mesg' => 'Result to send back to client: %s'
			),

			'make_request' => array(
				'code' => 36,
				'mesg' => 'Making request'
			),

			'url_given' => array(
				'code' => 37,
				'mesg' => 'URL given to us. Must parse it'
			),

			'url_not_given' => array(
				'code' => 38,
				'mesg' => 'URL not given. Configuring client with remaining params'
			),

			'function_called' => array(
				'code' => 39,
				'mesg' => '%s::%s method called'
			),

			'req_made' => array(
				'code' => 40,
				'mesg' => 'Request made'
			),

			'init_curl' => array(
				'code' => 41,
				'mesg' => 'Initializing cURL context'
			),

			'set_curl_opts' => array(
				'code' => 42,
				'mesg' => 'Setting cURL options'
			),

			'curl_exec' => array(
				'code' => 43,
				'mesg' => 'Executing cURL call'
			),

			'curl_contents' => array(
				'code' => 44,
				'mesg' => 'Contents of cURL call are: %s'
			)
		);

		$this->lang = $lang;
		return $lang;
	}

	public function read() {
		return $this->lang;
	}

	public function merge($original, $changes = array()) {
		$result = array();

		if (!empty($changes)) {
			$original = array_merge($original, $changes);
		}

		$this->lang = $original;
		return $original;
	}

	public function get($key) {
		if (isset($this->lang[$key]['mesg'])) {
			return $this->lang[$key];
		} else {
			return false;
		}
	}

	public function getMessage($key) {
		if (isset($this->lang[$key]['mesg'])) {
			return $this->lang[$key]['mesg'];
		} else {
			return false;
		}
	}

	public function getCode($key) {
		if (isset($this->lang[$key]['code'])) {
			return $this->lang[$key]['code'];
		} else {
			return false;
		}
	}
}

?>
