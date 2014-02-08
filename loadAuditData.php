<?php

require_once('include/modules.php');
require_once('modules/Audit/Audit.php');

global $focus;

//Pull list of cases to crunch here:



//Get single Case's Audit:

$focus = BeanFactory::getBean("Cases", "45b60855-49b4-b4eb-dc77-52f5d2cc8800");

$audit_list =  Audit::get_audit_list();

echo "<pre>".print_r($audit_list, true)."</pre>";

//Patricks code here:

?>