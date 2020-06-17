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
	 */
	public function random_timestamp($start = 1149095981, $end = 1749095981)
	{
		$random = mt_rand($start,  $end);
		return date("Y-m-d H:i:s", $random);
	}

	/**
	 * Get a random date
	 */
	public function random_date($start = 1149095981, $end = 1749095981)
	{
		$timestamp = mt_rand($start,  $end);
		$randomDate = new DateTime();
		$randomDate->setTimestamp($timestamp);
		$randomDate = json_decode(json_encode($randomDate), true);
		return $randomDate['date'];
	}

	/**
	 * Set the current timezone
	 */
	public function set_timezone(String $timezone = "Africa/Accra")
	{
		date_default_timezone_set($timezone);
	}

	/**
	 * Get the current timezone
	 */
	public function get_timezone()
	{
		return date_default_timezone_get();
	}

	/**
	 * Return current date(timestamp)
	 */
	public function now()
	{
		return date('Y-m-d h:i:s a', time());
	}

	/**
	 * Get the date from some 'days ago'
	 */
	public function days_ago(int $days_ago, $date = null)
	{
		return date('Y-m-d', strtotime("-$days_ago days", strtotime($this->ts_to_date($date ? $date : $this->now()))));
	}

	/**
	 * Get the date from some 'months ago'
	 */
	public function months_ago(int $months_ago, $date = null)
	{
		return date('Y-m-d', strtotime("-$months_ago months", strtotime($this->ts_to_date($date ? $date : $this->now()))));
	}

	/**
	 * Get the date from some 'years ago'
	 */
	public function years_ago(int $years_ago, $date = null)
	{
		return date('Y-m-d', strtotime("-$years_ago years", strtotime($this->ts_to_date($date ? $date : $this->now()))));
	}

	/**
	 * Get a date from a timestamp
	 */
	public function ts_to_date($timestamp)
	{
		$timestamp = new DateTime($timestamp);
		$date = $timestamp;
		return $date->format('Y-m-d');
	}

	/**
	 * Get an english date from a timestamp
	 */
	public function ts_to_english_date($timestamp)
	{
		$timestamp = new DateTime($timestamp);
		$day = $timestamp->format('d');
		$month = $timestamp->format('m');
		$month = ltrim($month, 0);
		$month = $this->int_to_month($month);
		$year = $timestamp->format('Y');
		$date = $month . ' ' . $day . ', ' . $year;
		return $date;
	}

	/**
	 * Get an english readable version of a timestamp
	 */
	public function ts_to_english_ts($timestamp)
	{
		$timestampp = new DateTime($timestamp);
		$day = $timestampp->format('d');
		$month = $timestampp->format('m');
		$month = $this->int_to_month(ltrim($month, '0'));
		$year = $timestampp->format('Y');
		$time = $this->ts_to_time($timestamp);
		$english_timeStamp = $day . ' ' . $month . ' ' . $year . ' ' . $time;
		return $english_timeStamp;
	}

	/**
	 * Get Time From a TimeStamp
	 */
	public function ts_to_time($ts)
	{
		$ts = new DateTime($ts);
		return $ts->format('G:i:s');
	}

	/**
	 * Format a TimeStamp
	 */
	public function format($ts, $format = "Y-m-d")
	{
		$ts = new DateTime($ts);
		return $ts->format($format);
	}

	/**
	 * Get the current year
	 */
	public function year()
	{
		return date('Y', time());
	}

	/**
	 * Get current month
	 */
	public function month()
	{
		return date('m', time());
	}

	/**
	 * Get current day
	 */
	public function day()
	{
		return date('m', time());
	}

	/**
	 * Get month in words from a number
	 */
	public function int_to_month(int $number)
	{
		$number = ltrim($number, '0');
		$months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
		$month = $months[$number - 1];
		return $month;
	}

	/**
	 * Get day in words from a number
	 */
	public function int_to_day(int $number)
	{
		$number = ltrim($number, '0');
		$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
		$day = $days[$number - 1];
		return $day;
	}
}
