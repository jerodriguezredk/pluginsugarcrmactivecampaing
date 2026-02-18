$(document).ready(function () {
    /**
     * CRM Target Lists Lists
     */
    var tgString = "";
    var tg_lists = "";
    if ($("#target_list_mapped").length) {
        tg_lists = document.getElementById('target_list_mapped').options;
        if (tg_lists && tg_lists != "undefined") {
            var arrayLength = tg_lists.length;
            for (var i = 0; i < arrayLength; i++) {
                var label = tg_lists[i].label;
                var value = tg_lists[i].value;
                var str = "<option value='" + value + "'> " + label + "</option>"
                tgString = tgString + str;
            }
        }
    }
    /**
     * Active Campaign Lists
     */
    var acString = "";
    if ($("#ac_lists_mapped").length) {
        var ac_lists = document.getElementById('ac_lists_mapped').options;
        if (ac_lists && ac_lists != "undefined") {
            var arrayLength = ac_lists.length;
            for (var i = 0; i < arrayLength; i++) {
                var label = ac_lists[i].label;
                var value = ac_lists[i].value;
                var str = "<option value='" + value + "'> " + label + "</option>"
                acString = acString + str;
            }
        }
    }

    $(document).on('click', '.add_more_mapping', function (e) {
        var count = get_row_count();
        var more_html = "<tr><td width='22%' valign='baseline'><select class='plugin_input' id='target_list_mapped' name='target_list_mapped[" + count + "]' style='width: 100%;'>" + tgString + "</select></td><td width='22%' valign='baseline'><select class='plugin_input' id='ac_lists_mapped' name='ac_lists_mapped[" + count + "][]' style='width: 100%; multiple=''>" + acString + "</select></td><td scope='row' id='password_label' width='22%' valign='baseline'><input type='checkbox' id='existing_sync' name='existing_sync[" + count + "]'></td><td scope='row' id='password_label' width='22%' valign='baseline'><input type='checkbox' id='new_sync' name='new_sync[" + count + "]'></td><td scope='row' id='password_label' width='10%' valign='baseline'><a href='#' class='add_more_mapping' row_count='" + count + "' style='margin-left: -0.5%;'><i class='plugin-icon plugin-plus'></i></a>&nbsp&nbsp&nbsp<a href='#' class='remove_mapping' style='text-decoration:none; color:#F00;'><i class='plugin-icon plugin-times'></i> </a></td></tr>";
        $('#add_more_mappings > tbody > tr').eq($(this).closest("tr")[0].rowIndex).after(more_html);
    });

    $(document).on('click', '.remove_mapping', function (e) {
        showLoading();
        $(this).parent().parent().remove();
        var new_count = 0;
        $('#add_more_mappings > tbody > tr').each(function () {
            if (new_count != 0) {
                temp = new_count - 1;
                $(this).find("#target_list_mapped").attr("name", "target_list_mapped[" + temp + "]");
                $(this).find("#ac_lists_mapped").attr("name", "ac_lists_mapped[" + temp + "][]");
                $(this).find("#existing_sync").attr("name", "existing_sync[" + temp + "]");
                $(this).find("#new_sync").attr("name", "new_sync[" + temp + "]");
            }
            new_count++
        });
        hideLoading();
    });
});

function mapping_view() {
    var app = window.parent.SUGAR.App;
    app.router.navigate("#bwc/index.php?module=TL_ActiveCampaigns&action=mapping", {trigger: true, replace: true});
}

function view_logs() {
    var app = window.parent.SUGAR.App;
    app.router.navigate("#bwc/index.php?module=TL_ActiveCampaigns&action=logs", {trigger: true, replace: true});
}

function get_row_count() {
    return $('#add_more_mappings tr').length - 1;
}

function showLoading() {
    $("#loading").show();
    SUGAR.ajaxUI.showLoadingPanel();
}

function hideLoading() {
    $("#loading").hide();
    SUGAR.ajaxUI.hideLoadingPanel();
}

function check_campaign_api() {
    showLoading();
    var messageDiv = $("#message");
    var successDiv = $("#success");
    var authenticated = $("#authenticated");
    messageDiv.html("");
    if (authenticated) {
        successDiv.html(app.lang.get('LBL_AUTHENTICATED', 'TL_ActiveCampaigns'));
    } else {
        successDiv.html("");
    }
    var api_url = $("#api_url").val();
    var api_key = $("#api_key").val();
    var check = true;
    if (api_url === "") {
        $("#api_url").attr('placeholder', app.lang.get('LBL_API_URL_REQUIRED', 'TL_ActiveCampaigns'));
        check = false;
    }
    if (api_key === "") {
        $("#api_key").attr('placeholder', app.lang.get('LBL_API_KEY_REQUIRED', 'TL_ActiveCampaigns'));
        check = false;
    }
    if (check === true) {
        $.ajax({
            url: "index.php?module=TL_ActiveCampaigns&action=check_api",
            data: {api_url: api_url, api_key: api_key},
            type: 'POST',
            success: function (response) {
                var result = JSON.parse(response);
                if (result.status && result.status === true) {
                    hideLoading();
                    successDiv.html(app.lang.get('LBL_AUTHENTICATION_COMPLETED', 'TL_ActiveCampaigns'));
                    setTimeout(function () {
                        successDiv.html("");
                    }, 7000);
                    location.reload(true);
                } else {
                    hideLoading();
                    if (result.message) {
                        messageDiv.html(result.message);
                    } else {
                        messageDiv.html(app.lang.get('LBL_AUTHENTICATION_ERROR', 'TL_ActiveCampaigns'));
                    }
                    setTimeout(function () {
                        messageDiv.html("");
                    }, 7000);
                }
            },
            error: function () {
                hideLoading();
                alert(app.lang.get('LBL_SOMETHING_WENT_WRONG', 'TL_ActiveCampaigns'));
            }
        });
    } else {
        hideLoading();
        messageDiv.html(app.lang.get('LBL_REQUIRED_FIELD_IS_EMPTY', 'TL_ActiveCampaigns'));
        setTimeout(function () {
            $("#message").html("");
        }, 7000);
    }
}

function store_setting() {
    showLoading();
    var api_url = $("#api_url").val();
    var api_key = $("#api_key").val();
    var messageDiv = $("#message2");
    var successDiv = $("#success2");
    messageDiv.html("");
    successDiv.html("");
    var check = true;
    if (api_url === "") {
        $("#api_url").attr('placeholder', app.lang.get('LBL_API_URL_REQUIRED', 'TL_ActiveCampaigns'));
        check = false;
    }
    if (api_key === "") {
        $("#api_key").attr('placeholder', app.lang.get('LBL_API_KEY_REQUIRED', 'TL_ActiveCampaigns'));
        check = false;
    }
    if (check === true) {
        var str = jQuery("#EditView").serializeArray();
        $.ajax({
            url: "index.php?module=TL_ActiveCampaigns&action=store_setting",
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
                    location.reload(true);
                } else {
                    hideLoading();
                    if (result.message) {
                        messageDiv.html(result.message);
                    } else {
                        messageDiv.html(app.lang.get('LBL_SOMETHING_WENT_WRONG', 'TL_ActiveCampaigns'));
                    }
                    setTimeout(function () {
                        messageDiv.html("");
                    }, 7000);
                }
                return true;
            }
        });
    } else {
        hideLoading();
        messageDiv.html(app.lang.get('LBL_REQUIRED_FIELD_IS_EMPTY', 'TL_ActiveCampaigns'));
        setTimeout(function () {
            $("#message").html("");
        }, 7000);
    }

}