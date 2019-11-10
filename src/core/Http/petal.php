<?php
namespace Leaf\Core\Http;

class Petal {
	private $request;
	private $supportedHttpMethods = ["GET", "POST", "PUT"];
	private $callback;
	private $url;
	private $requestMethod;
	private $requestData;
	private $requestResult;

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

	public function makeRequest($requestMethod, $url, $requestData = false) {
		$curl = curl_init();
		$requestMethod = strtoupper($requestMethod);

		switch ($requestMethod) {
			case "POST":
				curl_setopt($curl, CURLOPT_POST, 1);
				if ($requestData)
					curl_setopt($curl, CURLOPT_POSTFIELDS, $requestData);
				break;
			case "PUT":
				curl_setopt($curl, CURLOPT_PUT, 1);
				break;
			default:
				if ($requestData)
					$url = sprintf("%s?%s", $url, http_build_query($requestData));
		}

		// Optional Authentication:
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_USERPWD, "username:password");

		try {
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		} catch (\Throwable $err) {
			throw $err;
			throw new Exception("Failed to set URL", 1);
		}

		try {
			$result = curl_exec($curl);
		} catch(\Throwable $err) {
			throw new Exception("An error occured", 1);
		}
		
		curl_close($curl);

		return $this->saveResult($result);
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