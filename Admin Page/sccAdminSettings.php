<?php

///custom/modules/Administration/sccAdminSettings.php

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('modules/Administration/Forms.php');

if($_POST){

	$toDecode_internal = html_entity_decode  ($_REQUEST['internal_statuses'], ENT_QUOTES);
	$internal_statuses = json_decode($toDecode_internal);
	$toDecode_external = html_entity_decode  ($_REQUEST['external_statuses'], ENT_QUOTES);
	$external_statuses = json_decode($toDecode_external);

	global $sugar_config;

    require_once('modules/Configurator/Configurator.php');
    
	$conf = new Configurator();
	$conf->config['caseStatusInternal'] = $internal_statuses;
	$conf->config['caseStatusExternal'] = $external_statuses;
	//$conf->config['caseStatusExternal'] = array('Pending','Closed','Rejected','Duplicate');
	//$conf->config['caseStatusInternal'] = array('New','Assigned','Pending Input');
	$conf->handleOverride();
}

global $mod_strings;
global $app_list_strings;
global $app_strings;

$internal = array();
$external = array();

$this->ss->assign('APP', $GLOBALS['app_strings']);
$this->ss->assign('MOD', $GLOBALS['mod_strings']);
//$this->ss->assign('user_can_edit',  $user_can_edit);
$this->ss->assign('internal_statuses', json_encode($internal));
$this->ss->assign('external_statuses', json_encode($external));
$this->ss->assign('title',$this->getModuleTitle(false));

echo $this->ss->fetch('custom/modules/Administration/templates/sccAdminSettings.tpl');	

?>