<?php

require_once('include/modules.php');
require_once('modules/Audit/Audit.php');
require_once('custom/modules/Cases/scc-functions.php');

global $focus;

//Pull list of cases to crunch here:
$bean_list = BeanFactory::getBean('Cases');
$case_list = $bean_list->get_full_list("", "cases.date_modified > ".db_convert("'".gmdate($GLOBALS['timedate']->get_db_date_time_format(), strtotime("- 30 days"))."'" ,"datetime"));

foreach($case_list as $key => $focus){

//Get single Case's Audit:
//$focus = BeanFactory::getBean("Cases", "62859f2e-7bc1-6aff-fefc-52f6f541138b");

	$audit_list =  Audit::get_audit_list();

	//Patricks code here:
	$CaseCreated = $focus->getFieldValue('date_entered');

	foreach(array_reverse($audit_list) as $key => $value) {

		if($value['field_name'] == 'Status:') {

	            if ($key === 0) {
	                
	                //begin Diff New to First Status Change
	                $datetime1 = strtotime($CaseCreated);
	                $datetime2 = strtotime($value['date_created']);

	                //BEGIN DAYS BETWEEN 1
	                $start = new DateTime($CaseCreated);
	                $end = new DateTime($value['date_created']);

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
}

//Divides durations from total to product a percentage //
if ($total_dur != 0) 
    {
        $ud_percent = round(($int_dur/$total_dur)*100,1);
        $cd_percent = round(($ext_dur/$total_dur)*100,1);
    } else {
        $ud_percent = 0;
        $cd_percent = 0;
    }

echo "<pre>".print_r($int_dur, true)." >> ".print_r($ext_dur, true)."</pre>";
echo "<pre>".print_r($ud_percent, true)." >> ".print_r($cd_percent, true)."</pre>";


//Save time results to the bean

?>