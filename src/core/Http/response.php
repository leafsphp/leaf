<?php
    namespace Leaf\Core\Http;
    /**
	*	Leaf PHP Response
	*	--------------------------
	*	Handles responses from Leaf App
	*/
    class Response {
        public function respond($data) {
            header('Content-Type: application/json');
            echo json_encode($data);
        }

        public function respondWithCode($data, $code = 200) {
            header('Content-Type: application/json', true, $code);
            $dataToPrint = array('data' => $data, 'code' => $code);
            echo json_encode($dataToPrint);
        }

        public function throwErr($error, $code = 500) {
            header('Content-Type: application/json');
            $dataToPrint = array('error' => $error, 'code' => $code);
            $this->respond($dataToPrint);
            exit();
        }

        // has no use at the moment
        // public function rawResponse($res) {
        //     echo $res;
        // }

        public function renderPage($file) {
            header('Content-Type: text/html');
            require $file;
        }

        public function renderMarkup($markup) {
            header('Content-Type: text/html');
            echo $markup;
        }
    };
