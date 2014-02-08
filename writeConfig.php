<?php
    
	global $sugar_config;

    require_once('modules/Configurator/Configurator.php');
    
	$conf = new Configurator();

	$overrideArray = $conf->readOverride();
	$conf->previous_sugar_override_config_array = $overrideArray;
	$diffArray = deepArrayDiff($conf->config, $sugar_config);
    $overrideArray = sugarArrayMerge($overrideArray, $diffArray);

	// To remember checkbox state
	if(isset($overrideArray['authenticationClass']) && empty($overrideArray['authenticationClass'])) {
		unset($overrideArray['authenticationClass']);
	}

	//Add custom config overrides
	$overrideArray['caseStatusExternal'] = array('Pending','Closed','Rejected','Duplicate');
	$overrideArray['caseStatusInternal'] = array('New','Assigned','Pending Input');

	//Convert Overrides to String to write to file
	$overideString = "<?php\n/***CONFIGURATOR***/\n";

	foreach($overrideArray as $key => $val) {
		if (in_array($key, $this->allow_undefined) || isset ($sugar_config[$key])) {
			if (is_string($val) && strcmp($val, 'true') == 0) {
				$val = true;
				$this->config[$key] = $val;
			}
			if (is_string($val) && strcmp($val, 'false') == 0) {
				$val = false;
				$this->config[$key] = false;
			}
		}
		$overideString .= override_value_to_string_recursive2('sugar_config', $key, $val);
	}
	$overideString .= '/***CONFIGURATOR***/';

	//Write to config_override.php
	$conf->saveOverride($overideString);

?>