<?php

///custom/Extension/modules/Administration/Ext/Administration/scc_admin.php

    $admin_option_defs = array();
    $admin_option_defs['Administration']['scc_admin_settings'] = array(
        //Icon name. Available icons are located in ./themes/default/images
        'dce_Settings',
        
        //Link name label 
        'LBL_SCC_ADMIN_SETTINGS_LINK_NAME',
        
        //Link description label
        'LB_SCC_ADMIN_SETTINGS_LINK_DESCRIPTION',
        
        //Link URL - For Sidecar modules
        //'javascript:parent.SUGAR.App.router.navigate("<module>/<path>", {trigger: true});',
        
        //Alternatively, if you are linking to BWC modules
        './index.php?module=Administration&action=sccAdminSettings',
    );
    $admin_option_defs['Administration']['scc_status_settings'] = array(
        //Icon name. Available icons are located in ./themes/default/images
        'dce_Settings',
        
        //Link name label 
        'LBL_SCC_STATUS_SETTINGS_LINK_NAME',
        
        //Link description label
        'LB_SCC_STATUS_SETTINGS_LINK_DESCRIPTION',
        
        //Link URL - For Sidecar modules
        //'javascript:parent.SUGAR.App.router.navigate("<module>/<path>", {trigger: true});',
        
        //Alternatively, if you are linking to BWC modules
        './index.php?module=Administration&action=sccStatusSettings',
    );

    $admin_group_header[] = array(
        //Section header label
        'LBL_SCC_ADMIN_SECTION_HEADER',
        
        //$other_text parameter for get_form_header()
        '',
        
        //$show_help parameter for get_form_header()
        false,
        
        //Section links
        $admin_option_defs, 
        
        //Section description label
        'LBL_SCC_ADMIN_SECTION_DESCRIPTION'
    );

