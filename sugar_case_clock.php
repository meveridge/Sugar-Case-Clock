<?php

//Location of file: /custom/Extension/modules/Schedulers/Ext/ScheduledTasks/sugar_case_clock.php

array_push($job_strings, 'sugar_case_clock');

function sugar_case_clock(){

	require_once('include/modules.php');
	require_once('modules/Audit/Audit.php');
	require_once('custom/modules/Cases/scc-functions.php');

	//CONFIG INFO 1
	//Note: 06 = 6am, 18 = 6pm
	$daybegins = '06';
	$dayends = '18';
	if ($dayends > $daybegins) {
	    $daylength = $dayends - $daybegins;
	}else {
	    $daylength = ($dayends+24) - $daybegins;
	}

	//Note: 1 = Monday, 7 = Sunday
	$weekbegins = '1';
	$weekends = '6';


	global $focus;

	//Pull list of cases to crunch here:
	$bean_list = BeanFactory::getBean('Cases');
	$case_list = $bean_list->get_full_list("", "cases.date_modified > ".db_convert("'".gmdate($GLOBALS['timedate']->get_db_date_time_format(), strtotime("- 30 days"))."'" ,"datetime"),true);

	foreach($case_list as $key => $focus){

	//Get single Case's Audit:
	//$focus = BeanFactory::getBean("Cases", "62859f2e-7bc1-6aff-fefc-52f6f541138b");

	//echo "<pre>".print_r($focus, true)."</pre>";

	    $audit_list =  Audit::get_audit_list();
	    $status_audit = array();
	    foreach(array_reverse($audit_list) as $key => $value) {
	        if($value['field_name'] == 'Status:') {
	            array_push($status_audit, $value);
	        }
	    }

	    //Patricks code here:

	//CONFIG INFO 2
	$prev_date = null;

	//durations belonging to users and clients
	$int_dur = 0;
	$ext_dur = 0;
	$total_dur = 0;

	    $CaseCreated = $focus->getFieldValue('date_entered');

	    foreach($status_audit as $key => $value) {

	        if($value['field_name'] == 'Status:') {

	                if ($key === 0) {
	                    
	                    //begin Diff New to First Status Change
	                    $datetime1 = strtotime($CaseCreated);
	                    $datetime2 = strtotime($value['date_created']);

	                    //BEGIN DAYS BETWEEN 1
	                    $start = new DateTime($CaseCreated);
	                    $end = new DateTime($value['date_created']);

	                    //echo "1st - Start: $CaseCreated, End: {$value['date_created']} <br />";

	                    //'days between' function
	                    $days = DaysBetween($daylength, $weekbegins, $weekends, $start, $end);

	                    //differences
	                    $dur1 = DiffHours($daybegins , $dayends , $daylength , $start, $end, $days);
	                    //END DAYS BETWEEN 1

	                    $prev_date = $value['date_created'];
	                    
	                    //adds this duration to appropriate bin arrays //
	                    if ($value['before_value_string'] == 'New' || $value['before_value_string'] == 'Internal' || $value['before_value_string'] == 'Assigned') {
	                        $int_dur += $dur1;
	                    }
	                    else {
	                        $ext_dur += $dur1;
	                    }
	                    $total_dur += $dur1;

	                    //end Diff New to First Status Change //

	                } else {
	                    //begin get previous Status Change Time//
	                    if ($prev_date !== null) {
	                        $datetime1 = strtotime($prev_date);
	                        $datetime2 = strtotime($value['date_created']);
	                        $dur1 = round(($datetime2-$datetime1)/60/60,2);

	                    //BEGIN DAYS BETWEEN 2
	                    $start = new DateTime($prev_date);
	                    $end = new DateTime($value['date_created']);

	                    //echo "Start: $prev_date, End: {$value['date_created']} <br />";

	                    //'days between' function
	                    $days = DaysBetween($daylength, $weekbegins, $weekends, $start, $end);

	                    //differences
	                    $dur1 = DiffHours($daybegins , $dayends , $daylength , $start, $end, $days);
	                    //END DAYS BETWEEN 2

	                    }
	                    $prev_date = $value['date_created'];
	                    
	                    //adds this duration to appropriate bins //
	                    if ($value['before_value_string'] == 'New' || $value['before_value_string'] == 'Internal' || $value['before_value_string'] == 'Assigned') {
	                        $int_dur += $dur1;
	                    }
	                    else {
	                        $ext_dur += $dur1;
	                    }
	                    $total_dur += $dur1;
	                    //end get previous Status Change Time//

	                }
	        }
	    }

	//Divides durations from total to product a percentage //
	if ($total_dur != 0) 
	    {
	        $int_percent = round(($int_dur/$total_dur)*100,1);
	        $ext_percent = round(($ext_dur/$total_dur)*100,1);
	    } else {
	        $int_percent = 0;
	        $ext_percent = 0;
	    }

	echo "Int D: $int_dur || Ext D: $ext_dur <br />";
	echo "Int %: $int_percent || Ext %: $ext_percent <br />";


	//Save time results to the bean
	$focus->scc_int_duration_c = $int_dur;
	$focus->scc_ext_duration_c = $ext_dur;
	$focus->scc_int_percent_c = $int_percent;
	$focus->scc_ext_percent_c = $ext_percent;
	$focus->save();

	}

}
