<?php
    class Response {
        public function respond($data) {
            return json_encode($data);
        }

        public function respondWithCode($data, $code = 200) {
            $dataToPrint = array('data' => $data, 'code' => $code);
            return json_encode($dataToPrint);
        }

        public function throwErr($error, $code) {
            $dataToPrint = array('error' => $error, 'code' => $code);
            return json_encode($dataToPrint);
        }

        public function renderMarkup($file) {
            return require $file;
        }
    };
