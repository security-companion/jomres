<?php
/**
 * Core file
 * @author Vince Wooll <sales@jomres.net>
 * @version Jomres 4
* @package Jomres
* @copyright	2005-2009 Vince Wooll

Jomres is distributed as a mix of two licenses (excepting other files in the libraries folder, which are independantly licensed). 

The first, proprietary license, refers to Jomres as a package. You cannot share it, period. You can see the full license here http://www.jomres.net/license.html. There are some exceptions, and these files are independantly licensed (see the /jomres/libraries/phptools folder for example)
The files in the /jomres/core-minicomponents,  /jomres/libraries/jomres/cms_specific and the /jomres/templates folders, whilst copyright Vince Wooll, are licensed differently to allow those users who wish, to develop and distribute their own third party plugins for Jomres. Those files are licensed under the MIT license, which allows third party vendors to modify them to suit their own requirements and if so desired, distribute them for free or cost. 

################################################################
This file is subject to the Jomres proprietary license, please do not distribute it. For licencing information, please visit 
http://www.jomres.net/index.php?option=com_content&task=view&id=214&Itemid=86 and http://www.jomres.net/license.html
################################################################
*/

// ################################################################
defined( '_JOMRES_INITCHECK' ) or die( 'Direct Access to this file is not allowed.' );
// ################################################################

class jomres_performance_monitor
	{
	// Store the single instance of Database
	private static $configInstance;

	public function __construct() 
		{
		$this->log = array();
		$this->sql_queries = array();
		$this->init();
		$this->show_results = false;
		}

	public static function getInstance()
		{
		if (!self::$configInstance)
			{
			self::$configInstance = new jomres_performance_monitor();
			}
		return self::$configInstance;
		}
		
	public function __clone()
		{
		trigger_error('Cloning not allowed on a singleton object', E_USER_ERROR);
		}
		
	public function switch_on()
		{
		$this->show_results = true;
		}
		
	public function switch_off()
		{
		$this->show_results = false;
		}

	public function set_point($point)
		{
		$ut = $this->getmicrotime();
		$mem_usage = memory_get_usage  (TRUE );
		$this->log[$point]=array('ut'=>$ut,'usage'=>$mem_usage);
		}
		
	public function set_sqlquery_log($query)
		{
		$this->sql_queries[]=$query;
		}
		
	public function get_report()
		{
		return $this->log;
		}
	
	public function get_point($point)
		{
		return $this->log[$point];
		}

	public function output_report()
		{
		if (isset($_REQUEST['no_html']) && $_REQUEST['no_html'] == (int)1)
			return;
		if (!$this->show_results)
			return;
		while(list($key, $time) = each($this->log))
			{
			$ut=$time['ut'];
			$usage=$time['usage'];
			$norm_mem = $this->get_normalised_memory_usage($usage);
			
			print "<b>".$key." - ".$ut." sec.</b><br/>";
			print "Memory usage ".$norm_mem." ( ".$usage." )<br/>";
			if (isset($last_ut))
				{
				$time_diff = $this->diff_microtime($last_ut,$ut);
				print "Diff ".$time_diff." sec.<br/>";
				}
			else
				{
				$first_time = $ut;
				}
			$last_ut = $ut;
			}
		$time_diff = $this->diff_microtime($first_time,$ut);
		print "Total Runtime ".sprintf('%01.2f', $time_diff)." sec.<br/>";
		
		
		echo "<br/><br/><br/>";
		print "Total queries: ".count($this->sql_queries).".<br/>";
		foreach ($this->sql_queries as $query)
			{
			echo str_replace(",",", ",$query)."<br/>";
			}
		}

	private function init()
		{
		$this->set_point("start_run");
		}

	private function getmicrotime()
		{
         $time = microtime();
        $time = explode(' ', $time);
        return $time[1] + $time[0]; 
		}

	private function diff_microtime($mt_old,$mt_new)
		{
		list($old_usec, $old_sec) = explode(' ',$mt_old);
		list($new_usec, $new_sec) = explode(' ',$mt_new);
		$old_mt = ((float)$old_usec + (float)$old_sec);
		$new_mt = ((float)$new_usec + (float)$new_sec);
		return $new_mt - $old_mt;
		}

	private function get_normalised_memory_usage($mem_usage) 
		{
		if ($mem_usage < 1024)
			$result = $mem_usage." bytes";
		elseif ($mem_usage < 1048576)
			$result =  round($mem_usage/1024,2)." kilobytes";
		else
			$result =  round($mem_usage/1048576,2)." megabytes";
		return $result;
		}

	}
	

?>