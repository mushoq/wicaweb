$(document).ready(function() {
	//Loading view
	$("#loader_spinner").bind("ajaxStart", function(){
	    $(this).show();
	}).bind("ajaxStop", function(){
		$(this).hide();
	});
	
    /*STEP 2*/
    
    //back button
    $('#step2_back').bind('click',function(){
        window.location = '/installer';
    });
    
    //next
    $('#step2_next').bind('click',function(){
       
       //check pre-install details
       	$.ajax({
               type: 'POST',
               url: '/installer/index/preinstalldetails',
               dataType: 'json',
               success: function(data) {
            	   if(data['php'] && data['mysql'] && data['writable']){
            		   $('#step2_container').addClass('hide');
            	       $('#step3_container').removeClass('hide');
            	       //mark selected step in the legend
            	       notSelectedStep(2);
                       selectedStep(3);
            	   }else{
            		   //show error messages
            		   $('#preinstaller_test_msg').removeClass('hide');
            		   $('#preinstaller_test_msg').addClass('alert-danger');
            		   $('#preinstaller_test_msg').removeClass('alert-success');
            		   if(!data['php']){
            			   $('#preinstaller_test_msg').html(php_version_fail);
            		   }
            		   if(!data['mysql']){
            			   $('#preinstaller_test_msg').html(mysql_fail);
            		   }
            		   if(!data['writable']){
            			   $('#preinstaller_test_msg').html(iniwritable_fail);
            		   }
            		   
            		   $('#step2_container').removeClass('hide');
            	       $('#step3_container').addClass('hide');
            		   
            	   }

               }
           });
      });
    /*END STEP 2*/
    
    /*STEP 3*/
    //validation
    $("#frmInstallStep3").validate({
		wrapper: "span",		
		onfocusout: false,
		onkeyup: false,
		rules: {
			db_name: {
				required: true
			},
			db_user: {
				required: true
			},
			db_password: {
//				required: true
			},
			db_host: {
				required: true
			}
		}
	});
    
    //back button
    $('#step3_back').bind('click',function(){
        $('#step3_container').addClass('hide');
        $('#step2_container').removeClass('hide');
        $('#test_connection_msg').addClass('hide');
        $('#create_db_fail_msg').addClass('hide');
        //mark selected step in the legend
	    notSelectedStep(3);
        selectedStep(2);
        
    });
    
    //next
    $('#step3_next').bind('click',function(){
    	//validate that the db_config is completed
    	if($("#frmInstallStep3").valid()){
	    	//Create database estructure
	       	$.ajax({
	               type: 'POST',
	               url: '/installer/index/createdbestructure',
	               dataType: 'json',
	               data: $( "#frmInstallStep3" ).serialize(),
	               success: function(data) {
	            	   if(data['success']==true){
	            		//write the new params in the application ini file   
	           	    	$.ajax({
	         	           type: 'POST',
	         	            async: false,
	         	            url: '/installer/index/writeparameters',
	         	            dataType: 'json',
	         	            data: $( "#frmInstallStep3,#frmInstallStep4" ).serialize(),
	         	            success: function(data) {
	         	                if(data['success']==true){
	         	                	//mark selected step in the legend
	        	           		    notSelectedStep(3);
	        	           	        selectedStep(4);
	        	           	        $('#step4_container').removeClass('hide');
	        	           	        $('#step3_container').addClass('hide');
	         	               }
	         	               else if(data['success']==false){
	         	                	$('#create_db_fail_msg').removeClass('hide');
	         	                	$('#create_db_fail_msg').addClass('alert-danger');
	         	               	 	$('#create_db_fail_msg').html(write_app_fail);
	         	               	 	
	         	                }
	         	            }
	           	    	});
	                   }
	                   else if(data['success']==false){
	                	   $('#step3_container').removeClass('hide');
	            	       $('#step4_container').addClass('hide');
	            	       $('#create_db_fail_msg').removeClass('hide');
	                   		$('#create_db_fail_msg').removeClass('alert-success');
	                   		$('#create_db_fail_msg').addClass('alert-danger');
	                  	 	$('#create_db_fail_msg').html(create_db_error);
	                   }
	
	               }
	           });
    	}
       
    });
    
    //test_connection
    $('#test_connection').bind('click',function(){
    	if($("#frmInstallStep3").valid()){
    		$.ajax({
                type: 'POST',
                url: '/installer/index/dbtest',
                dataType: 'json',
                data: $( "#frmInstallStep3" ).serialize(),
                success: function(data) {
                    if(data['success']==true){
                    	$('#test_connection_msg').removeClass('hide');
                    	$('#test_connection_msg').addClass('alert-success');
                    	$('#test_connection_msg').removeClass('alert-danger');
                    	$('#test_connection_msg').html(db_test_success);
                    }
                    else if(data['success']==false){
                    	$('#test_connection_msg').removeClass('hide');
                    	$('#test_connection_msg').removeClass('alert-success');
                    	$('#test_connection_msg').addClass('alert-danger');
                   	 	$('#test_connection_msg').html(db_test_fail);
                    }
                }
            });
    	}
    });
    
    //create db
    $("[id^='db_create_']").each(function(){
    	$(this).bind('click',function(){
    		$('#db_new').val($(this).attr('option'));
    		//show or hide the root message
    		if($(this).attr('option')=='yes')
    			$('#root_info_msg_container').removeClass('hide');
    		else
    			$('#root_info_msg_container').addClass('hide');
    	});
    });
    /*END STEP 3*/
    
    /*STEP 4*/
    //validation
    $("#frmInstallStep4").validate({
		wrapper: "span",		
		onfocusout: false,
		onkeyup: false,
		rules: {
			user_name: {
				required: true
			},
			user_lastname: {
				required: true
			},
			user_email: {
				email: true
			},
			user_username: {
				required: true
			},
			user_password: {
				required: true,
            	minlength: 6
			},
			user_conf_password: {
				required: true,
            	minlength: 6,
            	equalTo: "#user_password"
			}
		},
	    messages:{
	    	user_conf_password:{
	    		equalTo: confirm_password_message
	    	}
	    }
	});
    
    //back button
    $('#step4_back').bind('click',function(){
        $('#step4_container').addClass('hide');
        $('#step3_container').removeClass('hide');
        $('#test_connection_msg').addClass('hide');
        $('#create_db_fail_msg').addClass('hide');
        //mark selected step in the legend
	    notSelectedStep(4);
        selectedStep(3);
    });
    
    //next
    $('#step4_next').bind('click',function(){
    	if($("#frmInstallStep4").valid()){
    		$.ajax({
                type: 'POST',
                url: '/installer/index/createadminuser',
                dataType: 'json',
                data: $( "#frmInstallStep3,#frmInstallStep4" ).serialize(),
                success: function(data) {
                    if(data['success']==true){
                    	//mark selected step in the legend
                    	notSelectedStep(4);
                        selectedStep(5);
                    	$('#step4_container').addClass('hide');
             	        $('#step5_container').removeClass('hide');
             	        //add default info to the express website form
             	        $('#info_email').val(data['user_email']);
             	        $('#copyright').val(data['user_name']);
             	        
                    }
                    else if(data['success']==false){
                    	$('#step4_container').removeClass('hide');
             	        $('#step5_container').addClass('hide');
                    	$('#create_admin_error_msg').removeClass('hide');
                    	$('#create_admin_error_msg').removeClass('alert-success');
                    	$('#create_admin_error_msg').addClass('alert-danger');
                   	 	$('#create_admin_error_msg').html(create_admin_fail);
                   	 	
                    }
                }
            });
    	}
    });
    /*END STEP 4*/
    
    
    /*STEP 5*/
  //validation
    $("#frmInstallStep5").validate({
		wrapper: "span",		
		onfocusout: false,
		onkeyup: false,
		rules: {
			config_type: {
				required: true
			}
		}
	});
  
    //back button
    $('#step5_back').bind('click',function(){
    	$('#step5_container').addClass('hide');
	    $('#step4_container').removeClass('hide');
	    $('#create_admin_error_msg').addClass('hide');
	    //mark selected step in the legend
	    notSelectedStep(5);
        selectedStep(4);
    });
    
    //next
    $('#step5_next').bind('click',function(){
    	if($("#frmInstallStep5").valid()){
    		if($('#config_type').val()=='express'){
	    		//load the form
	            $.ajax({
	                type: 'POST',
	                url: '/installer/index/step6',
	                dataType: 'html',
	                success: function(data) {
	                    $('#step6_container').html(data);
	                    $('#step5_container').addClass('hide');
	                    $('#step6_container').removeClass('hide');
	                    //mark selected step in the legend
	    			    notSelectedStep(5);
	    		        selectedStep(6);
	                    //load JS actions for further steps
	                    $.getScript('/js/modules/installer/index/step6.js');
	                }
	            });
	    	}
	    	else if($('#config_type').val()=='advanced'){
		    	$.ajax({
			           type: 'POST',
			            async: false,
			            url: '/installer/index/autologinajax',
			            dataType: 'json',
			            success: function(data) {
			                if(data['success']==true){
			                	window.location = '/core/website_website/new';
			         	        
			               }
			               else if(data['success']==false){
			                	$('#write_app').removeClass('hide');
			                	$('#write_app').addClass('alert-danger');
			               	 	$('#write_app').html(advanced_website_fail);
			               	 	
			                }
			            }
			        });
	    	}
	    	
    	}
    });
    //choose website config
    $("[id^='website_config_']").each(function(){
    	$(this).bind('click',function(){
    		if($(this).attr('option')=='express'){
    			$('#adv_config_type_msg').addClass('hide');
    			$('#exp_config_type_msg').removeClass('hide');
    			$('#config_type').val($(this).attr('option'));
    		}
    		else if($(this).attr('option')=='advanced'){
    			$('#exp_config_type_msg').addClass('hide');
    			$('#adv_config_type_msg').removeClass('hide');
    			$('#config_type').val($(this).attr('option'));
    		}
    	});
    });
    /*END STEP 5*/
        
}); //END DOCUMENT ROOT
