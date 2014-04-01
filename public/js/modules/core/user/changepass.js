$(document).ready(function(){
	$("#frmUser").validate({
        wrapper: "span",
        onfocusout: false,
        onkeyup: false,
        rules: {
            old_password:{
                required: true
            },
            new_password:{
                required: true,
                minlength: 6
            },
            confirm_password:{
                required: true,
                equalTo: "#new_password"
            }
        },
        messages:{
        	username:{
        		remote: username_remote_message
        	}
        }
		
	});
	
	//button submit
	$('#submit').bind('click',function(){
		if($('#frmUser').valid()){
			$("#frmUser").submit();
		}
	});
	
	//button accion
	$('#cancel').bind('click',function(){
		window.location = '/core/index/controlpanel';
	});

	
});