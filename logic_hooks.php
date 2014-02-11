<?php
// Do not store anything in this file that is not part of the array or the hook version.  This file will	
// be automatically rebuilt in the future. 
 $hook_version = 1; 
$hook_array = Array(); 
// position, file, function 
$hook_array['before_save'] = Array(); 
$hook_array['before_save'][] = Array('1','workflow','include/workflow/WorkFlowHandler.php','WorkFlowHandler','WorkFlowHandler',);
$hook_array['before_save'][] = Array('2','SugarCaseClock','custom/modules/Cases/SugarCaseClock_Hook.php','logic_hooks_class','before_save_method');

?>