{*
///custom/modules/Administration/templates/sccAdminSettings.tpl
*}

<link rel="stylesheet" type="text/css" href="{sugar_getjspath file='modules/Connectors/tpls/tabs.css'}"/>
<script type="text/javascript" src="cache/include/javascript/sugar_grp_yui_widgets.js"></script>

<form name="ConfigureCaseStatuses" method="POST"  method="POST" action="index.php">
<input type="hidden" name="module" value="Administration">
<input type="hidden" name="action" value="sccStatusSettings">
<input type="hidden" id="internal_statuses" name="internal_statuses" value="">
<input type="hidden" id="external_statuses" name="external_statuses" value="">
<input type="hidden" name="return_module" value="{$RETURN_MODULE}">
<input type="hidden" name="return_action" value="{$RETURN_ACTION}">

<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr><td colspan='100'><h2>{$title}</h2></td></tr>
<tr><td colspan='100'>{$MOD.LB_SCC_ADMIN_SETTINGS_LINK_DESCRIPTION}</td></tr>
<tr><td><br></td></tr>
<tr><td colspan='100'>
	<table border="0" cellspacing="1" cellpadding="1" class="actionsContainer">
		<tr>
			<td>
				<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="button primary" onclick="SUGAR.saveConfigureStatuses();this.form.action.value='sccStatusSettings'; " type="submit" name="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}" > 
				<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="button" onclick="this.form.action.value='index'; this.form.module.value='Administration';" type="submit" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}">
			</td>
		</tr>
	</table>

	<table width="100%" border="0" cellspacing="1" cellpadding="0" class="edit view">
	<tr>
		<th align="left" scope="row" colspan="4"><h4>{$MOD.DEFAULT_SYSTEM_SETTINGS}</h4></th>
	</tr>

		<tr>
			<td  scope="row">{$MOD.LBL_SCC_DAY_BEGINS}: </td>
			<td  >
				<input type='text' size='4' id='ConfigureSettings_list_max_entries_per_page' name='list_max_entries_per_page' value='{$config.list_max_entries_per_page}'>
			</td>
			<td  scope="row">{$MOD.LBL_SCC_DAY_END}: </td>
			<td  >
				<input type='text' size='4' id='ConfigureSettings_list_max_entries_per_subpanel' name='list_max_entries_per_subpanel' value='{$config.list_max_entries_per_subpanel}'>
			</td>
		</tr>
		<tr>
			<td  scope="row">{$MOD.LBL_SCC_WEEK_BEGINS}: </td>
			<td  >
				<input type='text' size='4' id='ConfigureSettings_list_max_entries_per_page' name='list_max_entries_per_page' value='{$config.list_max_entries_per_page}'>
			</td>
			<td  scope="row">{$MOD.LBL_SCC_WEEK_ENDS}: </td>
			<td  >
				<input type='text' size='4' id='ConfigureSettings_list_max_entries_per_subpanel' name='list_max_entries_per_subpanel' value='{$config.list_max_entries_per_subpanel}'>
			</td>
		</tr>
		<tr>
			<td  scope="row">{$MOD.LBL_SCC_SCHEDULER_INTERVAL}: </td>
			<td  >
				<input type='text' size='4' id='ConfigureSettings_list_max_entries_per_page' name='list_max_entries_per_page' value='{$config.list_max_entries_per_page}'>
			</td>
		</tr>
	</table>

	<table border="0" cellspacing="1" cellpadding="1" class="actionsContainer">
		<tr>
			<td>
				<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" class="button primary" onclick="SUGAR.saveConfigureStatuses();this.form.action.value='sccStatusSettings'; " type="submit" name="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}" >
				<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" class="button" onclick="this.form.action.value='index'; this.form.module.value='Administration';" type="submit" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}">
			</td>
		</tr>
	</table>
</td></tr>
</table>	
</form>

<script type="text/javascript">
	var internal_statuses = {$internal_statuses};
	var external_statuses = {$external_statuses};
	var lblInternal = '{sugar_translate label="LBL_SCC_INTERNAL_STATUS"}';
	var lblExternal = '{sugar_translate label="LBL_SCC_EXTERNAL_STATUS"}';
	{literal}
	
	SUGAR.internalStatusesTable = new YAHOO.SUGAR.DragDropTable(
		"internal_statuses_div",
		[{key:"label",  label: lblInternal, width: 200, sortable: false},
		 {key:"module", label: lblInternal, hidden:true}],
		new YAHOO.util.LocalDataSource(internal_statuses, {
			responseSchema: {
			   resultsList : "modules",
			   fields : [{key : "module"}, {key : "label"}]
			}
		}), 
		{
			height: "300px",
			group: ["internal_statuses_div", "external_statuses_div"]
		}
	);
	SUGAR.externalStatusesTable = new YAHOO.SUGAR.DragDropTable(
		"external_statuses_div",
		[{key:"label",  label: lblExternal, width: 200, sortable: false},
		 {key:"module", label: lblExternal, hidden:true}],
		new YAHOO.util.LocalDataSource(external_statuses, {
			responseSchema: {
			   resultsList : "modules",
			   fields : [{key : "module"}, {key : "label"}]
			}
		}),
		{
			height: "300px",
		 	group: ["internal_statuses_div", "external_statuses_div"]
		 }
	);
	SUGAR.internalStatusesTable.disableEmptyRows = true;
    SUGAR.externalStatusesTable.disableEmptyRows = true;
    SUGAR.internalStatusesTable.addRow({module: "", label: ""});
    SUGAR.externalStatusesTable.addRow({module: "", label: ""});
	SUGAR.internalStatusesTable.render();
	SUGAR.externalStatusesTable.render();

	SUGAR.saveConfigureStatuses = function()
	{
		var internalTable = SUGAR.internalStatusesTable;
		var modules = [];
		for(var i=0; i < internalTable.getRecordSet().getLength(); i++){
			var data = internalTable.getRecord(i).getData();
			if (data.module && data.module != '')
			    modules[i] = data.module;
		}
		YAHOO.util.Dom.get('internal_statuses').value = YAHOO.lang.JSON.stringify(modules);
		
		var externalTable = SUGAR.externalStatusesTable;
		var modules = [];
		for(var i=0; i < externalTable.getRecordSet().getLength(); i++){
			var data = externalTable.getRecord(i).getData();
			if (data.module && data.module != '')
			    modules[i] = data.module;
		}
		YAHOO.util.Dom.get('external_statuses').value = YAHOO.lang.JSON.stringify(modules);
	}
{/literal}
</script>