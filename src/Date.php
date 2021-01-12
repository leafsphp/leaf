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
	 * Generate a random timestamp
	 */
	public static function randomTimestamp($start = 1149095981, $end = 1749095981)
	{
		$random = mt_rand($start,  $end);
		return date("Y-m-d H:i:s", $random);
	}

	/**
	 * Generate a random date
	 */
	public static function randomDate($start = 1149095981, $end = 1749095981)
	{
		$timestamp = mt_rand($start,  $end);
		$randomDate = new DateTime();
		$randomDate->setTimestamp($timestamp);
		$randomDate = json_decode(json_encode($randomDate), true);
		return $randomDate['date'];
	}

	/**
	 * Set default date timezone
	 */
	public static function setTimezone(String $timezone = "Africa/Accra")
	{
		date_default_timezone_set($timezone);
	}

	/**
	 * Set default date timezone
	 */
	public static function getTimezone()
	{
		return date_default_timezone_get();
	}

	/**
	 * Parse unix date
	 */
	public static function rawDate($date, $format = 'D, d M Y H:i:s')
	{
		return date($format, $date);
	}

	/**
	 * Return current date(timestamp)
	 */
	public static function now($useTFFormat = true)
	{
		return date($useTFFormat ? 'Y-m-d H:i:s' : 'Y-m-d h:i:s a', time());
	}

	/**
	 * Get the date a number of days ago
	 */
	public static function daysAgo(int $days_ago, $date = null)
	{
		return date('Y-m-d', strtotime("-$days_ago days", strtotime(self::toDate($date ?? self::now()))));
	}

	/**
	 * Get the date a number of months ago
	 */
	public static function monthsAgo(int $months_ago, $date = null)
	{
		return date('Y-m-d', strtotime("-$months_ago months", strtotime(self::toDate($date ?? self::now()))));
	}

	/**
	 * Get the date a number of years ago
	 */
	public static function yearsAgo(int $years_ago, $date = null)
	{
		return date('Y-m-d', strtotime("-$years_ago years", strtotime(self::toDate($date ?? self::now()))));
	}

	/**
	 * Convert a timstamp to a date
	 */
	public static function toDate($timestamp, $format = 'Y-m-d')
	{
		$timestamp = new DateTime($timestamp);
		$date = $timestamp;
		return $date->format($format);
	}

	/**
	 * Get a neatly formatted english date from a timestamp
	 */
	public static function toEnglishDate($timestamp)
	{
		$timestamp = new DateTime($timestamp);
		$day = $timestamp->format('d');
		$month = $timestamp->format('m');
		$month = ltrim($month, 0);
		$month = self::intToMonth($month);
		$year = $timestamp->format('Y');
		$date = $month . ' ' . $day . ', ' . $year;
		return $date;
	}

	/**
	 * Format a timestamp to an english readable of a timestamp
	 */
	public static function toEnglishTs($timestamp)
	{
		$timestampp = new DateTime($timestamp);
		$day = $timestampp->format('d');
		$month = $timestampp->format('m');
		$month = self::intToMonth(ltrim($month, '0'));
		$year = $timestampp->format('Y');
		$time = self::toTime($timestamp);
		$english_timeStamp = $day . ' ' . $month . ' ' . $year . ' ' . $time;
		return $english_timeStamp;
	}

	/**
	 * Get the time from a timestamp
	 */
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
	 * Get the month from a number
	 */
	public static function intToMonth(int $number)
	{
		$number = ltrim($number, '0');
		$months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
		$month = $months[$number - 1];
		return $month;
	}

	/**
	 * Get the day from a number
	 */
	public static function intToDay(int $number)
	{
		$number = ltrim($number, '0');
		$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
		$day = $days[$number - 1];
		return $day;
	}
}
