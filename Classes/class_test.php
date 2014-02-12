<?php

	require_once('sugarCaseClock.class.php');
	$sugar_case_clock = new sugarCaseClock();


	global $focus;

	//Pull list of cases to crunch here:
	$case_list = $sugar_case_clock->getRecentModifiedCases();

	foreach($case_list as $key => $focus){
		echo"<br />Case: {$focus->name}<br />";
	    $status_audit = $sugar_case_clock->getAuditData();

		$results = array();

	    $auditStartTime = $focus->getFieldValue('date_entered');

	    foreach($status_audit as $key => $value) {

	    	//TODO: What if they change the label of the status field?
			if($value['field_name'] != 'Status:') continue;

	    	//process audit data
	    	echo"Process Audit Data (From: {$value['before_value_string']} to: {$value['after_value_string']}): $auditStartTime<br />";
	    	$results = $sugar_case_clock->processAuditData($auditStartTime,$key,$value,$results);
	    }

		//Divides durations from total to product a percentage //
		if ($results['total_dur'] != 0){
	        $int_percent = round(($results['int_dur']/$results['total_dur'])*100,1);
	        $ext_percent = round(($results['ext_dur']/$results['total_dur'])*100,1);
	    } else {
	        $int_percent = 0;
	        $ext_percent = 0;
	    }

		echo "Int D: {$results['int_dur']} || Ext D: {$results['ext_dur']} <br />";
		echo "Int %: {$results['int_percent']} || Ext %: {$results['ext_percent']} <br />";

		//Save time results to the bean
		$focus->scc_int_duration_c = $results['int_dur'];
		$focus->scc_ext_duration_c = $results['ext_dur'];
		$focus->scc_int_percent_c = $int_percent;
		$focus->scc_ext_percent_c = $ext_percent;
		$focus->save();
	}


?>