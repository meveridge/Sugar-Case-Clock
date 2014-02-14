<?php

///custom/modules/Administration/sccAdminSettings.php

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('modules/Administration/Forms.php');
require_once('include/SubPanel/SubPanelDefinitions.php');
require_once('modules/MySettings/TabController.php');

global $mod_strings;
global $app_list_strings;
global $app_strings;

$enabled = array();
$disabled = array();

$status_list = $app_list_strings['case_status_dom'];
foreach($status_list as $status){
    if(in_array($status, $sugar_config['caseStatusInternal'])){
        $enabled[] = $status;
    }else{
        $disabled[] = $status;
    }
}



$this->ss->assign('APP', $GLOBALS['app_strings']);
$this->ss->assign('MOD', $GLOBALS['mod_strings']);
//$this->ss->assign('user_can_edit',  $user_can_edit);
$this->ss->assign('enabled_tabs', json_encode($enabled));
$this->ss->assign('disabled_tabs', json_encode($disabled));
$this->ss->assign('title',$this->getModuleTitle(false));

//get list of all subpanels and panels to hide 
$mod_list_strings_key_to_lower = array_change_key_case($app_list_strings['moduleList']);
$panels_arr = SubPanelDefinitions::get_all_subpanels();
$hidpanels_arr = SubPanelDefinitions::get_hidden_subpanels();


$this->ss->assign('enabled_panels', json_encode($enabled));
$this->ss->assign('disabled_panels', json_encode($disabled));

echo $this->ss->fetch('custom/modules/Administration/templates/sccAdminSettings.tpl');	

?>