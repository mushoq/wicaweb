$(document).ready(function() {

	//change password
	if($("#change_psw").val()==1){
		$("#anchor_change_psw").fancybox();
		$("#anchor_change_psw").click();
		
		//Validation
		$("#form_change_psw").validate({
			wrapper: "span",
			onfocusout: false,
			onkeyup: false,					
            rules: {
           	
            }
		});	
		
		$("#public_user_change_psw").rules('add',{
			required: true
		});
		$("#public_user_change_cpsw").rules('add',{
			required: true,
			equalTo: "#public_user_change_psw"
		});				
		
		$("#btn_register_change_psw").bind("click",function(){
			if($("#form_change_psw").valid()){
				$.ajax({
					type: 'POST',
					async: false,
					url: '/default/index/changepsw',
					data: {
						password: $("#public_user_change_psw").val()
					},
					dataType: 'json',
					
					success: function(data) {	
						window.location.reload(true);
					}
				});
			}
		});		
		
	}
	
	
	//Loading view
	$("#loader_spinner").bind("ajaxStart", function(){
	    $(this).show();
	}).bind("ajaxStop", function(){
		$(this).hide();
	});
	
	$("[id^='myCarousel_']").each(function(){
		$(this).carousel({
			interval : 8000
		});

		$(this).bind("mouseenter", function(){
			$("#carousel_left_"+this.id.replace('myCarousel_','')).show();
			$("#carousel_right_"+this.id.replace('myCarousel_','')).show();				
		});
		
		$(this).bind("mouseleave", function(){
			$("#carousel_left_"+this.id.replace('myCarousel_','')).hide();
			$("#carousel_right_"+this.id.replace('myCarousel_','')).hide();				
		});
		
		
		$("#carousel_left_"+this.id.replace('myCarousel_','')).bind("click", function() {
			$(this).carousel('prev');
		});

		$("#carousel_right_"+this.id.replace('myCarousel_','')).bind("click", function() {
			$(this).carousel('next');
		});				
	});
        if(document.getElementById("myCarousel_4") !== null){
            $("#myCarousel_4").carousel('next');
            }            
       
        $("#form_field_file_").bind('click', function() {
            alert('hola');
                    
                });
                
	//search form contents
	$("[id^='content_form_']").each(function(){
		var content_id = $(this).attr('content_id');

		//Validation
		$("#content_form_"+content_id).validate({
			wrapper: "span",
			onfocusout: false,
			onkeyup: false,					
            rules: {
            }
		});
        
		$('[id^="form_field_file_"]').each(function(){
			content_id = this.id.replace("form_field_file_","");
			load_file('form_field_file'+content_id, 'file'); 
		}) 
               
		
		$("#btn_sub_form_"+content_id).bind("click",function(){                    
			$("#captcha_error").hide();
			if($("#content_form_"+content_id).valid()){
				$.ajax({
					type: 'POST',
					async: false,
					url: '/default/index/sendformemail',
					dataType: 'json',
					data: $("#content_form_"+content_id).serialize(),
					success: function(data) {	
						if(data=='error_captcha'){
							$("#captcha_error_"+content_id).show();
						}else
							if(data=='success_captcha' || data=='error_dictionary' || data=='error_sending'){
								window.location.reload(true);
							}
					},
					error: function(){
						$.ajax({
							type: 'POST',
							async: false,
							url: '/default/index/seterrormessage',
							dataType: 'json',
							
							success: function(data) {	
								if(data){
									window.location.reload(true);
								}
							}
						});
						
					}
				});
			}
		});
		
	});
	
	//select only elements inside this form
	$("[id^='form_field']").each(function(){
		if($(this).attr('valid')=='yes'){
			//add required rules
			$(this).rules('add',{
				required:true
			});
		}
	});
		
	$("[id^='btnLogin_']").each(function(){
		var area = $(this).attr('area');
		
		//Validation
		$("#form_login_"+area).validate({
			wrapper: "span",
			onfocusout: false,
			onkeyup: false,					
            rules: {
           	
            }
		});	
		
		$("#public_user_"+area).rules('add',{
			required: true
		});
		$("#public_password_"+area).rules('add',{
			required: true
		});				
		
		$("#btnLogin_"+area).bind("click",function(){
			$("#error_login_"+area ).hide();
			if($("#form_login_"+area).valid()){
				$.ajax({
					type: 'POST',
					async: false,
					url: '/default/index/externallogin',
					data: {
						username: $("#public_user_"+area).val(),
						password: $("#public_password_"+area).val()
					},
					dataType: 'json',
					
					success: function(data) {	
						if(data && data!='error' && data!='inactive'){
							window.location.reload(true);
						}else{
							if(data == 'error'){
								$("#error_login_"+area).show();
							}else
								if(data == 'inactive'){
									$("#error_login_inactive_"+area).show();
							}
						}
					}
				});
			}
		});
	});
	
	
	$("#logout_public_user").bind("click",function(){
		$.ajax({
			type: 'POST',
			async: false,
			url: '/default/index/externallogout',
	
			dataType: 'json',
			
			success: function(data) {	
				if(data){
					window.location.reload(true);
				}
			}
		});
	});
        
         $("#profile").bind("click",function(){
		
                    $.ajax({
                            type: 'POST',
                            async: false,
                            url: '/default/index/profile',
                            //dataType: 'json',

                            success: function(data) {

//
                                $('#wica_main_area').load("/default/index/profile", {

                                },function(){						
                                        $.getScript('/js/modules/default/index/index.js');
                                });

                            }
                    });
			
		});   
	
	
	$("[id^='register_']").each(function(){
		var area_reg = $(this).attr('area');
		$("[id^='register_']").fancybox({
			fitToView	: false,
			width		: '70%',
			height		: '70%',
			maxWidth	: 600,
			maxHeight	: 400,
			autoSize	: false,
			closeClick	: false,
			openEffect	: 'none',
			closeEffect	: 'none',
			'onClosed':  function(){
				$("#form_register_"+area_reg+" input[id^='public_user_']").each(function(){
					$("input.error_validation").removeClass('error_validation');
					$("label.error_validation").hide();
					$(this).val('');
				});			
			}
		});
		
		//Validation
		$("#form_register_"+area_reg).validate({
			wrapper: "span",
			onfocusout: false,
			onkeyup: false,					
            rules: {
           	
            }
		});	

		$("#public_user_email_"+area_reg).rules('add',{
			remote:{//check if email already exist
				url: "/default/index/checkregistermail",
				type: "POST",
				async:false,
				data:{
					area: area_reg
				}
			},			
			required: true,
    		email:true,
			messages:{
				remote: $("#error_email_"+area_reg).val()
			}
		});	
		$("#public_user_username_"+area_reg).rules('add',{
			remote:{//check if username already exist
				url: "/default/index/checkusername",
				type: "POST",
				async: false,
				data:{
					area: area_reg
				}
			},			
			required: true,
			minlength: 6,
			alphaNumeric: true,
			messages:{
				remote: $("#error_username_"+area_reg).val()
			}
		});			
		
		$("#public_user_name_"+area_reg).rules('add',{
			required: true
		});
		$("#public_user_last_name_"+area_reg).rules('add',{
			required: true
		});		
		$("#public_user_identification_"+area_reg).rules('add',{
			required: true
		});	
	
		$("#public_user_password_"+area_reg).rules('add',{
			required: true,
			minlength: 6,
			alphaNumeric: true
		});	
		$("#public_user_cpassword_"+area_reg).rules('add',{
			equalTo: $("#public_user_password_"+area_reg),
			required: true,
			minlength: 6,
			alphaNumeric: true
		});			
		
		
		
		$("#btn_register_user_"+area_reg).bind("click",function(){
			$("#public_user_email_"+area_reg).valid();
			$("#public_user_username_"+area_reg).valid();
			if($("#form_register_"+area_reg).valid()){
				$.ajax({
					type: 'POST',
					async: false,
					url: '/default/index/register',
					data: $("#form_register_"+area_reg).serialize(),
					dataType: 'json',
					
					success: function(data) {	
						if(data){
							window.location.reload(true);
						}
						$.fancybox.close();
					}
				});		
			}
		});
	});
	

	$("[id^='forgot_']").each(function(){
		var area_for = $(this).attr('area');
		$(this).fancybox({
				fitToView	: false,
				width		: '70%',
				height		: '70%',
				maxWidth	: 400,
				maxHeight	: 200,
				autoSize	: false,
				closeClick	: false,
				openEffect	: 'none',
				closeEffect	: 'none',
			});
		
		//Validation
		$("#form_forgot_"+area_for).validate({
			wrapper: "span",
			onfocusout: false,
			onkeyup: false,					
            rules: {
           	
            }
		});	
		
		$("#public_for_user_email_"+area_for).rules('add',{
			required: true,
			email:true
		});	
		
		$("#btn_send_password_user_"+area_for).bind("click",function(){
			if($("#form_forgot_"+area_for).valid()){
				$.ajax({
					type: 'POST',
					async: false,
					url: '/default/index/forgotpass',
					data: {
						public_for_user_email: $("#public_for_user_email_"+area_for).val(),
						website_id: $("#website_id").val()
					},
					dataType: 'json',
					
					success: function(data) {	
						if(data){
							window.location.reload(true);
						}
						$.fancybox.close();
					}
				});		
			}
		});		
		
	});
	setDefaultCalendarOldPbl($("#view_old_plublications"));	
	
	$(".fancybox").fancybox();
	$(".wicabox").fancybox();
	
	$(".various").fancybox({
		maxWidth	: 800,
		maxHeight	: 600,
		fitToView	: false,
		width		: '70%',
		height		: '70%',
		autoSize	: false,
		closeClick	: false,
		openEffect	: 'none',
		closeEffect	: 'none'
	});
	
	$( ".wicaDatepicker" ).datepicker({
        changeYear: true,
        yearRange: '1900:+0',
        dateFormat: 'dd/mm/yy'//,   
        //showOn: "both",
        //buttonImage: "/images/calendar.gif", 
        //buttonImageOnly: true
    });
	
});

$(function() {
  $('#nav').smartmenus();
});

$(window).load(function(){
	$('#showMenu').click(function() {
	 $('ul.sm-simple').show();
	 $('#hideMenu').show();
	 $('#showMenu').hide();
	});
	
	$('#hideMenu').click(function() {
	 $('ul.sm-simple').hide();
	 $('#hideMenu').hide();
	 $('#showMenu').show();
	});	
});
	
/**
  * Function to upload files by ajax
  * @action : Action controller
  * @directory: Directory where files are to upload
  * @nameFile: name $_FILE()
  * @content_id: content Id 
  */			
function load_file(element_sufix, element_type)
{
	
	new AjaxUpload('#'+element_sufix,{//UPLOADS FILE TO THE $_FILES VAR
		action: "/index/index/uploadfile",
		data:{
			directory: 'public/uploads/tmp/',
			maxSize: 22097152,
			type: element_type
		},
		name: 'content_file',
		onSubmit : function(file, ext){
			this.disable();
		},
		onComplete: function(file, response){//ONCE THE USER SELECTS THE FILE
			this.enable();
			if(isNaN(response)){//IF THE RESPONSE OF uploadFile.rpc ITS NOT A NUMBER (NOT AN ERROR)
				//DELETING PREVIOUS PICTURE IF IT EXISTS
				if($("#hdnNameFile_"+element_sufix).val()){
					$.ajax({
						url: "/index/index/deletefile",
						type: "post",
						data: ({
							file: function(){
								return $("#hdnNameFile").val();
							}
						}),
						success: function(data) {
						}
					});
				}					
				if(element_type == 'image'){
					$('#imageprw_'+element_sufix).attr('src', "/uploads/tmp/"+response);
					$('#imageprw_'+element_sufix).show();
				}
				// resize tree height according content
				setSectionTreeHeight();	
				
				$('#input_file_'+element_sufix).val(file);
				$('#hdnNameFile_'+element_sufix).val(response);
				
			}else{//ERRORS ON THE FILE UPLOADED
				
				if(response == 1){
					alert("El tamaño del archivo es demasiado grande");
				}
				if(response == 2){
					alert("Extensión no válida");
				}
			}
		}
	});
		
}
