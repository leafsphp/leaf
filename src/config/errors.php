<?php
namespace Leaf\Config;

use Leaf\Core\Logger;

/**
 * Leaf Dev Errors
 * ------
 * Easily handle run-time errors
 */
class Errors extends Logger {
	/**
	 * Hide run time errors
	 */
	public function hide() {
		error_reporting(0);
   		ini_set('display_errors', 0);
	}

	/**
	 * Show run time errors
	 */
	public function show() {
		error_reporting(1);
   		ini_set('display_errors', 1);
	}

	public function showCustom() {}

	// public function report(Exception $e) {
	// 	if ($this->shouldntReport($e)) {
    //         return;
    //     }

    //     if (is_callable($reportCallable = [$e, 'report'])) {
    //         return $this->container->call($reportCallable);
    //     }

    //     try {
    //         // $logger = $this->container->make(LoggerInterface::class);
    //     } catch (Exception $ex) {
    //         throw $e;
    //     }

    //     $logger->error(
    //         $e->getMessage(),
    //         array_merge($this->context(), ['exception' => $e]
    //     ));
	// }
}
