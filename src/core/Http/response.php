<?php
    namespace Leaf\Core\Http;
    
    class Response {
        public function respond($data) {
            header('Content-Type: application/json');
            echo json_encode($data);
        }

        public function respondWithCode($data, $code = 200) {
            header('Content-Type: application/json');
            $dataToPrint = array('data' => $data, 'code' => $code);
            echo json_encode($dataToPrint);
        }

        public function throwErr($error, $code) {
            header('Content-Type: application/json');
            $dataToPrint = array('error' => $error, 'code' => $code);
            echo json_encode($dataToPrint);
        }

        // has no use at the moment
        // public function rawResponse($res) {
        //     echo $res;
        // }

        public function renderHtmlPage($file) {
            header('Content-Type: text/html');
            echo require $file;
        }

        public function renderMarkup($markup) {
            header('Content-Type: text/html');
            echo $markup;
        }
    };
