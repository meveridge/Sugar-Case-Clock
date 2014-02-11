<?php

    if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
   
    class logic_hooks_class
    {
        function before_save_method($bean, $event, $arguments)
        {
            
        //if($bean->$status != $bean->fetched_row[$status] && $bean->$status == 'Closed') {

        	//CONFIG INFO

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

			$prev_date = null;

			//durations belonging to users and clients
			$int_dur = 0;
			$ext_dur = 0;
			$total_dur = 0;

			require_once('include/modules.php');
			require_once('modules/Audit/Audit.php');
			require_once('custom/modules/Cases/scc-functions.php');

			global $focus;

			//Pull list of cases to crunch here:



			//Get single Case's Audit:

			$focus = $bean;
			$status_audit = array();

			$audit_list =  Audit::get_audit_list();

			foreach(array_reverse($audit_list) as $key => $value) {
				if($value['field_name'] == 'Status:') {
					array_push($status_audit, $value);
				}
			}

			//Patricks code here:
			$CaseCreated = $bean->getFieldValue('date_entered');

			foreach($status_audit as $key => $value) {

				if($value['field_name'] == 'Status:') {

			            if ($key === 0) {
			                
			                //begin Diff New to First Status Change
			                $datetime1 = strtotime($CaseCreated);
			                $datetime2 = strtotime($value['date_created']);
			                $dur1 = round(($datetime2-$datetime1)/60/60,5);

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
			                    $dur1 = round(($datetime2-$datetime1)/60/60,5);

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

			//Divides durations from total to product a percentage //
			if ($total_dur != 0) 
			    {
			        $int_percent = round(($int_dur/$total_dur)*100,5);
			        $ext_percent = round(($ext_dur/$total_dur)*100,5);
			    } else {
			        $int_percent = 0;
			        $ext_percent = 0;
			    }

			echo "<pre>".print_r($int_dur, true)." >> ".print_r($ext_dur, true)."</pre>";
			echo "<pre>".print_r($int_percent, true)." >> ".print_r($ext_percent, true)."</pre>";

			//Save time results to the bean
			$bean->scc_int_duration_c = $int_dur;
			$bean->scc_ext_duration_c = $ext_dur;
			$bean->scc_int_percent_c = $int_percent;
			$bean->scc_ext_percent_c = $ext_percent;
			//$bean->save();

		//}
        }
    }

?>