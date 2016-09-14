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
                dataType: 'json',
                async:false,
                data: {
                    username: function() {
                      return $("#username").val();
                    },
                    id: function() {
                    return $("#id").val();
                  }
                }
        	}
           },
           profile:{
           	required: true
           },
           status:{
           	required: true
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
		window.location = '/core/user_user';
	});
	
	//hide elements
	$('#password_container').hide();
	
	//listeners
	$('#password_checkbox').bind('click',function(){
		if($(this).is(':checked')){
			$('#password_container').removeClass('hide');
			//add rules
			$('#password').rules('add',{
				required: true,
				minlength: 6
			});
			$('#confirm_password').rules('add',{
				required: true,
				minlength: 6,
				equalTo: "#password",
                messages: {
                    equalTo: confirm_password_message
                }
			});
		}
		else{
			$('#password_container').hide();
			//remove rules
			$('#password').rules('remove');
			$('#confirm_password').rules('remove');	
		}	
	});
});