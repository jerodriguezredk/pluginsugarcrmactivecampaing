<div style="width: 90%;margin: 0 auto;display: block;">
    {literal}
        <style type="text/css">
            .plugin_input {
                background-color: #ffffff;
                border: 1px solid #4E8CCF;
                padding: 6px 15px;
                font-size: 1.2em;
                margin-right: 10px;
            }
            .tcenter{
                text-align: center;
            }
            .tright{
                text-align: right;
            }
            .tleft{
                text-align: left;
            }
            .tlink{
                color: #0679c8;
                font-weight: bold;
            }
            .tHeaderEle{
                font-weight: bold;
                display: block;
                padding: 12px;
            }
            .tbold{
                vertical-align: middle;
                font-weight: bold;
            }
            .tdElem{
                padding: 12px !important;
            }
            .borderedRow{
                border-bottom: 1px solid #000000 !important;
                display: block;
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
    <br/>
    <div>
        <h1>{$MOD.LBL_ACTIVE_CAMPAIGN_CONFIGURATION}</h1>
    </div>
    <br/>
    <form name="ConfigureSettings" id="EditView" method="POST">
        <input type="hidden" name="module" id="module" value="{$MODULE}">
        <input type="hidden" name="return_module" value="{$RETURN_MODULE}">
        <input type="hidden" name="return_action" value="{$RETURN_ACTION}">
        <input type="hidden" name="source_form" value="config"/>
        <input type="hidden" id="authenticated" value="{$authenticated}"/>
        <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr class="borderedRow">
                <td align="left"><span class="required">{$APP.LBL_REQUIRED_SYMBOL}</span> {$APP.NTC_REQUIRED} </td>
            </tr>
            <tr class="borderedRow">
                <td style="display: block; width: 100%; padding: 10px">
                    <table width="100%" cellspacing="1" cellpadding="0" border="0" class="edit view">
                        <tbody>
                            <tr>
                                <td width="10%" scope="row" id="LBL_API_URL">
                                    {$MOD.LBL_API_URL}
                                    <span class="required" id="lbl_api_url">*</span>
                                </td>
                                <td width="90%" colspan="2">
                                    <input class="plugin_input" type="text" title="" required value="{$api_url}" maxlength="255" size="30" id="api_url" name="api_url">
                                </td>
                            </tr>
                            <tr>
                                <td width="10%" scope="row" id="secret_key_label"> {$MOD.LBL_API_KEY} <span class="required" id="lbl_api_key">*</span></td>
                                <td width="25%" >
                                    <input class="plugin_input" type="text" title="" required value="{$api_key}" maxlength="255" size="30" id="api_key" name="api_key">
                                </td>
                                <td width="65%" style="vertical-align: middle">
                                  <span id="check_api_status" class="sugar_field">
                                    <span>
                                        <input title="{$MOD.LBL_AUTHENTICATE}" class="button primary" onclick="return check_campaign_api();" type="button" name="button" value="{$MOD.LBL_AUTHENTICATE}">
                                        <img style=" display:none;" id="loading" src="themes/default/images/loading.gif" height="14px"/>
                                    </span>
                                  </span>
                                </td>

                            </tr>
                            <tr>
                                <td colspan="3">
                                    <span>
                                        <b style="color:#F00;" id="message">{if $display_error!==null}{$display_error}{/if}</b>
                                        <b style="color:#3bb300;" id="success"> {if $authenticated===true} {$MOD.LBL_AUTHENTICATED} {/if} </b>
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <!--------- syncing setting --------------->
            <tr {if $authenticated===true} {else} style="display: none;" {/if}>
                <td colspan="3" width="115" valign="top">
                    <table width="100%" cellpadding="0" cellspacing="0" border="0" class="edit view" style="margin-top: 10px;">
                        <thead class="tHeader">
                            <tr>
                                <td class="tHeaderEle"></td>
                                <td class="tcenter">
                                    <span class="tHeaderEle">{$MOD.LBL_ACTIVE_CAMPAIGN_LISTS}</span>
                                </td>
                                <td class="tcenter">
                                    <span class="tHeaderEle">{$MOD.LBL_SYNC_EXISTING_RECORDS}</span>
                                </td>
                                <td class="tcenter">
                                    <span class="tHeaderEle">{$MOD.LBL_AUTOMATICALLY_SYNC_NEW_RECORDS}</span>
                                </td>
                            </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td class="tright">
                                <span class="tbold">{$MOD.LBL_ACCOUNTS}</span>
                                <span class="required" id="lbl_api_url">*</span>
                            </td>
                            <td class="tcenter">
                                <select class="plugin_input" id="account_sync" name="account_sync" style="width: 100%;">
                                    <option value="">select</option>
                                    {foreach from=$list_arr key=myId  item=mapValue}
                                        <option {if $account_sync == $mapValue.id} selected {/if}value="{$mapValue.id}">
                                            {$mapValue.name}
                                        </option>
                                    {/foreach}
                                </select>
                            </td>
                            <td class="tcenter">
                                <input type="checkbox" id="account_existing_sync" name="account_existing_sync" {if $account_existing_sync == 'on'} checked {/if}>
                            </td>
                            <td class="tcenter">
                                <input type="checkbox" id="account_new_sync" name="account_new_sync" {if $account_new_sync == 'on'} checked {/if}>
                            </td>
                        </tr>

                        <tr>
                            <td class="tright">
                                <span class="tbold">{$MOD.LBL_CONTACTS}</span>
                                <span class="required" id="lbl_api_url">*</span>
                            </td>
                            <td class="tcenter">
                                <select class="plugin_input" id="contacts_sync" name="contacts_sync" style="width: 100%;">
                                    <option value="">select</option>
                                    {foreach from=$list_arr key=myId  item=mapValue}
                                        <option {if $contacts_sync == $mapValue.id} selected {/if}value="{$mapValue.id}">
                                            {$mapValue.name}
                                        </option>
                                    {/foreach}
                                </select>
                            </td>
                            <td class="tcenter">
                                <input type="checkbox" id="contacts_existing_sync" name="contacts_existing_sync" {if $contacts_existing_sync == 'on'} checked {/if}>
                            </td>
                            <td class="tcenter">
                                <input type="checkbox" id="contacts_new_sync" name="contacts_new_sync" {if $contacts_new_sync == 'on'} checked {/if}>
                            </td>
                        </tr>

                        <tr>
                            <td class="tright">
                                <span class="tbold">{$MOD.LBL_LEADS}</span>
                                <span class="required" id="lbl_api_url">*</span>
                            </td>
                            <td class="tcenter">
                                <select class="plugin_input" id="leads_sync" name="leads_sync" style="width: 100%;">
                                    <option value="">select</option>
                                    {foreach from=$list_arr key=myId  item=mapValue}
                                        <option {if $leads_sync eq $mapValue.id} selected {/if} value="{$mapValue.id}">
                                            {$mapValue.name}
                                        </option>
                                    {/foreach}
                                </select>
                            </td>
                            <td class="tcenter">
                                <input type="checkbox" id="leads_existing_sync" name="leads_existing_sync" {if $leads_existing_sync == 'on'} checked {/if}>
                            </td>
                            <td class="tcenter">
                                <input type="checkbox" id="leads_new_sync" name="leads_new_sync" {if $leads_new_sync == 'on'} checked {/if}>
                            </td>
                        </tr>
                        <tr>
                            <td class="tright">
                                <span class="tbold">{$MOD.LBL_CONTRACTS}</span>
                                <span class="required" id="lbl_api_url">*</span>
                            </td>
                            <td class="tcenter">
                                <select class="plugin_input" id="contracts_sync" name="contracts_sync" style="width: 100%;">
                                    <option value="">select</option>
                                    {foreach from=$list_arr key=myId  item=mapValue}
                                        <option {if $contracts_sync eq $mapValue.id} selected {/if} value="{$mapValue.id}">
                                            {$mapValue.name}
                                        </option>
                                    {/foreach}
                                </select>
                            </td>
                            <td class="tcenter">
                                <input type="checkbox" id="contracts_existing_sync" name="contracts_existing_sync" {if $contracts_existing_sync == 'on'} checked {/if}>
                            </td>
                            <td class="tcenter">
                                <input type="checkbox" id="contracts_new_sync" name="contracts_new_sync" {if $contracts_new_sync == 'on'} checked {/if}>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <div style="text-align: left; padding-top: 20px; border-bottom: 1px solid;">
                        <h3 style="color: #000; padding: 8px"><strong>{$MOD.LBL_CRM_TARGET_LISTS_MAPPING}</strong></h3>
                    </div>

                    <table width="100%" cellpadding="0" cellspacing="0" border="0" id="add_more_mappings" class="edit view" style="margin: 20px 0;">
                        <tbody>
                        <tr>
                            <td width="22%" class="tcenter">
                                <span class="tbold">{$MOD.LBL_CRM_TARGET_LISTS}</span>
                            </td>
                            <td width="22%" class="tcenter">
                                <span class="tbold">{$MOD.LBL_ACTIVE_CAMPAIGN_TARGET_LISTS}</span>
                            </td>
                            <td width="22%" class="tcenter">
                                <span class="tbold">{$MOD.LBL_SYNC_EXISTING_RECORDS}</span>
                            </td>
                            <td width="22%" class="tcenter">
                                <span class="tbold">{$MOD.LBL_AUTOMATICALLY_SYNC_NEW_RECORDS}</span>
                            </td>
                            <td width="10%" class="tcenter">
                                <span class="tbold">{$MOD.LBL_ADD_REMOVE_MAPPING}</span>
                            </td>
                        </tr>
                        {$mapping_htmls}
                        </tbody>
                    </table>
                </td>
            </tr>

            <tr {if $authenticated===true} {else} style="display: none;" {/if}>
                <td>
                    <div style="text-align: left; padding-top: 20px; border-bottom: 1px solid;">
                        <h3 style="color: #000; padding: 8px"><strong>{$MOD.LBL_CONFIGURE_LEAD_SCORE}</strong></h3>
                    </div>
                    <table width="100%" cellspacing="1" cellpadding="0" border="0" class="edit view" style="margin: 20px 0;">
                        <tbody>
                        <tr>
                            <td width="24%" class="tcenter" id="lead_score_label">
                                {$MOD.LBL_LEAD_SCORE_VALUES}
                                <span class="required" id="lbl_lead_score">*</span>
                                <input type="checkbox" id="lead_score_sync" name="lead_score_sync" {if $lead_score_sync == 'on'} checked {/if} style="margin-left: 10px">
                            </td>
                            <td width="76%" class="tcenter">
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>
        <div {if $authenticated===true} style="padding-top:2px;"  {else} style="display: none;" {/if}>
            <input title="{$APP.LBL_SAVE_BUTTON_TITLE}" class="button primary" onclick="return store_setting();" type="button" name="button" value="{$MOD.LBL_SAVE_SETTINGS}">
            <input title="{$APP.LBL_SAVE_BUTTON_TITLE}" class="button primary" onclick="mapping_view();" type="button" name="button" value="{$MOD.LBL_FIELD_MAPPINGS}">
            <input title="View Logs" class="button secondary" onclick="view_logs();" type="button" name="button" value="View Logs">
            <b style="color:#F00;" id="message2"> </b>
            <b style="color:#3bb300;" id="success2"> </b>
        </div>
    </form>
    <br/>
    <div>
        <p>
            {$MOD.LBL_WARNING}
            <a class="tlink" href="https://www.activecampaign.com/signup/" target="_blank">{$MOD.LBL_ACTIVE_CAMPAIGN_REGISTRATION}</a>
        </p>
    </div>
    {$JAVASCRIPT}
    {$JAVASCRIPT2}
    {literal}
    {/literal}
</div>