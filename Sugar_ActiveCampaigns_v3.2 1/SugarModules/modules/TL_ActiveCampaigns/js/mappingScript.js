// validate signer emails
$(document).on('focusout','.signer_email',function(e){
    var email = $(this).val();
    if(email != ""){
        if(!validate_email(email)){
            alert("Please provide valid email");
            $(this).val('');
            $(this).prev('input').val('');
            $(this).prev('input').focus();
        }
    }
});

// stop adding duplicate signers
$(document).on('keyup','.signer_email',function(e){
    var signers_inputs = $(".signer_email");
    results_array = get_duplicates(signers_inputs);
    if(results_array.length > 0){
        alert("Duplicate singers not allowed");
        $(this).val('');
        $(this).prev('input').val('');
        $(this).prev('input').focus();
    }
});

// stop adding duplicate cc recepients
$(document).on('keyup','.cc_emails',function(e){
    var cc_emails = $(".cc_emails");
    results_array = get_duplicates(cc_emails);
    if(results_array.length > 0){
        alert("Duplicate Recipient not allowed");
        $(this).val('');
        $(this).prev('input').val('');
        $(this).prev('input').focus();
    }
});

// validate cc emails
$(document).on('focusout','.cc_emails',function(e){
    var email = $(this).val();
    if(email != ""){
        if(!validate_email(email)){
            alert("Please provide valid email");
            $(this).val('');
            $(this).prev('input').val('');
            $(this).prev('input').focus();
        }
    }
});



$(document).on('click','#send_now',function(e){
    e.preventDefault();
/*    var file = $("#document").val();
    if(file == ""){
        alert("Please select pdf document to send.");
        return false;
    }
    // get duplicate signers
    var signers_inputs = $(".signer_email");
    var input_array = [];
    for(var i = 0; i < signers_inputs.length; i++){
        input_array.push($(signers_inputs).eq(i).val());
    }
    var newArray = input_array.filter(function(v){return v!==''});
    if(newArray.length == 0){
        alert("Please add at least one signer");
        return false;
    }

    results_array = get_duplicates(signers_inputs);
    if(results_array.length > 0){
        alert("Duplicate singers not allowed");
        return false;
    }
    // get duplicate cc emails
    var cc_inputs = $(".cc_emails");
    results_array = get_duplicates(cc_inputs);
    if(results_array.length > 0){
        alert("Duplicate Recipient not allowed");
        return false;
    }*/
    $("form[name='ConfigureSettings']").submit();
});

//valid email check
function validate_email(email){
    var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
    return pattern.test(email);

}
// function to check duplicates values
function get_duplicates(inputs){
    var input_array = [];
    for(var i = 0; i < inputs.length; i++){
        input_array.push($(inputs).eq(i).val());
    }
    var newArray = input_array.filter(function(v){return v!==''});
    var sorted_arr = newArray.slice().sort();
    var results = [];

    for (var i = 0; i < newArray.length - 1; i++) {
        if (sorted_arr[i + 1] == sorted_arr[i]) {
            results.push(sorted_arr[i]);
        }
    }
    return results;
}




function javascript_function(list){
    alert(list);
}

function redirect_to_setting(){
    var app = window.parent.SUGAR.App;
    app.router.navigate("#bwc/index.php?module=TL_RightSignature&action=setting", {trigger:true, replace:true});
}

$(document).ready(function(e) {


    var optiopnString;
    var myStringArray = document.getElementById('selectBox').options;
    var arrayLength = myStringArray.length;
    for (var i = 0; i < arrayLength; i++) {
        var fieldName=myStringArray[i].value;
        var str="<option value='"+fieldName+"'> "+fieldName+"</option>"
        optiopnString=optiopnString+str;
    }

    var optiopnStringSugar;
    var myStringArraySugar = document.getElementById('selectBoxSugar').options;
    var arrayLength = myStringArraySugar.length;
    for (var i = 0; i < arrayLength; i++) {
        var fieldName=myStringArraySugar[i].value;
        var str="<option value='"+fieldName+"'> "+fieldName+"</option>"
        optiopnStringSugar=optiopnStringSugar+str;
    }





    var cc_email_html = '<tr><td width="12.5%" valign="baseline" scope="row" id="password_label"></td><td width="37.5%" valign="baseline"> <select class="plugin_input" style="width: 30%;" name="hubFields[]">'+optiopnString+'</select>  <select class="plugin_input" style="width: 30%;" name="sugarFields[]">'+optiopnStringSugar+'</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" class="add_more_cc" style="text-decoration:none;"><i class="plugin-icon plugin-plus"></i>&nbsp;&nbsp;<a href="#" class="remove" style="text-decoration:none; color:#F00;"> <i class="plugin-icon plugin-times"></i> </a></td></tr>';

    var signer_email_html = '<tr><td width="12.5%" valign="baseline" scope="row" id="password_label"></td><td width="37.5%" valign="baseline"> <select class="plugin_input" style="width: 30%;" name="hubFields[]">'+optiopnString+'</select> <select class="plugin_input" style="width: 30%;" name="sugarFields[]">'+optiopnStringSugar+'</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" class="add_more_cc" style="text-decoration:none;"><i class="plugin-icon plugin-plus"></i></a>&nbsp;<a href="#" class="remove" style="text-decoration:none; color:#F00;"> <i class="plugin-icon plugin-times"></i> </a></td></tr>';

    // add cc row on the click of add fields button
    $(document).on('click','.add_more_cc',function(e){
        $('#send_doc_table > tbody > tr').eq($(this).closest("tr")[0].rowIndex).after(cc_email_html);
    });
    //remove cc row on click of remove button
    $(document).on('click','.remove',function(e){
        $(this).parent().parent().remove();
    });


    // add cc row on the click of add fields button
    $(document).on('click','.add_more_signer',function(e){
        //alert(cc);
        $('.lin_break').show();
        $('#send_doc_table > tbody > tr').eq($(this).closest("tr")[0].rowIndex).after(signer_email_html);
    });
    //remove cc row on click of remove button
    $(document).on('click','.remove_signer',function(e){
        $(this).parent().parent().remove();
    });

});





function ValidateSingleInput(oInput) {
    var _validFileExtensions = [".pdf"];
    if (oInput.type == "file") {
        var sFileName = oInput.value;
        if (sFileName.length > 0) {
            var blnValid = false;
            for (var j = 0; j < _validFileExtensions.length; j++) {
                var sCurExtension = _validFileExtensions[j];
                if (sFileName.substr(sFileName.length - sCurExtension.length, sCurExtension.length).toLowerCase() == sCurExtension.toLowerCase()) {
                    blnValid = true;
                    break;
                }
            }

            if (!blnValid) {
                alert("Sorry, " + sFileName + " is invalid, only pdf document is allowed");
                oInput.value = "";
                return false;
            }
        }
    }
    return true;
}



function start_syncing_process() {
       showLoading();
    return false;
    /*    var syncwayid = $('#syncwayid:checked').val();
      // console.log(syncwayid);
        if (syncwayid.length > 0) {
            $.ajax({
                type: 'POST',
                url: 'index.php?module=cloud_hubspot&action=startsyncing',
                data: {"sync_way_type": syncwayid},
                datatype: 'json',
                success: function (response, status) {
                   // location.reload();
                    return false;

                },
                error: function (response, status) {
                    console.log("in error");
                    console.log(status);
                }
            });
        } else {
            alert('Please Enter Dropbox Access Token Key');
            hideLoading();
        }*/

   
}
function hideLoading() {
    SUGAR.ajaxUI.hideLoadingPanel();
}
function showLoading() {

    SUGAR.ajaxUI.showLoadingPanel();
}