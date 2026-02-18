// JavaScript Document
$(document).ready(function (e) {

    var fieldName = '';
    var fieldLabel = '';
    var str = '';
    var arrayLength = 0;
    var i = 0;
    /////lead field mapping//////////
    var optiopnStringLead = "";
    var myStringArrayLead = document.getElementById('selectBoxLead').options;
    arrayLength = myStringArrayLead.length;
    for (i = 0; i < arrayLength; i++) {
        fieldName = myStringArrayLead[i].value;
        fieldLabel = myStringArrayLead[i].text;
        str = "<option value='" + fieldName + "'> " + fieldLabel + "</option>"
        optiopnStringLead = optiopnStringLead + str;
    }

    var optiopnStringSugarLead = "";
    var myStringArraySugarLead = document.getElementById('selectBoxSugarLead').options;
    arrayLength = myStringArraySugarLead.length;
    for (i = 0; i < arrayLength; i++) {
        fieldName = myStringArraySugarLead[i].value;
        fieldLabel = myStringArraySugarLead[i].text;
        str = "<option value='" + fieldName + "'> " + fieldLabel + "</option>"
        optiopnStringSugarLead = optiopnStringSugarLead + str;
    }

    var add_more_lead_fields = '<tr><td width="12.5%" valign="baseline" scope="row" id="password_label"></td><td width="37.5%" valign="baseline"> <select class="plugin_input" style="width: 30%;" name="campaignFieldsLead[]">' + optiopnStringLead + '</select>  <select class="plugin_input" style="width: 30%;" name="sugarLeadFields[]">' + optiopnStringSugarLead + '</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" class="add_more_lead" style="text-decoration:none;"><i class="plugin-icon plugin-plus"></i>&nbsp;&nbsp;<a href="#" class="remove_lead" style="text-decoration:none; color:#F00;"> <i class="plugin-icon plugin-times"></i> </a></td></tr>';
    ///////////contact field mapping//////////////////
    var optiopnStringContact = "";
    var myStringArrayContact = document.getElementById('selectBoxContact').options;

    arrayLength = myStringArrayContact.length;
    for (i = 0; i < arrayLength; i++) {
        fieldName = myStringArrayContact[i].value;
        fieldLabel = myStringArrayContact[i].text;
        str = "<option value='" + fieldName + "'> " + fieldLabel + "</option>"
        optiopnStringContact = optiopnStringContact + str;
    }

    var optiopnStringSugarContact = "";
    var myStringArraySugarContact = document.getElementById('selectBoxSugarContact').options;

    arrayLength = myStringArraySugarContact.length;
    for (i = 0; i < arrayLength; i++) {
        fieldName = myStringArraySugarContact[i].value;
        fieldLabel = myStringArraySugarContact[i].text;
        str = "<option value='" + fieldName + "'> " + fieldLabel + "</option>"
        optiopnStringSugarContact = optiopnStringSugarContact + str;
    }


    ///////////contract field mapping////////////////
    var optiopnStringContract = "";
    var myStringArrayContract = document.getElementById('selectBoxContract').options;
    arrayLength = myStringArrayContract.length;
    for (i = 0; i < arrayLength; i++) {
        fieldName = myStringArrayContract[i].value;
        fieldLabel = myStringArrayContract[i].text;
        str = "<option value='" + fieldName + "'> " + fieldLabel + "</option>"
        optiopnStringContract = optiopnStringContract + str;
    }

    var optiopnStringSugarContract = "";
    var myStringArraySugarContract = document.getElementById('selectBoxSugarContract').options;
    arrayLength = myStringArraySugarContract.length;
    for (i = 0; i < arrayLength; i++) {
        fieldName = myStringArraySugarContract[i].value;
        fieldLabel = myStringArraySugarContract[i].text;
        str = "<option value='" + fieldName + "'> " + fieldLabel + "</option>"
        optiopnStringSugarContract = optiopnStringSugarContract + str;
    }
    var add_more_contract_fields = '<tr><td width="12.5%" valign="baseline" scope="row" id="password_label"></td><td width="37.5%" valign="baseline"> <select class="plugin_input" style="width: 30%;" name="campaignFieldsContract[]">' + optiopnStringContract + '</select>  <select class="plugin_input" style="width: 30%;" name="sugarContractFields[]">' + optiopnStringSugarContract + '</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" class="add_more_contract" style="text-decoration:none;"><i class="plugin-icon plugin-plus"></i>&nbsp;&nbsp;<a href="#" class="remove_contract" style="text-decoration:none; color:#F00;"> <i class="plugin-icon plugin-times"></i> </a></td></tr>';


    var add_more_contact_fields = '<tr><td width="12.5%" valign="baseline" scope="row" id="password_label"></td><td width="37.5%" valign="baseline"> <select class="plugin_input" style="width: 30%;" name="campaignFieldsContact[]">' + optiopnStringContact + '</select>  <select class="plugin_input" style="width: 30%;" name="sugarContactFields[]">' + optiopnStringSugarContact + '</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" class="add_more_contact" style="text-decoration:none;"><i class="plugin-icon plugin-plus"></i>&nbsp;&nbsp;<a href="#" class="remove_contact" style="text-decoration:none; color:#F00;"> <i class="plugin-icon plugin-times"></i> </a></td></tr>';
    ///////////account field mapping////////////////
    var optiopnStringAccount = "";
    var myStringArrayAccount = document.getElementById('selectBoxAccount').options;
    arrayLength = myStringArrayAccount.length;
    for (i = 0; i < arrayLength; i++) {
        fieldName = myStringArrayAccount[i].value;
        fieldLabel = myStringArrayAccount[i].text;
        str = "<option value='" + fieldName + "'> " + fieldLabel + "</option>"
        optiopnStringAccount = optiopnStringAccount + str;
    }


    var optiopnStringSugarAccount = "";
    var myStringArraySugarAccount = document.getElementById('selectBoxSugarAccount').options;
    arrayLength = myStringArraySugarAccount.length;
    for (i = 0; i < arrayLength; i++) {
        fieldName = myStringArraySugarAccount[i].value;
        fieldLabel = myStringArraySugarAccount[i].text;
        str = "<option value='" + fieldName + "'> " + fieldLabel + "</option>"
        optiopnStringSugarAccount = optiopnStringSugarAccount + str;
    }


    var add_more_account_fields = '<tr><td width="12.5%" valign="baseline" scope="row" id="password_label"></td><td width="37.5%" valign="baseline"> <select class="plugin_input" style="width: 30%;" name="campaignFieldsAccount[]">' + optiopnStringAccount + '</select>  <select class="plugin_input" style="width: 30%;" name="sugarAccountFields[]">' + optiopnStringSugarAccount + '</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" class="add_more_account" style="text-decoration:none;"><i class="plugin-icon plugin-plus"></i>&nbsp;&nbsp;<a href="#" class="remove_account" style="text-decoration:none; color:#F00;"> <i class="plugin-icon plugin-times"></i> </a></td></tr>';

    $(document).on('click', '.add_more_lead', function (e) {
        $('#lead_field_table > tbody > tr:last').after(add_more_lead_fields);
    });

    $(document).on('click', '.add_more_contact', function (e) {
        $('#contact_field_table > tbody > tr:last').after(add_more_contact_fields);
    });

    $(document).on('click', '.add_more_account', function (e) {
        $('#account_field_table > tbody > tr:last').after(add_more_account_fields);
    });

    $(document).on('click', '.add_more_contract', function (e) {
        $('#contract_field_table > tbody > tr:last').after(add_more_contract_fields);
    });

    $(document).on('click', '.remove_lead', function (e) {
        $(this).parent().parent().remove();
    });

    $(document).on('click', '.remove_account', function (e) {
        $(this).parent().parent().remove();
    });

    $(document).on('click', '.remove_contact', function (e) {
        $(this).parent().parent().remove();
    });

    $(document).on('click', '.remove_contract', function (e) {
        $(this).parent().parent().remove();
    });

    $("#sugar_module").change(function () {
        var opt = this.value;
        if (opt === "contacts") {
            $("#no_list").hide();
            $("#leads_list").hide();
            $("#account_list").hide();
            $("#contracts_list").hide();
            $("#contacts_list").show();

        }

        if (opt === "leads") {
            $("#no_list").hide();
            $("#contacts_list").hide();
            $("#account_list").hide();
            $("#contracts_list").hide();
            $("#leads_list").show();
        }

        if (opt === "accounts") {
            $("#no_list").hide();
            $("#contacts_list").hide();
            $("#leads_list").hide();
            $("#contracts_list").hide();
            $("#account_list").show();
        }

        if (opt === "contracts") {
            $("#no_list").hide();
            $("#contacts_list").hide();
            $("#leads_list").hide();
            $("#account_list").hide();
            $("#contracts_list").show();
        }

        if (opt === "") {
            $("#contacts_list").hide();
            $("#leads_list").hide();
            $("#account_list").hide();
            $("#contracts_list").hide();
            $("#no_list").show();
        }
    });

});

function showLoading() {
    SUGAR.ajaxUI.showLoadingPanel();
}

function hideLoading() {
    SUGAR.ajaxUI.hideLoadingPanel();
}

function store_lead_field_setting() {
    showLoading();
    var messageDiv = $(".message");
    var successDiv = $(".success");
    var str = jQuery("#SaveLeadMapping").serializeArray();
    $.ajax({
        url: "index.php?module=TL_ActiveCampaigns&action=store_mapping",
        data: str,
        type: 'POST',
        async: true,
        success: function (response) {
            var result = JSON.parse(response);
            if (result.status && result.status === true) {
                hideLoading();
                successDiv.html(app.lang.get('LBL_CONFIGURATION_STORED', 'TL_ActiveCampaigns'));
                setTimeout(function () {
                    successDiv.html("");
                }, 7000);
            } else {
                hideLoading();
                messageDiv.html(app.lang.get('LBL_SOMETHING_WENT_WRONG', 'TL_ActiveCampaigns'));
                setTimeout(function () {
                    messageDiv.html("");
                }, 7000);
            }
        }
    });

}

function store_contact_field_setting() {
    showLoading();
    var messageDiv = $(".message");
    var successDiv = $(".success");
    var str = jQuery("#SaveContactMapping").serializeArray();
    $.ajax({
        url: "index.php?module=TL_ActiveCampaigns&action=store_mapping",
        data: str,
        type: 'POST',
        async: true,
        success: function (response) {
            var result = JSON.parse(response);
            if (result.status && result.status === true) {
                hideLoading();
                successDiv.html(app.lang.get('LBL_CONFIGURATION_STORED', 'TL_ActiveCampaigns'));
                setTimeout(function () {
                    successDiv.html("");
                }, 7000);
            } else {
                hideLoading();
                messageDiv.html(app.lang.get('LBL_SOMETHING_WENT_WRONG', 'TL_ActiveCampaigns'));
                setTimeout(function () {
                    messageDiv.html("");
                }, 7000);
            }
        }
    });

}

function store_account_field_setting() {
    showLoading();
    var messageDiv = $(".message");
    var successDiv = $(".success");
    var str = jQuery("#SaveAccountMapping").serializeArray();
    $.ajax({
        url: "index.php?module=TL_ActiveCampaigns&action=store_mapping",
        data: str,
        type: 'POST',
        async: true,
        success: function (response) {
            var result = JSON.parse(response);
            if (result.status && result.status === true) {
                hideLoading();
                successDiv.html(app.lang.get('LBL_CONFIGURATION_STORED', 'TL_ActiveCampaigns'));
                setTimeout(function () {
                    successDiv.html("");
                }, 7000);
            } else {
                hideLoading();
                messageDiv.html(app.lang.get('LBL_SOMETHING_WENT_WRONG', 'TL_ActiveCampaigns'));
                setTimeout(function () {
                    messageDiv.html("");
                }, 7000);
            }
        }
    });

}

function store_contract_field_setting() {
    showLoading();
    var messageDiv = $(".message");
    var successDiv = $(".success");
    var str = jQuery("#SaveContractMapping").serializeArray();
    $.ajax({
        url: "index.php?module=TL_ActiveCampaigns&action=store_mapping",
        data: str,
        type: 'POST',
        async: true,
        success: function (response) {
            var result = JSON.parse(response);
            if (result.status && result.status === true) {
                hideLoading();
                successDiv.html(app.lang.get('LBL_CONFIGURATION_STORED', 'TL_ActiveCampaigns'));
                setTimeout(function () {
                    successDiv.html("");
                }, 7000);
            } else {
                hideLoading();
                messageDiv.html(app.lang.get('LBL_SOMETHING_WENT_WRONG', 'TL_ActiveCampaigns'));
                setTimeout(function () {
                    messageDiv.html("");
                }, 7000);
            }
        }
    });

}
