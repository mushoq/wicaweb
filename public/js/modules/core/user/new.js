$(document).ready(function(){
	$("#frmUser").validate({
        wrapper: "span",
        onfocusout: false,
        onkeyup: false,
        rules: {
                name: {
                     required: true
                },
                lastname: {
                     required: true
                },
                email:{
                	email: true
                },
                phone:{
                	digits: true
                },
                username:{
                	required: true,
                	remote: {
                		url: "/core/user_user/validateusername",
                        type: "post",
                        data: {
	                        username: function() {
	                          return $("#username").val();
	                        }
                        }
                	}
                },
                password:{
                	required: true,
                	minlength: 6
                },
                confirm_password:{
                	required: true,
                	minlength: 6,
                	equalTo: "#password"
                },
                profile:{
                	required: true
                }
        },
        messages:{
        	username:{
        		remote: username_remote_message
        	},
        	confirm_password:{
        		equalTo: confirm_password_message
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
		window.location = '/core/user_user';
	});
});
        