<?php
	namespace Leaf\Core;
	use \DateTime;

	class Date {
		function randomTimestamp($start=1149095981, $end=1749095981) {
			$random = mt_rand($start,  $end);
			return date("Y-m-d H:i:s", $random);
		}

		function setTimeZone($timezone="Africa/Accra") {
			date_default_timezone_set($timezone);
			return;
		}

		function getTimeZone() {
			return date_default_timezone_get();
		}

		function now() {
			return date('Y-m-d h:i:s a', time());
		}

		function randomDate($start=1149095981, $end=1749095981) {
			$timestamp = mt_rand($start,  $end);
			$randomDate = new DateTime();
			$randomDate->setTimestamp($timestamp);
			$randomDate = json_decode(json_encode($randomDate), true);
			return $randomDate['date'];
		}

		# date stuff
		function GetDateFromTimeStamp($timestamp) {
			$timestamp = new DateTime($timestamp);
			$date = $timestamp;
			return $date->format('Y-m-d');
		}

		function GetMonthFromNumber($number) {
			$number = ltrim($number, '0');
			$months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
			$month = $months[$number - 1];
			return $month;
		}

		function GetDayFromNumber($number) {
			$number = ltrim($number, '0');
			$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
			$day = $days[$number - 1];
			return $day;
		}

		function GetEnglishDateFromTimeStamp($timestamp) {
			$timestamp = new DateTime($timestamp);
			$day = $timestamp->format('d');
			$month = $timestamp->format('m');
			$month = ltrim($month, 0);
			$month = $this->GetMonthFromNumber($month);
			$year = $timestamp->format('Y');
			$date = $month.' '.$day.', '.$year;
			return $date;
		}

		function GetEnglishTimeStampFromTimeStamp($timestamp) {
			$timestampp = new DateTime($timestamp);
			$day = $timestampp->format('d');
			$month = $timestampp->format('m');
			$month = ltrim($month, '0');
			$month = $this->GetMonthFromNumber($month);
			$year = $timestampp->format('Y');
			$time = $this->GetTimeFromTimeStamp($timestamp);
			$english_timeStamp = $day.' '.$month.' '.$year.' '.$time;
			return $english_timeStamp;
		}

		# time stuff
		function GetTimeFromTimeStamp($timestamp) {
			$timestamp = new DateTime($timestamp);
			$time = $timestamp;
			return $time->format('G:i:s');
		}
	}
