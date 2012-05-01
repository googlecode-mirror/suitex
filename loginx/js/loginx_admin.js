jQuery(function(){
        
    
    
    
    
    
});

function loginx_admin_ajax(act, act_nonce, obj_id){
    
    data = {
        action: 'loginx_admin',
        sub: act,
        nonce: act_nonce,
        id: obj_id
    };
    jQuery.post(ajaxurl, data, function(response){
        alert('Got this: ' + response); 
    });
    
    
}

function loginx_confirm_delete(url){
    var mess = confirm("Confirm Delete");
    if (mess == true){
        window.location.href = url;        
    }
}