<?php

	require_once('sugarCaseClock.class.php');
	$sugar_case_clock = new sugarCaseClock();


	global $focus;

	//Pull list of cases to crunch here:
	$case_list = $sugar_case_clock->getRecentModifiedCases();

	foreach($case_list as $key => $focus){
		echo"<br />Case: {$focus->name}<br />";
	    $status_audit = $sugar_case_clock->getAuditData();

	    //Without, every Case without audit data for Status will throw a PHP Notice error
	    if (count($status_audit) == 0) continue;

		$results = array();

	    $auditStartTime = $focus->getFieldValue('date_entered');
echo"Process Audit Data (Date Created): $auditStartTime<br />";
	    foreach($status_audit as $key => $value) {

	    	//TODO: What if they change the label of the status field?
			if($value['field_name'] != 'Status:') continue;

	    	//process audit data
	    	echo"Process Audit Data (From: {$value['before_value_string']} to: {$value['after_value_string']}): {$value['date_created']}<br />";
	    	$results = $sugar_case_clock->processAuditData($auditStartTime,$key,$value,$results);
	    }
echo"Total Dur: {$results['total_dur']}<br />";
		//Divides durations from total to product a percentage //
		if ($results['total_dur'] != 0){
	        $int_percent = round(($results['int_dur']/$results['total_dur'])*100,1);
	        $ext_percent = round(($results['ext_dur']/$results['total_dur'])*100,1);
	    } else {
	        $int_percent = 0;
	        $ext_percent = 0;
	    }

		echo "Int D: {$results['int_dur']} || Ext D: {$results['ext_dur']} <br />";
		echo "Int %: $int_percent || Ext %: $ext_percent <br />";

		//Save time results to the bean
		$focus->scc_int_duration_c = $results['int_dur'];
		$focus->scc_ext_duration_c = $results['ext_dur'];
		$focus->scc_int_percent_c = $int_percent;
		$focus->scc_ext_percent_c = $ext_percent;
		$focus->save();
	}


?>