<?php
  namespace Leaf\Core\Http;
  
    class Request {
        public function __construct() {
          $this->requestMethod = $_SERVER['REQUEST_METHOD'];
          $handler = fopen('php://input', 'r');
          $this->request = stream_get_contents($handler);
        }

        public function get($param) {
            if ($this->requestMethod == "POST") {
              if (isset($_POST[$param])) {
                return $_POST[$param];
              } else {
                $data = json_decode($this->request, true);
                return isset($data[$param]) ? $data[$param] : null;
              }
            } else {
              return isset($_GET[$param]) ? $_GET[$param] : null;
            }
        }

        public function getBody() {
            $data = json_decode($this->request, true);

            if($this->requestMethod === "GET") {
              $body = array();
              foreach($_GET as $key => $value) {
                $body[$key] = $value;
              }
              return count($body) > 0 ? $body : null;
            }
            if ($this->requestMethod == "POST") {
              if (isset($_POST)) {
                $body = array();
                foreach($_POST as $key => $value) {
                  $body[$key] = $value;
                }
                return count($body) > 0 ? $body : null;
              } else {
                $body = array();
                foreach($data as $key => $value) {
                  $body[$key] = $value;
                }
                return count($body) > 0 ? $body : null;
              }
            }
        }
    };
