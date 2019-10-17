<?php
    namespace Leaf\Core;
    
    class Response {
        public function respond($data) {
            header('Content-Type: application/json');
            return json_encode($data);
        }

        public function respondWithCode($data, $code = 200) {
            header('Content-Type: application/json');
            $dataToPrint = array('data' => $data, 'code' => $code);
            return json_encode($dataToPrint);
        }

        public function throwErr($error, $code) {
            header('Content-Type: application/json');
            $dataToPrint = array('error' => $error, 'code' => $code);
            return json_encode($dataToPrint);
        }

        // has no use at the moment
        // public function rawResponse($res) {
        //     return $res;
        // }

        public function renderHtmlPage($file) {
            header('Content-Type: text/html');
            return require $file;
        }

        public function renderMarkup($markup) {
            header('Content-Type: text/html');
            return $markup;
        }
    };
