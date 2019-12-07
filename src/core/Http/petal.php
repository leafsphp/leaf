<?php
namespace Leaf\Core\Http;

class Petal {
	protected $request;
	protected $supportedHttpMethods = ["GET", "POST", "PUT"];
	protected $callback;
	protected $url;
	protected $requestMethod;
	protected $requestData;
	protected $requestResult;

	function __call($requestMethod, $args) {
		list($url, $callback) = $args;
		$this->url = $url;
		$this->callback = $callback;
		$this->requestMethod = $requestMethod;
	}

	public function getUrl() {
		return $this->url;
	}

	public function getMethod() {
		return $this->requestMethod;
	}

	public function getResponse() {
		$res = $this->requestResult;
		return $res;
	}

	public function getResponseParam($param) {
		$res = $this->requestResult;
		return $res;
	}

	public function saveResult($requestResult) {
		$this->requestResult = $requestResult;
	}

	public function makeRequest($requestMethod, $url, $requestData = false, $requestHeaders = null) {
		$requestMethod = strtoupper($requestMethod);

		$netRequest = curl_init($url);
		
		curl_setopt($netRequest, CURLOPT_RETURNTRANSFER, true);
		if ($requestHeaders != null) {
			curl_setopt($netRequest, CURLOPT_HTTPHEADER, $requestHeaders);
		}

		switch ($requestMethod) {
			case "POST":
				curl_setopt($netRequest, CURLOPT_POST, 1);
				if ($requestData) {
					curl_setopt($netRequest, CURLOPT_POSTFIELDS, $requestData);
				}
			break;

			case "PUT":
				curl_setopt($netRequest, CURLOPT_PUT, 1);
			break;

			// default:
			// 	if ($requestData) {
			// 		$url = sprintf("%s?%s", $url, http_build_query($requestData));
			// 	}
		}

		// Optional Authentication:
		// curl_setopt($netRequest, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		// curl_setopt($netRequest, CURLOPT_USERPWD, "username:password");

		$this->saveResult(curl_exec($netRequest));
		curl_close($netRequest);
	}

	function resolve() {
		$this->makeRequest($this->requestMethod, $this->url, $this->requestData);
		return call_user_func_array($this->callback, array());
	}

	function __destruct() {
		if (!isset($this->callback) || $this->callback == null) {
			return;
		}
		$this->resolve();
	}
}