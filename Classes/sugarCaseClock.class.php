<?php


		require_once('include/modules.php');
		require_once('modules/Audit/Audit.php');
		require_once('include/TimeDate.php');

class sugarCaseClock{

	//Global Properties
	var $dayBegins = 14; //Default Day begins = 6:00 AM
	var $dayEnds = 2; //Default Day ends = 6:00 PM

	var $weekBegins = 1; //Default Week begins = Monday
	var $weekEnds = 5; //Default Week ends = Friday

	var $dayLength = 0;

	var $schedulerDayInterval = 30; //Default number of days modified to run update on

	//Constructor
	public function __construct(){
		echo"Weclome to the class constructor!<br />";
		
		if ($this->dayEnds > $this->dayBegins) {
		    $dayLength = $this->dayEnds - $this->dayBegins;
		}else {
		    $dayLength = ($this->dayEnds+24) - $this->dayBegins;
		}

	}

	//Methods

	/**
	 * Get List of Cases to Modify
	 * Returns list of beans
	 */
	public function getRecentModifiedCases($schedulerDayInterval_override = 0){

		echo"Retreive the list of cases to update.<br />";

		if($schedulerDayInterval_override == 0) $schedulerDayInterval_override = $this->schedulerDayInterval;

		//Pull list of cases to crunch here:
		$bean_list = BeanFactory::getBean('Cases');
		$case_list = $bean_list->get_full_list(
			"", 
			"cases.date_modified > ".db_convert("'".gmdate($GLOBALS['timedate']->get_db_date_time_format(), strtotime("- $schedulerDayInterval_override days"))."'" ,"datetime")
		);

		return $case_list;

	}

	/**
	 * Get a single Case to Modify
	 * Returns bean
	 */
	public function getSingleCase($id){

		//Get single Case's Audit:
		$bean = BeanFactory::getBean("Cases", $id);

		return $bean;
	}

	/**
	 * Get Audit List for the case we are currently on
	 * Returns Audit Array
	 */
	public function getAuditData(){

	    $audit_list =  Audit::get_audit_list();
	    $status_audit = array();
	    foreach(array_reverse($audit_list) as $key => $value) {
	        if($value['field_name'] == 'Status:') {
	            array_push($status_audit, $value);
	        }
	    }

	    return $status_audit;

	}

	/**
	 * Process Audit Data
	 * Returns array of internal data
	 */
	public function processAuditData(&$auditStartTime,$key,$value,$results){

		global $timedate;

		if(!isset($results['int_dur'])) $results['int_dur'] = 0;
		if(!isset($results['ext_dur'])) $results['ext_dur'] = 0;
		if(!isset($results['total_dur'])) $results['total_dur'] = 0;

		if(!isset($results['int_percent'])) $results['int_percent'] = 0;
		if(!isset($results['ext_percent'])) $results['ext_percent'] = 0;

		$start = new DateTime($auditStartTime);
		$end = new DateTime($value['date_created']);
		$end = $timedate->tzGMT($end);
		$endStr = $end->format('Y-m-d H:i:s');

		//echo "1st - Start: $CaseCreated, End: {$value['date_created']} <br />";

		//'days between' function
		$days = $this->DaysBetween($this->dayLength, $this->weekBegins, $this->weekEnds, $start, $end);

        //differences
        $dur1 = $this->DiffHours($this->dayBegins , $this->dayEnds , $this->dayLength , $start, $end, $days);
        //END DAYS BETWEEN 1

        $auditStartTime = $endStr;
        
        //adds this duration to appropriate bins //
        if ($value['before_value_string'] == 'New' || $value['before_value_string'] == 'Internal' || $value['before_value_string'] == 'Assigned') {
            $results['int_dur'] += $dur1;
        }else{
			$results['ext_dur'] += $dur1;
        }
        $results['total_dur'] += $dur1;

		return $results;

	}

//FUNCTIONS from scc-functions.php
//TODO: Cleanup

//Excludes weekends and calculates the days between two dates
function DaysBetween($dayLength, $weekBegins, $weekEnds, $start, $end) {

	$oneday = new DateInterval("P1D");
	$endClone = clone($end);
	$days = 0;

	// Adds to $days any day not worked

	foreach(new DatePeriod($start, $oneday, $endClone/*->add($oneday)*/) as $day) {
		$days = $this->AddDay($days, $day, $weekBegins, $weekEnds);
	}
	return $days;
}

//Used within DaysBetween() to add weekdays to $days
function AddDay($days, $day, $weekBegins, $weekEnds) {
    $day_num = $day->format("N"); /* 'N' number days 1 (mon) to 7 (sun) */
    if ($weekEnds < $weekBegins) {
        if($day_num < $weekEnds && $day_num > $weekBegins) { /* weekday */
            $days ++;
        }
    } else {
        if($day_num > $weekEnds || $day_num < $weekBegins) { /* weekday */
            $days ++;
        }
    }
    return $days;
}

//Determines the number of hours between two DateTimes
function DiffHours($dayBegins , $dayEnds , $dayLength , $start, $end, $days) {

	$startW = $start->format('Y-m-d H:i:s');
	$endW = $end->format('Y-m-d H:i:s');

	$startStr = strtotime($startW);
	$endStr = strtotime($endW);

	$startDay = $start->format('Y-m-d');
	$endDay = $end->format('Y-m-d');

	$startBegin = $start->format('Y-m-d') . ' ' . $dayBegins . ':00:00';
		$startBeginStr = strtotime($startBegin);
	$startEnd = $start->format('Y-m-d') . ' ' . $dayEnds . ':00:00';
		$startEndStr = strtotime($startEnd);
	$endBegin = $end->format('Y-m-d') . ' ' . $dayBegins . ':00:00';
		$endBeginStr = strtotime($endBegin);
	$endEnd = $end->format('Y-m-d') . ' ' . $dayEnds . ':00:00';
		$endEndStr = strtotime($endEnd);
	$startM24 = $start->format('Y-m-d') . ' 23:59:59';
		$startM24Str = strtotime($startM24) + 1;
	$endM00 = $end->format('Y-m-d') . ' 00:00:00';
		$endM00Str = strtotime($endM00);

	$dayFirst = 0;
	$dayMiddle = 0;
	$dayLast = 0;


	//Days Between
	// (((endDay - startDay) - 1) - $days) * dayLength

	//Calculates Days Between
	if ($endDay != $startDay) {
		$dayMiddle = (((($endDayStr - $startDayStr)/60/60/24) - 1) - $days) * $dayLength;
	} else {
		$dayMiddle = 0;
	}

	//Calculates First Day
	if ($dayBegins <= $dayEnds) {
		if ($startStr <= $startBeginStr) { 
			if ($endStr <= $startBeginStr) $dayFirst = 0;
			if ($endStr > $startBeginStr && $endStr <= $startEndStr) $dayFirst = round(($endStr - $startBeginStr)/60/60, 2);
			if ($endStr > $startEndStr) $dayFirst = round(($startEndStr - $startBeginStr)/60/60,2);
		} elseif ($startStr > $startBeginStr && $startStr < $startEndStr) {
			if ($endStr > $startBeginStr && $endStr <= $startEndStr) $dayFirst = round(($endStr - $startStr)/60/60,2);
			if ($endStr > $startEndStr) $dayFirst = round(($startEndStr - $startStr)/60/60,2);
		} elseif ($startStr >= $$startEndStr) {
			if ($endStr > $startEndStr) $dayFirst = round(($startEndStr - $startStr)/60/60,2);
		}
	} else {
		if ($startStr <= $startEndStr) { 
			if ($endStr <= $startEndStr) $dayFirst = round(($endStr - $startStr)/60/60,2);
			if ($endStr > $startEndStr && $endStr <= $startBeginStr) $dayFirst = round(($startEndStr - $startStr)/60/60,2);
			if ($endStr > $startBeginStr && $endStr <= $startM24Str) $dayFirst = round((($startEndStr - $startStr) + ($endStr - $startBeginStr))/60/60,2);
			if ($endDay != $startDay) $dayFirst = round((($startEndStr - $startStr) + ($startM24Str - $startBeginStr))/60/60,2);
		} elseif ($startStr > $startEndStr && $startStr < $startBeginStr) {
			if ($endStr > $startEndStr && $endStr <= $startBeginStr) $dayFirst = 0;
			if ($endStr > $startBeginStr && $endStr <= $startM24Str) $dayFirst = round(($endStr - $startBeginStr)/60/60,2);
		} elseif ($startStr >= $startBeginStr) {
			if ($endStr > $startBeginStr && $endStr <= $startM24Str) $dayFirst = round(($endStr - $startStr)/60/60,2);
			if ($endDay != $startDay) $dayFirst = round(($startM24Str - $startStr)/60/60,2);
		}		
	}

	//Calculates Last Day
	if ($dayBegins <= $dayEnds) {
		if ($endStr <= $endBeginStr) { 
			if ($startStr <= $endBeginStr) $dayLast = 0;
		} elseif ($endStr > $endBeginStr && $endStr < $endEndStr) {
			if ($startStr <= $endBeginStr) $dayLast = round(($endStr - $endBeginStr)/60/60,2);
			if ($startStr > $endBeginStr && $startStr <= $endEndStr) $dayLast = 0;
		} elseif ($endStr >= $$endEndStr) {
			if ($startStr <= $endBeginStr) $dayLast = round(($endEndStr - $endBeginStr)/60/60,2);
			if ($startStr > $endBeginStr && $startStr <= $endEndStr) $dayLast = 0;
			if ($startStr > $endEndStr) $dayLast = 0;
		}
	} else {
		if ($endStr <= $endEndStr) { 
			if ($startDay != $endDay) $dayLast = round(($endStr - $endM00Str)/60/60,2);
			if ($startStr <= $endEndStr && $startStr > $endM00Str) $dayLast = 0;
		} elseif ($endStr > $endEndStr && $endStr < $endBeginStr) {
			if ($startDay != $endDay) $dayLast = round(($endBeginStr - $endM00Str)/60/60,2);
			if ($startStr <= $endEndStr && $startStr > $endM00Str) $dayLast = 0;
			if ($startStr > $endEndStr && $startStr <= $endBeginStr) $dayLast = 0;
		} elseif ($endStr >= $endBeginStr) {
			if ($startDay != $endDay) $dayLast = round((($endStr - $endBeginStr) + ($endEndStr - $endM00Str))/60/60,2);
			if ($startStr <= $endEndStr && $startStr > $endM00Str) $dayLast = 0;
			if ($startStr > $endEndStr && $startStr <= $endBeginStr) $dayLast = 0;
			if ($startStr > $endBeginStr) $dayLast = 0;
		}		
	}

	$dur1 = $dayFirst + $dayMiddle + $dayLast;
	//echo "First: $dayFirst<br />Last: $dayLast<br />dur1: $dur1<br />";

return $dur1;
}

}

?>