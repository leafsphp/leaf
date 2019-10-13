<?php
    class Request {
        public function __construct() {
            $this->requestMethod = $_SERVER['REQUEST_METHOD'];
            $handler = fopen('php://input', 'r');
			$this->request = stream_get_contents($handler);
        }

        public function getParam($param) {
            $data = json_decode($this->request, true);
            return isset($data[$param]) ? $data[$param] : null;
        }

        public function getBody() {
            $data = json_decode($this->request, true);

            if($this->requestMethod === "GET") {
              return;
            }
            if ($this->requestMethod == "POST") {
              $body = array();
              foreach($data as $key => $value) {
                $body[$key] = $value;
              }
              return $body;
            }
        }
    };
