<?php

class sugarCaseClock{

	//Global Properties
	var $dayBegins = 6; //Default Day begins = 6:00 AM
	var $dayEnds = 18; //Default Day ends = 6:00 PM

	var $weekBegins = 1; //Default Week begins = Monday
	var $weekEnds = 6; //Default Week ends = Sunday

	var $dayLength = 0;

	var $schedulerDayInterval = 30; //Default number of days modified to run update on

	//Constructor
	public function __construct(){
		echo"Weclome to the class constructor!<br />";

		require_once('include/modules.php');
		require_once('modules/Audit/Audit.php');
		//require_once('custom/modules/Cases/scc-functions.php');
		
		if ($this->dayEnds > $this->dayBegins) {
		    $this->dayLength = $this->dayEnds - $this->dayBegins;
		}else {
		    $this->dayLength = ($this->dayEnds+24) - $this->dayBegins;
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
			"cases.date_modified > ".db_convert("'".gmdate($GLOBALS['timedate']->get_db_date_time_format(), strtotime("- $schedulerDayInterval_override days"))."'" ,"datetime"),
			true
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

		if(!isset($results['int_dur'])) $results['int_dur'] = 0;
		if(!isset($results['ext_dur'])) $results['ext_dur'] = 0;
		if(!isset($results['total_dur'])) $results['total_dur'] = 0;

		if(!isset($results['int_percent'])) $results['int_percent'] = 0;
		if(!isset($results['ext_percent'])) $results['ext_percent'] = 0;

		$start = new DateTime($auditStartTime);
		$end = new DateTime($value['date_created']);

		//echo "1st - Start: $CaseCreated, End: {$value['date_created']} <br />";

		//'days between' function
		$days = $this->DaysBetween($this->dayLength, $this->weekBegins, $this->weekEnds, $start, $end);

        //differences
        $dur1 = $this->DiffHours($this->dayBegins , $this->dayEnds , $this->dayLength , $start, $end, $days);
        //END DAYS BETWEEN 1

        $auditStartTime = $value['date_created'];
        
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
function DaysBetween($daylength, $weekbegins, $weekends, $start, $end) {

$oneday = new DateInterval("P1D");

$days = array();
$data = $daylength;
//$data = '12';

// Creates an array for each day considered, excluding weekends
if ($start->format('Y-m-d') == $end->format('Y-m-d')) {
    foreach(new DatePeriod($start, $oneday, $end) as $day) {
        $days = $this->AddDay($days, $day, $data, $weekbegins, $weekends);
    }
} else {
    foreach(new DatePeriod($start, $oneday, $end->add($oneday)) as $day) {
        $days = $this->AddDay($days, $day, $data, $weekbegins, $weekends);
    }
}
return $days;
}

//Used within DaysBetween() to add weekdays to $days
function AddDay($days, $day, $data, $weekbegins, $weekends) {
        $day_num = $day->format("N"); /* 'N' number days 1 (mon) to 7 (sun) */
        if ($weekends > $weekbegins && $day_num >= $weekbegins) {
            if($day_num < $weekends) { /* weekday */
                array_push($days,$data);
            }
        } else {
            if($day_num > $weekends && $day_num <= $weekbegins) { /* weekday */
                array_push($days,$data);
            }
        } 
        return $days;
}

//Determines the number of hours between two DateTimes
function DiffHours($daybegins , $dayends , $daylength , $start, $end, $days) {

$startstring = $start->format('Y-m-d H:i:s');
$startend1 = $start->format('Y-m-d') . ' ' . $dayends . ':00:00';
$ds = sizeof($days) - 1;
$endstring = $end->format('Y-m-d H:i:s');
$endend1 = $end->format('Y-m-d') . ' ' . $daybegins . ':00:00';
if ($start->format('Y-m-d') !== $end->format('Y-m-d')) {
    //for $start, determines hours between DateTime and end of day (Note: Initially, 6-18 workday is hardcoded)
    if ($start->format('H') >= $daybegins && $start->format('H') < $dayends) {
        $days[0] = round((strtotime($startend1) - strtotime($startstring))/60/60,2);
    } elseif ($start->format('H') < $daybegins) {
        $days[0] = $daylength;
    } elseif ($start->format('H') >= $dayends) {
        $days[0] = '00';
    }

    //for $end, determines hours between DateTime and end of day (Note: Initially, 6-18 workday is hardcoded)
    if ($end->format('H') >= $daybegins && $end->format('H') < $dayends) {
        $days[$ds] = round((strtotime($endstring) - strtotime($endend1))/60/60,2);
    } elseif ($end->format('H') < $daybegins) {
        $days[$ds] = $daylength;
    } elseif ($end->format('H') >= $dayends) {
        $days[$ds] = '00';
    }

} else {
    $days[0] = '00';
    $days[1] = round((strtotime($endstring)-strtotime($startstring))/60/60,2);
}

//computes hours between start and end
$dur1 = 0;
foreach ($days as $x) {
    $dur1 = $dur1 + $x;
}
return $dur1;
}

}

?>