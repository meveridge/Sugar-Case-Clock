<?php
    
	global $sugar_config;

    require_once('modules/Configurator/Configurator.php');
    
	$conf = new Configurator();

	$conf->config['caseStatusExternal'] = array('Pending','Closed','Rejected','Duplicate');
	$conf->config['caseStatusInternal'] = array('New','Assigned','Pending Input');
	$conf->handleOverride();

?>