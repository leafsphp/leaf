<?php
	class CustomDate {
		public function __construct() {
			// maybe initialise date
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
			$day = $days[$number];
			return $day;
		}

		function GetEnglishDateFromTimeStamp($timestamp) {
			$timestamp = new DateTime($timestamp);
			$day = $timestamp->format('d');
			$month = $timestamp->format('m');
			$month = ltrim($month, 0);
			$month = GetMonthFromNumber($month);
			$year = $timestamp->format('Y');
			$date = $month.' '.$day.', '.$year;
			return $date;
		}

		# time stuff
		function GetTimeFromTimeStamp($timestamp) {
			$timestamp = new DateTime($timestamp);
			$time = $timestamp;
			return $time->format('G:i:s');
		}

		function GetEnglishTimeStampFromTimeStamp($timestamp) {
			$timestampp = new DateTime($timestamp);
			$day = $timestampp->format('d');
			$month = $timestampp->format('m');
			$month = ltrim($month, '0');
			$month = GetMonthFromNumber($month);
			$year = $timestampp->format('Y');
			$time = GetTimeFromTimeStamp($timestamp);
			$english_timeStamp = $day.' '.$month.' '.$year.' '.$time;
			return $english_timeStamp;
		}
	}