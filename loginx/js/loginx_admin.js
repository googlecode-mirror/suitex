jQuery(document).ready(function(){
    loginx_refreshFieldList();     
    
    jQuery('#loginxFieldForm_end').click(function(){
        loginx_submitAdminForm();    
    });
    
    
    
});

function loginx_admin_ajax(act, act_nonce, obj_id){
    
    data = {
        action: 'loginx_admin',
        sub: act,
        nonce: act_nonce,
        id: obj_id
    };
    jQuery.post(ajaxurl, data, function(response){
        loginx_refreshFieldList();
    });
}

function loginx_refreshFieldList(){
    jQuery.get(ajaxurl, {action:'loginx_fields'}, function(response){
        jQuery('#customFieldsList').html(response);
    });     
}

function loginx_submitAdminForm(){

    data = {
        action: 'loginx_admin',
        nonce: jQuery("#loginxFieldForm input[name=nonce]").val(),
        loginx_field_name: jQuery("#loginxFieldForm input[name=loginx_field_name]").val(),
        loginx_field_id: jQuery("#loginxFieldForm input[name=loginx_field_id]").val(),
        loginx_field_label: jQuery("#loginxFieldForm input[name=loginx_field_label]").val(),
        loginx_field_options: jQuery("#loginxFieldForm textarea[name=loginx_field_options]").val(),
        loginx_field_type: jQuery("#loginxFieldForm select[name=loginx_field_type]").val()    
    };
    
    jQuery.post(ajaxurl, data, function(){
        loginx_refreshFieldList();  
         
        jQuery("#loginxFieldForm input[name=loginx_field_name]").val('');
        jQuery("#loginxFieldForm input[name=loginx_field_id]").val('0');
        jQuery("#loginxFieldForm input[name=loginx_field_label]").val('');
        jQuery("#loginxFieldForm textarea[name=loginx_field_options]").val('');
        jQuery("#loginxFieldForm select[name=loginx_field_type]").val(''); 
        
        jQuery.get(ajaxurl, {action:'loginx_admin'}, function(response){
            jQuery("#loginxFieldForm input[name=nonce]").val(response.replace(/^\s+|\s+$/g,""));     
        });
        
                 
    });        

    
}

function loginx_populateAdminForm(field_id){
    jQuery.get(ajaxurl, {action:'loginx_admin', id: field_id}, function(response){
        jQuery("#loginxFieldForm input[name=loginx_field_name]").val(response.loginx_field_name);
        jQuery("#loginxFieldForm input[name=loginx_field_id]").val(field_id);
        jQuery("#loginxFieldForm input[name=loginx_field_label]").val(response.loginx_field_label);
        jQuery("#loginxFieldForm textarea[name=loginx_field_options]").val(response.loginx_field_options);
        jQuery("#loginxFieldForm select[name=loginx_field_type]").val(response.loginx_field_type);         
        jQuery.get(ajaxurl, {action:'loginx_admin'}, function(response){
            jQuery("#loginxFieldForm input[name=nonce]").val(response.replace(/^\s+|\s+$/g,""));     
        });
        jQuery("#loginxFieldForm input[name=loginx_field_name]").focus();
        
    });
    
}

function loginx_confirm_delete(act_nonce, obj_id){
    var mess = confirm("Confirm Delete");
    if (mess == true){
        loginx_admin_ajax('delete', act_nonce, obj_id);    
    }
}