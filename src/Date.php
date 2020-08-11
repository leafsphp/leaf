<?php

namespace Leaf;

use \DateTime;

/**
 * Leaf Date
 * ----------------------
 * Quick date/time manipulation with Leaf
 * 
 * @author Michael Darko
 * @since 1.1.0
 */
class Date
{
	/**
	 * Get a random timestamp
	 * 
	 * DEPRECATION WARNING: SWITCH TO CAMEL CASE
	 */
	public static function random_timestamp($start = 1149095981, $end = 1749095981)
	{
		return self::randomTimestamp($start, $end);
	}

	public static function randomTimestamp($start = 1149095981, $end = 1749095981)
	{
		$random = mt_rand($start,  $end);
		return date("Y-m-d H:i:s", $random);
	}

	/**
	 * Get a random date
	 * 
	 * DEPRECATION WARNING: SWITCH TO CAMEL CASE
	 */
	public static function random_date($start = 1149095981, $end = 1749095981)
	{
		return self::randomTimestamp($start, $end);
	}
	
	public static function randomDate($start = 1149095981, $end = 1749095981)
	{
		$timestamp = mt_rand($start,  $end);
		$randomDate = new DateTime();
		$randomDate->setTimestamp($timestamp);
		$randomDate = json_decode(json_encode($randomDate), true);
		return $randomDate['date'];
	}

	/**
	 * Set the current timezone
	 * 
	 * DEPRECATION WARNING: SWITCH TO CAMEL CASE
	 */
	public static function set_timezone(String $timezone = "Africa/Accra")
	{
		self::setTimezone($timezone);
	}

	public static function setTimezone(String $timezone = "Africa/Accra")
	{
		date_default_timezone_set($timezone);
	}

	/**
	 * Get the current timezone
	 * 
	 * DEPRECATION WARNING: SWITCH TO CAMEL CASE
	 */
	public static function get_timezone()
	{
		return self::getTimezone();
	}

	public static function getTimezone()
	{
		return date_default_timezone_get();
	}

	/**
	 * Return current date(timestamp)
	 */
	public static function now()
	{
		return date('Y-m-d h:i:s a', time());
	}

	/**
	 * Get the date from some 'days ago'
	 * 
	 * DEPRECATION WARNING: SWITCH TO CAMEL CASE
	 */
	public static function days_ago(int $days_ago, $date = null)
	{
		return self::daysAgo($days_ago, $date);
	}

	public static function daysAgo(int $days_ago, $date = null)
	{
		return date('Y-m-d', strtotime("-$days_ago days", strtotime(self::toDate($date ?? self::now()))));
	}

	/**
	 * Get the date from some 'months ago'
	 * 
	 * DEPRECATION WARNING: SWITCH TO CAMEL CASE
	 */
	public static function months_ago(int $months_ago, $date = null)
	{
		self::monthsAgo($months_ago, $date);
	}

	public static function monthsAgo(int $months_ago, $date = null)
	{
		return date('Y-m-d', strtotime("-$months_ago months", strtotime(self::toDate($date ?? self::now()))));
	}

	/**
	 * Get the date from some 'years ago'
	 * 
	 * DEPRECATION WARNING: SWITCH TO CAMEL CASE
	 */
	public static function years_ago(int $years_ago, $date = null)
	{
		return self::yearsAgo($years_ago, $date);
	}

	public static function yearsAgo(int $years_ago, $date = null)
	{
		return date('Y-m-d', strtotime("-$years_ago years", strtotime(self::toDate($date ?? self::now()))));
	}

	/**
	 * Get a date from a timestamp
	 * 
	 * DEPRECATION WARNING: SWITCH TO CAMEL CASE
	 */
	public static function ts_to_date($timestamp, $format = 'Y-m-d')
	{
		return self::toDate($timestamp, $format);
	}

	public static function toDate($timestamp, $format = 'Y-m-d')
	{
		$timestamp = new DateTime($timestamp);
		$date = $timestamp;
		return $date->format($format);
	}

	/**
	 * Get an english date from a timestamp
	 * 
	 * DEPRECATION WARNING: SWITCH TO CAMEL CASE
	 */
	public static function ts_to_english_date($timestamp)
	{
		return self::toEnglishDate($timestamp);
	}

	public static function toEnglishDate($timestamp)
	{
		$timestamp = new DateTime($timestamp);
		$day = $timestamp->format('d');
		$month = $timestamp->format('m');
		$month = ltrim($month, 0);
		$month = self::int_to_month($month);
		$year = $timestamp->format('Y');
		$date = $month . ' ' . $day . ', ' . $year;
		return $date;
	}

	/**
	 * Get an english readable version of a timestamp
	 * 
	 * DEPRECATION WARNING: SWITCH TO CAMEL CASE
	 */
	public static function ts_to_english_ts($timestamp)
	{
		return self::toEnglishTs($timestamp);
	}

	public static function toEnglishTs($timestamp)
	{
		$timestampp = new DateTime($timestamp);
		$day = $timestampp->format('d');
		$month = $timestampp->format('m');
		$month = self::int_to_month(ltrim($month, '0'));
		$year = $timestampp->format('Y');
		$time = self::toTime($timestamp);
		$english_timeStamp = $day . ' ' . $month . ' ' . $year . ' ' . $time;
		return $english_timeStamp;
	}

	/**
	 * Get Time From a TimeStamp
	 * 
	 * DEPRECATION WARNING: SWITCH TO CAMEL CASE
	 */
	public static function ts_to_time($ts)
	{
		return self::toTime($ts);
	}

	public static function toTime($ts)
	{
		$ts = new DateTime($ts);
		return $ts->format('G:i:s');
	}

	/**
	 * Format a TimeStamp
	 */
	public static function format($ts, $format = "Y-m-d")
	{
		$ts = new DateTime($ts);
		return $ts->format($format);
	}

	/**
	 * Get the current year
	 */
	public static function year()
	{
		return date('Y', time());
	}

	/**
	 * Get current month
	 */
	public static function month()
	{
		return date('m', time());
	}

	/**
	 * Get current day
	 */
	public static function day()
	{
		return date('m', time());
	}

	/**
	 * Get month in words from a number
	 * 
	 * DEPRECATION WARNING: SWITCH TO CAMEL CASE
	 */
	public static function int_to_month(int $number)
	{
		return self::intToMonth($number);
	}

	public static function intToMonth(int $number)
	{
		$number = ltrim($number, '0');
		$months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
		$month = $months[$number - 1];
		return $month;
	}

	/**
	 * Get day in words from a number
	 * 
	 * DEPRECATION WARNING: SWITCH TO CAMEL CASE
	 */
	public static function int_to_day(int $number)
	{
		return self::intToMonth($number);
	}

	public static function intToDay(int $number)
	{
		$number = ltrim($number, '0');
		$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
		$day = $days[$number - 1];
		return $day;
	}
}
