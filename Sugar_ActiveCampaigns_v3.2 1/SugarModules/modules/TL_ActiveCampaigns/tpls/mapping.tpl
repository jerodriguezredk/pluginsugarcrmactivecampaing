<div style="width: 90%;margin: 0 auto;display: block;">
    {literal}
        <style type="text/css">
            .plugin_input {
                background-color: #ffffff;
                border: 1px solid #4E8CCF;
                padding: 10px 30px;
                font-size: 1.2em;
                margin-right: 10px;
            }

            .plugin-icon {
                font-size: 1.5rem;
                font-weight: bold;
                font-style: normal;
            }

            .plugin-plus, .plugin-times{
                height: 24px;
                width: 24px;
                display: inline-block;
                background-color: black;
                color: white;
                font-size: 24px;
                line-height: 24px;
                text-align: center;
                border: 1px solid #000;
                border-radius: 13px;
                box-shadow: 2px 2px 5px black;
            }

            .plugin-icon:hover{
                background-color: #ffffff;
                color: #000000;
            }

            .plugin-times:hover{
                background-color: #000000;
                color: #ffffff;
            }

            .plugin-times{
                color: red;
                background: transparent;
                font-style: normal;
                font-weight: normal;
            }

            .plugin-plus::before {
                content: "+";
            }

            .plugin-times::before {
                content: "x";
            }
        </style>
    {/literal}
    <div class="action_buttons">
        <div>
            <h1 class="title" style="margin-top: 1%;margin-bottom: 0.5%">{$MOD.LBL_MAP_FIELDS_WITH_ACTIVECAMPAIGN}</h1>
        </div>

        <div class="row">
            <div class="">

            </div>
        </div>

        <div class="module_list" style="height: 70px;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td>
                        <table width="100%" cellspacing="1" cellpadding="0" border="0" id="send_doc_table" class="edit view">
                            <tbody>
                            <tr>
                                <td height="10%" width="12.5%" valign="baseline" scope="row" id="password_label" style="font-size: 16px;font-weight: bold;">{$MOD.LBL_SELECT_MODULE}</td>
                                <td height="10%" width="37.5%" valign="baseline">
                                    <select class="plugin_input" id="sugar_module" name="sugar_module" style="width: 30%;">
                                        <option value="">{$MOD.LBL_NONE}</option>
                                        <option value="contacts">{$APP_LIST_STRINGS.moduleList.Contacts}</option>
                                        <option value="leads">{$APP_LIST_STRINGS.moduleList.Leads}</option>
                                        <option value="accounts">{$APP_LIST_STRINGS.moduleList.Accounts}</option>
                                        <option value="contracts">{$APP_LIST_STRINGS.moduleList.Contracts}</option>
                                    </select>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <div class="no_list" id="no_list">
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td colspan="3" width="115" valign="baseline">
                        <p>
                            <input type="button" class="button primary btn btn-primary" onclick="view_settings();" style="margin: 0 7px;" value=" Back to Settings ">
                        </p>
                    </td>
                </tr>
            </table>
    </div>

        <div class="leads_list" id="leads_list" style="display: none;">
            <form name="ConfigureSettings" id="SaveLeadMapping" method="POST" style="float: inherit !important;">
                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <input type="hidden" name="module" id="module" value="{$MODULE}">
                    <input type="hidden" name="return_module" value="{$RETURN_MODULE}">
                    <input type="hidden" name="return_action" value="{$RETURN_ACTION}">
                    <input type="hidden" name="source_form" value="config"/>
                    <tr>
                        <td>
                            <table width="100%" cellspacing="1" cellpadding="0" border="0" id="lead_field_table" class="edit view">
                                <tbody>
                                <tr>
                                    <td width="12.5%" valign="baseline" scope="row" id="password_label"></td>
                                    <td width="37.5%" valign="baseline">
                                        <span style="font-size: 16px;font-weight: bold;color: blue;">{$MOD.LBL_ACTIVECAMPAIGN_FIELDS}</span>
                                        <span style="margin-left: 245px;font-size: 16px;font-weight: bold;color: blue;">{$MOD.LBL_SUGARCRM_FIELDS} [{$APP_LIST_STRINGS.moduleList.Contacts}]</span>
                                    </td>
                                </tr>

                                <tr>
                                    <td width="12.5%" valign="baseline" scope="row" id="password_label"></td>
                                    <td width="37.5%" valign="baseline">

                                        <select class="plugin_input" name="campaignFieldsLead[]" style="width: 30%;">
                                            <option value="email">{$MOD.LBL_EMAIL}</option>
                                        </select>
                                        <select class="plugin_input" name="sugarLeadFields[]" style="width: 30%;">
                                            <option value="email">{$MOD.LBL_EMAIL}</option>
                                        </select>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <a href="#" class="add_more_lead" style="margin-left: -0.5%;"><i class="plugin-icon plugin-plus"></i></a>
                                    </td>
                                </tr>

                                <!-- if mapped data alredy avaiable then show that  -->
                                {if isset($leadMappedInfo) && !empty($leadMappedInfo)}
                                    {foreach from=$leadMappedInfo  key=mapKey item=mapValue}
                                        <tr>
                                            <td width="12.5%" valign="baseline" scope="row" id="password_label"></td>
                                            <td width="37.5%" valign="baseline">

                                                <select class="plugin_input" name="campaignFieldsLead[]" id="selectBoxLead" style="width: 30%;">
                                                    {foreach from=$campaign_custom_fields key=pluginFieldName item=pluginFieldText}
                                                        <option value="{$pluginFieldName}" {if $mapKey===$pluginFieldName} selected {/if}> {$pluginFieldText}</option>
                                                    {/foreach}
                                                </select>
                                                <select class="plugin_input" name="sugarLeadFields[]" id="selectBoxSugarLead" style="width: 30%;">
                                                    {foreach from=$sugar_leads_attributes key=fieldName item=fieldTranslatedName}
                                                        <option value="{$fieldName}" {if $mapValue===$fieldName} selected {/if}> {$fieldTranslatedName}</option>
                                                    {/foreach}
                                                </select>
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                <a href="#" class="add_more_lead" style="margin-left: -0.5%;"><i class="plugin-icon plugin-plus"></i></a>
                                                &nbsp;
                                                <a href="#" class="remove_lead" style="text-decoration:none; color:#F00;"><i class="plugin-icon plugin-times"></i></a>
                                            </td>
                                        </tr>
                                    {/foreach}
                                {else}
                                    <tr>
                                        <td width="12.5%" valign="baseline" scope="row" id="password_label"></td>
                                        <td width="37.5%" valign="baseline">

                                            <select class="plugin_input" name="campaignFieldsLead[]" id="selectBoxLead" style="width: 30%;">
                                                {foreach from=$campaign_custom_fields key=pluginFieldName item=pluginFieldText}
                                                    <option value="{$pluginFieldName}"> {$pluginFieldText}</option>
                                                {/foreach}
                                            </select>
                                            <select class="plugin_input" name="sugarLeadFields[]" id="selectBoxSugarLead" style="width: 30%;">
                                                {foreach from=$sugar_leads_attributes key=fieldName item=fieldTranslatedName}
                                                    <option value="{$fieldName}"> {$fieldTranslatedName}</option>
                                                {/foreach}
                                            </select>
                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                            <a href="#" class="add_more_lead" style="margin-left: -0.5%;"><i class="plugin-icon plugin-plus"></i></a>
                                            &nbsp;
                                            <a href="#" class="remove_lead" style="text-decoration:none; color:#F00;"><i class="plugin-icon plugin-times"></i></a>
                                        </td>
                                    </tr>
                                {/if}
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" width="115" valign="baseline">
                            <p>
                                <input type="button" class="button primary btn btn-primary" onclick="view_settings();" style="margin: 0 7px;" value=" Back to Settings ">
                                <input title="{$APP.LBL_SAVE_BUTTON_TITLE}" class="button primary btn btn-success" onclick="return store_lead_field_setting();" type="button" name="button" value=" {$APP.LBL_SAVE_BUTTON_LABEL} ">
                                <b style="color:#F00;" class="message"> </b>
                                <b style="color:#3bb300;" class="success"> </b>
                            </p>
                        </td>
                    </tr>
                    {* <tr>
                        <td colspan="3" width="115" valign="baseline">
                            <span>
                            <b style="color:#F00;" class="message"> </b>
                            <b style="color:#3bb300;" class="success"> </b>
                            </span>
                        </td>
                    </tr> *}
                </table>
            </form>
        </div>

        <div class="contacts_list" id="contacts_list" style="display: none;">
            <form name="ConfigureSettings" id="SaveContactMapping" method="POST" style="float: inherit !important;">
                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <input type="hidden" name="module" id="module" value="{$MODULE}">
                    <input type="hidden" name="return_module" value="{$RETURN_MODULE}">
                    <input type="hidden" name="return_action" value="{$RETURN_ACTION}">
                    <input type="hidden" name="source_form" value="config"/>
                    <tr>
                        <td>
                            <table width="100%" cellspacing="1" cellpadding="0" border="0" id="contact_field_table" class="edit view">
                                <tbody>
                                <tr>
                                    <td width="12.5%" valign="baseline" scope="row" id="password_label"></td>
                                    <td width="37.5%" valign="baseline">
                                        <span style="font-size: 16px;font-weight: bold;color: blue;">{$MOD.LBL_ACTIVECAMPAIGN_FIELDS}</span>
                                        <span style="margin-left: 245px;font-size: 16px;font-weight: bold;color: blue;">{$MOD.LBL_SUGARCRM_FIELDS} [{$APP_LIST_STRINGS.moduleList.Contacts}]</span>
                                    </td>
                                </tr>

                                <tr>
                                    <td width="12.5%" valign="baseline" scope="row" id="password_label"></td>
                                    <td width="37.5%" valign="baseline">

                                        <select class="plugin_input" name="campaignFieldsContact[]" style="width: 30%;">
                                            <option value="email">{$MOD.LBL_EMAIL}</option>
                                        </select>
                                        <select class="plugin_input" name="sugarContactFields[]" style="width: 30%;">
                                            <option value="email">{$MOD.LBL_EMAIL}</option>
                                        </select>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <a href="#" class="add_more_contact" style="margin-left: -0.5%;"><i class="plugin-icon plugin-plus"></i></a>
                                    </td>
                                </tr>

                                <!-- if mapped data alredy avaiable then show that  -->
                                {if isset($contactMappedInfo) && !empty($contactMappedInfo)}
                                    {foreach from=$contactMappedInfo  key=mapKey item=mapValue}
                                        <tr>
                                            <td width="12.5%" valign="baseline" scope="row" id="password_label"></td>
                                            <td width="37.5%" valign="baseline">

                                                <select class="plugin_input" name="campaignFieldsContact[]" id="selectBoxContact" style="width: 30%;">
                                                    {foreach from=$campaign_custom_fields key=pluginFieldName item=pluginFieldText}
                                                        <option value="{$pluginFieldName}" {if $mapKey===$pluginFieldName} selected {/if}> {$pluginFieldText}</option>
                                                    {/foreach}
                                                </select>
                                                <select class="plugin_input" name="sugarContactFields[]" id="selectBoxSugarContact" style="width: 30%;">
                                                    {foreach from=$sugar_contacts_attributes key=fieldName item=fieldTranslatedName}
                                                        <option value="{$fieldName}" {if $mapValue===$fieldName} selected {/if}> {$fieldTranslatedName}</option>
                                                    {/foreach}
                                                </select>
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                <a href="#" class="add_more_contact" style="margin-left: -0.5%;"><i class="plugin-icon plugin-plus"></i></a>
                                                &nbsp;
                                                <a href="#" class="remove_contact" style="text-decoration:none; color:#F00;"><i class="plugin-icon plugin-times"></i></a>
                                            </td>
                                        </tr>
                                    {/foreach}
                                {else}
                                    <tr>
                                        <td width="12.5%" valign="baseline" scope="row" id="password_label"></td>
                                        <td width="37.5%" valign="baseline">

                                            <select class="plugin_input" name="campaignFieldsContact[]" id="selectBoxContact" style="width: 30%;">
                                                {foreach from=$campaign_custom_fields key=pluginFieldName item=pluginFieldText}
                                                    <option value="{$pluginFieldName}"> {$pluginFieldText}</option>
                                                {/foreach}
                                            </select>
                                            <select class="plugin_input" name="sugarContactFields[]" id="selectBoxSugarContact" style="width: 30%;">
                                                {foreach from=$sugar_contacts_attributes key=fieldName item=fieldTranslatedName}
                                                    <option value="{$fieldName}"> {$fieldTranslatedName}</option>
                                                {/foreach}
                                            </select>
                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                            <a href="#" class="add_more_contact" style="margin-left: -0.5%;"><i class="plugin-icon plugin-plus"></i></a>
                                            &nbsp;
                                            <a href="#" class="remove_contact" style="text-decoration:none; color:#F00;"><i class="plugin-icon plugin-times"></i></a>
                                        </td>
                                    </tr>
                                {/if}
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" width="115" valign="baseline">
                            <p>
                                <input type="button" class="button primary btn btn-primary" onclick="view_settings();" style="margin: 0 7px;" value=" Back to Settings ">
                                <input title="{$APP.LBL_SAVE_BUTTON_TITLE}" class="button primary btn btn-success" onclick="return store_contact_field_setting();" type="button" name="button" value=" {$APP.LBL_SAVE_BUTTON_LABEL} ">
                                <b style="color:#F00;" class="message"> </b>
                                <b style="color:#3bb300;" class="success"> </b>
                            </p>
                        </td>
                    </tr>
                    {* <tr>
                        <td colspan="3" width="115" valign="baseline">
                            <span>
                            <b style="color:#F00;" class="message"> </b>
                            <b style="color:#3bb300;" class="success"> </b>
                            </span>
                        </td>
                    </tr> *}
                </table>
            </form>
        </div>

        <div class="account_list" id="account_list" style="display: none;">
            <form name="ConfigureSettings" id="SaveAccountMapping" method="POST" style="float: inherit !important;">
                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <input type="hidden" name="module" id="module" value="{$MODULE}">
                    <input type="hidden" name="return_module" value="{$RETURN_MODULE}">
                    <input type="hidden" name="return_action" value="{$RETURN_ACTION}">
                    <input type="hidden" name="source_form" value="config"/>
                    <tr>
                        <td>
                            <table width="100%" cellspacing="1" cellpadding="0" border="0" id="account_field_table" class="edit view">
                                <tbody>
                                <tr>
                                    <td width="12.5%" valign="baseline" scope="row" id="password_label"></td>
                                    <td width="37.5%" valign="baseline">
                                        <span style="font-size: 16px;font-weight: bold;color: blue;">{$MOD.LBL_ACTIVECAMPAIGN_FIELDS}</span>
                                        <span style="margin-left: 245px;font-size: 16px;font-weight: bold;color: blue;">{$MOD.LBL_SUGARCRM_FIELDS} [{$APP_LIST_STRINGS.moduleList.Contacts}]</span>
                                    </td>
                                </tr>

                                <tr>
                                    <td width="12.5%" valign="baseline" scope="row" id="password_label"></td>
                                    <td width="37.5%" valign="baseline">

                                        <select class="plugin_input" name="campaignFieldsAccount[]" style="width: 30%;">
                                            <option value="email">{$MOD.LBL_EMAIL}</option>
                                        </select>
                                        <select class="plugin_input" name="sugarAccountFields[]" style="width: 30%;">
                                            <option value="email">{$MOD.LBL_EMAIL}</option>
                                        </select>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <a href="#" class="add_more_account" style="margin-left: -0.5%;"><i class="plugin-icon plugin-plus"></i></a>
                                    </td>
                                </tr>

                                <!-- if mapped data alredy avaiable then show that  -->
                                {if isset($accountMappedInfo) && !empty($accountMappedInfo)}
                                    {foreach from=$accountMappedInfo  key=mapKey item=mapValue}
                                        <tr>
                                            <td width="12.5%" valign="baseline" scope="row" id="password_label"></td>
                                            <td width="37.5%" valign="baseline">

                                                <select class="plugin_input" name="campaignFieldsAccount[]" id="selectBoxAccount" style="width: 30%;">
                                                    {foreach from=$campaign_custom_fields key=pluginFieldName item=pluginFieldText}
                                                        <option value="{$pluginFieldName}" {if $mapKey===$pluginFieldName} selected {/if}> {$pluginFieldText}</option>
                                                    {/foreach}
                                                </select>
                                                <select class="plugin_input" name="sugarAccountFields[]" id="selectBoxSugarAccount" style="width: 30%;">
                                                    {foreach from=$sugar_accounts_attributes key=fieldName item=fieldTranslatedName}
                                                        <option value="{$fieldName}" {if $mapValue===$fieldName} selected {/if}> {$fieldTranslatedName}</option>
                                                    {/foreach}
                                                </select>
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                <a href="#" class="add_more_account" style="margin-left: -0.5%;"><i class="plugin-icon plugin-plus"></i></a>
                                                &nbsp;
                                                <a href="#" class="remove_account" style="text-decoration:none; color:#F00;"><i class="plugin-icon plugin-times"></i></a>
                                            </td>
                                        </tr>
                                    {/foreach}
                                {else}
                                    <tr>
                                        <td width="12.5%" valign="baseline" scope="row" id="password_label"></td>
                                        <td width="37.5%" valign="baseline">

                                            <select class="plugin_input" name="campaignFieldsAccount[]" id="selectBoxAccount" style="width: 30%;">
                                                {foreach from=$campaign_custom_fields key=pluginFieldName item=pluginFieldText}
                                                    <option value="{$pluginFieldName}"> {$pluginFieldText}</option>
                                                {/foreach}
                                            </select>
                                            <select class="plugin_input" name="sugarAccountFields[]" id="selectBoxSugarAccount" style="width: 30%;">
                                                {foreach from=$sugar_accounts_attributes key=fieldName item=fieldTranslatedName}
                                                    <option value="{$fieldName}"> {$fieldTranslatedName}</option>
                                                {/foreach}
                                            </select>
                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                            <a href="#" class="add_more_account" style="margin-left: -0.5%;"><i class="plugin-icon plugin-plus"></i></a>
                                            &nbsp;
                                            <a href="#" class="remove_account" style="text-decoration:none; color:#F00;"><i class="plugin-icon plugin-times"></i></a>
                                        </td>
                                    </tr>
                                {/if}
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" width="115" valign="baseline">
                            <p>
                                <input type="button" class="button primary btn btn-primary" onclick="view_settings();" style="margin: 0 7px;" value=" Back to Settings ">
                                <input title="{$APP.LBL_SAVE_BUTTON_TITLE}" class="button primary btn btn-success" onclick="return store_account_field_setting();" type="button" name="button" value=" {$APP.LBL_SAVE_BUTTON_LABEL} ">
                                <b style="color:#F00;" class="message"> </b>
                                <b style="color:#3bb300;" class="success"> </b>
                            </p>
                        </td>
                    </tr>
                </table>
            </form>
        </div>

        <div class="contracts_list" id="contracts_list" style="display: none;">
            <form name="ConfigureSettings" id="SaveContractMapping" method="POST" style="float: inherit !important;">
                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <input type="hidden" name="module" id="module" value="{$MODULE}">
                    <input type="hidden" name="return_module" value="{$RETURN_MODULE}">
                    <input type="hidden" name="return_action" value="{$RETURN_ACTION}">
                    <input type="hidden" name="source_form" value="config"/>
                    <tr>
                        <td>
                            <table width="100%" cellspacing="1" cellpadding="0" border="0" id="contract_field_table" class="edit view">
                                <tbody>
                                <tr>
                                    <td width="12.5%" valign="baseline" scope="row" id="password_label"></td>
                                    <td width="37.5%" valign="baseline">
                                        <span style="font-size: 16px;font-weight: bold;color: blue;">{$MOD.LBL_ACTIVECAMPAIGN_FIELDS}</span>
                                        <span style="margin-left: 245px;font-size: 16px;font-weight: bold;color: blue;">{$MOD.LBL_SUGARCRM_FIELDS} [{$APP_LIST_STRINGS.moduleList.Contacts}]</span>
                                    </td>
                                </tr>

                                <tr>
                                    <td width="12.5%" valign="baseline" scope="row" id="password_label"></td>
                                    <td width="37.5%" valign="baseline">

                                        <select class="plugin_input" name="campaignFieldsContract[]" style="width: 30%;">
                                            <option value="email">{$MOD.LBL_EMAIL}</option>
                                        </select>
                                        <select class="plugin_input" name="sugarContractFields[]" style="width: 30%;">
                                            <option value="email">{$APP_LIST_STRINGS.moduleListSingular.Accounts} {$MOD.LBL_EMAIL}</option>
                                        </select>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <a href="#" class="add_more_contract" style="margin-left: -0.5%;"><i class="plugin-icon plugin-plus"></i></a>
                                    </td>
                                </tr>

                                <!-- if mapped data alredy avaiable then show that  -->
                                {if isset($contractMappedInfo) && !empty($contractMappedInfo)}
                                    {foreach from=$contractMappedInfo  key=mapKey item=mapValue}
                                        <tr>
                                            <td width="12.5%" valign="baseline" scope="row" id="password_label"></td>
                                            <td width="37.5%" valign="baseline">

                                                <select class="plugin_input" name="campaignFieldsContract[]" id="selectBoxContract" style="width: 30%;">
                                                    {foreach from=$campaign_custom_fields key=pluginFieldName item=pluginFieldText}
                                                        <option value="{$pluginFieldName}" {if $mapKey===$pluginFieldName} selected {/if}> {$pluginFieldText}</option>
                                                    {/foreach}
                                                </select>
                                                <select class="plugin_input" name="sugarContractFields[]" id="selectBoxSugarContract" style="width: 30%;">
                                                    {foreach from=$sugar_contracts_attributes key=fieldName item=fieldTranslatedName}
                                                        <option value="{$fieldName}" {if $mapValue===$fieldName} selected {/if}> {$fieldTranslatedName}</option>
                                                    {/foreach}
                                                </select>
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                <a href="#" class="add_more_contract" style="margin-left: -0.5%;"><i class="plugin-icon plugin-plus"></i></a>
                                                &nbsp;
                                                <a href="#" class="remove_contract" style="text-decoration:none; color:#F00;"><i class="plugin-icon plugin-times"></i></a>
                                            </td>
                                        </tr>
                                    {/foreach}
                                {else}
                                    <tr>
                                        <td width="12.5%" valign="baseline" scope="row" id="password_label"></td>
                                        <td width="37.5%" valign="baseline">

                                            <select class="plugin_input" name="campaignFieldsContract[]" id="selectBoxContract" style="width: 30%;">
                                                {foreach from=$campaign_custom_fields key=pluginFieldName item=pluginFieldText}
                                                    <option value="{$pluginFieldName}"> {$pluginFieldText}</option>
                                                {/foreach}
                                            </select>
                                            <select class="plugin_input" name="sugarContractFields[]" id="selectBoxSugarContract" style="width: 30%;">
                                                {foreach from=$sugar_contracts_attributes key=fieldName item=fieldTranslatedName}
                                                    <option value="{$fieldName}"> {$fieldTranslatedName}</option>
                                                {/foreach}
                                            </select>
                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                            <a href="#" class="add_more_contract" style="margin-left: -0.5%;"><i class="plugin-icon plugin-plus"></i></a>
                                            &nbsp;
                                            <a href="#" class="remove_contract" style="text-decoration:none; color:#F00;"><i class="plugin-icon plugin-times"></i></a>
                                        </td>
                                    </tr>
                                {/if}
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" width="115" valign="baseline">
                            <p>
                                <input type="button" class="button primary btn btn-primary" onclick="view_settings();" style="margin: 0 7px;" value=" Back to Settings ">
                                <input title="{$APP.LBL_SAVE_BUTTON_TITLE}" class="button primary btn btn-success" onclick="return store_contract_field_setting();" type="button" name="button" value=" {$APP.LBL_SAVE_BUTTON_LABEL} ">
                                <b style="color:#F00;" class="message"> </b>
                                <b style="color:#3bb300;" class="success"> </b>
                            </p>
                        </td>
                    </tr>
                    {* <tr>
                        <td colspan="3" width="115" valign="baseline">
                            <span>
                            <b style="color:#F00;" class="message"> </b>
                            <b style="color:#3bb300;" class="success"> </b>
                            </span>
                        </td>
                    </tr> *}
                </table>
            </form>
        </div>
    </div>
    {$JAVASCRIPT}
{literal}
    <script>
        function view_settings() {
            var app = window.parent.SUGAR.App;
            app.router.navigate("#bwc/index.php?module=TL_ActiveCampaigns&action=config", {trigger: true, replace: true});
        }
    </script>
{/literal}
</div>