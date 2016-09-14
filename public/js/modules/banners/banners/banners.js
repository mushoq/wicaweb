$(document).ready(function() {    
	
	//Sortable banner list
	$("#sortable").sortable(
			{
			handle: ".handler",
			axis: "y",
			cursor: "move"
			}
	);	
	$("#sortable").disableSelection();

	//New banner action
	$("#new_banner").bind("click",function(){
		$('#cms_container').load("/banners/banners/new", {
			id : $("#section_id").val()
		}, function() {		
			//Initialize Form
			$('#frmBanners').hide();
			$('#dates_container').hide();
			$('#hits_container').hide();
			
			//Show Banner type form: Picture 
			$('#image_btn').bind('click',function(){
				$('#btn_value').val('image');
				$('#flash_btn').removeClass('active');
				$('#html_btn').removeClass('active');
				$(this).addClass('active');
				if ($('#frmBanners').is(':hidden'))
				{
					$('#frmBanners').removeClass('hide');
				}
				$('#flash_container').hide();
				$('#html_container').hide();
				$('#image_container').removeClass('hide');


			});
			
			//Show Banner type form: Flash 
			$('#flash_btn').bind('click',function(){
				$('#btn_value').val('flash');
				$('#image_btn').removeClass('active');
				$('#html_btn').removeClass('active');
				$(this).addClass('active');
				if ($('#frmBanners').is(':hidden'))
				{
					$('#frmBanners').removeClass('hide');
				}
				$('#flash_container').removeClass('hide');
				$('#html_container').hide();
				$('#image_container').hide();
			});
			
			//Show Banner type form: HTML 
			$('#html_btn').bind('click',function(){
				$('#btn_value').val('html');
				$('#image_btn').removeClass('active');
				$('#flash_btn').removeClass('active');
				$(this).addClass('active');
				if ($('#frmBanners').is(':hidden'))
				{
					$('#frmBanners').removeClass('hide');
				}
				$('#flash_container').hide();
				$('#html_container').removeClass('hide');
				$('#image_container').hide();
			});
			
			//Set calendars
			
			setDefaultCalendar($('#publish_date'),$('#expire_date'));
			
			
			//Validate	
			$("#frmBanners").validate({
		        wrapper: "span",
		        onfocusout: false,
		        onkeyup: false,
		        rules: {
		                name: {
		                 	required: true, 
		                	remote: {
		                 		url: "/banners/banners/validatebannername",
		                         type: "post",
		                         async:false,
		                         data: {
		 	                        name: function() {
		 	                          return $("#name").val();
		 	                        }
		                         }
		                 	
		                 	 }
		                 	 
		                },
		                type:{
		                	required: true
		                },
		        	},
                  messages:{
	                    name:{
	                     	remote: bannername_remote_message
	                    }
	              }
		        });
		
			//Add validate rule for empty sections in edit banner of website tree link
			if($('#section_id').val()=='all'){
	        	var settings = $('#frmBanners').validate().settings;
	        	settings.rules.sections = {
	        		required: true
	        	};
			}
			
			//Listeners
			
			//Publication type
			$('[id^=publication_type_btn_]').each(function(){	
				$(this).bind('click',function(){
					$('#type').val($(this).attr('option'));
					if($('#type').val()=='calendar'){
						//Actions calendar
						$('#dates_container').removeClass('hide');
						$('#hits_container').hide();
						
						//Add validation rules
			        	var settings = $('#frmBanners').validate().settings;
			        	settings.rules.publish_date = {
			        		required: true
			        	};
			        	settings.rules.expire_date = {
				        		required: true
				        };
			        	delete settings.rules.hits;
	
					}else if($('#type').val()=='hits'){
						//Actions hits
						$('#dates_container').hide();
						$('#hits_container').removeClass('hide');
						//Add validation rules
			        	var settings = $('#frmBanners').validate().settings;
			        	settings.rules.hits = {
			        		required: true
			        	};
			        	delete settings.rules.publish_date;
			        	delete settings.rules.expire_date;
					}
				});		
			});	
			
			/*Image control*/
			
			//Upload image Banner
			$('[id^="img_"]').each(function(){
				//Add validation rule
				$("#hdnNameFile_"+this.id.replace("img_","")).rules("add", {				 
					 accept: "jpg,png,gif,jpeg"
				});
				
				//Get image id
				element_sufix = this.id.replace("img_","");
				//Get image id
				load_picture(element_sufix);
				
			});
			
			//Upload flash Banner
			$('#flash_1').each(function(){		
				//Add validation rule
				$("#hdnNameFileFlash_1").rules("add", {				 
					 accept: "swf"
				});
				
				//Get flash id
				element_sufix = this.id.replace("flash_","");
				//Call load flash function
				load_flash(element_sufix);
				
			});
			
			//submit button
		    $('#save_btn').bind('click',function(){
		    	
				//Validation rules accord banner type
				
		    	switch ($('#btn_value').val()) {
			        case "image":
			        	var settings = $('#frmBanners').validate().settings;
			        	settings.rules.hdnNameFile_1 = {
			        		required: true
			        	};
			        	delete settings.rules.hdnNameFileFlash_1;
			        	delete settings.rules.html;
			            break;
			        case "flash":
			        	var settings = $('#frmBanners').validate().settings;
			        	settings.rules.hdnNameFileFlash_1 = {
			        		required: true
			        	};
			        	delete settings.rules.hdnNameFile_1;
			        	delete settings.rules.html;
			            break;

			            break;
			        case "html":
			        	var settings = $('#frmBanners').validate().settings;
			        	settings.rules.html = {
			        		required: true
			        	};
			        	delete settings.rules.hdnNameFile_1;
			        	delete settings.rules.hdnNameFileFlash_1;
			            break;
		    	} 


		    	if($("#frmBanners").valid()){
					//ajax save
					$.ajax({
						type: 'POST',
						async: false,
						url: '/banners/banners/save',
						dataType: 'json',
						data: 	$( "#frmBanners" ).serialize(),
						success: function(data) {	
							if(data['section_id']){
								$('#cms_container').load("/banners/banners/index", {
									id: data['section_id']
								},function(){							
									$.getScript('/js/modules/banners/banners/banners.js');				
								});
							}
						}								
					});		    		
		    	}
		    });
			
		});
	});	
	
	//Edit Banner 
	$('[id^="edit_banner_"]').each(function() {
		$(this).bind('click', function() {
			//mark_edit_section_selected($(this));
			$('#cms_container').load("/banners/banners/edit", {
				banner_id: this.id.replace('edit_banner_',''),
				section_id: $('#section_id').val()
			},function(){
				
				$.getScript('/js/modules/banners/banners/banners.js');
				$('#frmBanners').hide();
				
				//Show form depend banner_type
				
				//If content is image
				if($('#btn_value').val()=='image'){
					$('#btn_value').val('image');
					$('#flash_btn').removeClass('active');
					$('#html_btn').removeClass('active');
					$('#image_btn').addClass('active');
					if ($('#frmBanners').is(':hidden'))
					{
						$('#frmBanners').removeClass('hide');
					}
					$('#flash_container').hide();
					$('#html_container').hide();
					$('#image_container').removeClass('hide');
				}
				
				//If content is flash
				if($('#btn_value').val()=='flash'){
					$('#btn_value').val('flash');
					$('#flash_btn').addClass('active');
					$('#html_btn').removeClass('active');
					$('#image_btn').removeClass('active');
					if ($('#frmBanners').is(':hidden'))
					{
						$('#frmBanners').removeClass('hide');
					}
					$('#flash_container').removeClass('hide');
					$('#html_container').hide();
					$('#image_container').hide();				
				}
				
				//If content is html	
				if($('#btn_value').val()=='html'){
					$('#btn_value').val('html');
					$('#flash_btn').removeClass('active');
					$('#html_btn').addClass('active');
					$('#image_btn').removeClass('active');
					if ($('#frmBanners').is(':hidden'))
					{
						$('#frmBanners').removeClass('hide');
					}
					$('#flash_container').hide();
					$('#html_container').removeClass('hide');
					$('#image_container').hide();
				}
				
				//Show fields by publication type of banner
				if($('#type').val()=='calendar'){
					$('#hits_container').hide();
				}else if($('#type').val()=='hits'){
					$('#dates_container').hide();
				}

				
				//Show Banner type form: Picture 
				$('#image_btn').bind('click',function(){
					$('#btn_value').val('image');
					$('#flash_btn').removeClass('active');
					$('#html_btn').removeClass('active');
					$(this).addClass('active');
					if ($('#frmBanners').is(':hidden'))
					{
						$('#frmBanners').removeClass('hide');
					}
					$('#flash_container').hide();
					$('#html_container').hide();
					$('#image_container').removeClass('hide');


				});
				
				//Show Banner type form: Flash
				$('#flash_btn').bind('click',function(){
					$('#btn_value').val('flash');
					$('#image_btn').removeClass('active');
					$('#html_btn').removeClass('active');
					$(this).addClass('active');
					if ($('#frmBanners').is(':hidden'))
					{
						$('#frmBanners').removeClass('hide');
					}
					$('#flash_container').removeClass('hide');
					$('#html_container').hide();
					$('#image_container').hide();
				});
				
				//Show Banner type form: HTML
				$('#html_btn').bind('click',function(){
					$('#btn_value').val('html');
					$('#image_btn').removeClass('active');
					$('#flash_btn').removeClass('active');
					$(this).addClass('active');
					if ($('#frmBanners').is(':hidden'))
					{
						$('#frmBanners').removeClass('hide');
					}
					$('#flash_container').hide();
					$('#html_container').removeClass('hide');
					$('#image_container').hide();
				});
				
				//Set calendars
				setDefaultCalendar($('#publish_date'),$('#expire_date'));
				
				
				//Validate Form
				$("#frmBanners").validate({
			        wrapper: "span",
			        onfocusout: false,
			        onkeyup: false,
			        rules: {
			                name: {
			                 	required: true, 
			                	remote: {
			                 		url: "/banners/banners/validatebannername",
			                         type: "post",
			                         async:false,
			                         data: {
			 	                        name: function() {
			 	                          return $("#name").val();
			 	                        },
			  	                       id: function() {
			  	                          return $("#id").val();
			  	                        }
			                         }
			                 	
			                 	 }
			                 	 
			                },
			                type:{
			                	required: true
			                }
			        	},
	                  messages:{
		                    name:{
		                     	remote: bannername_remote_message
		                    }
		              }
			        });
				
				//Add validate rule for empty sections in edit banner of website tree link
				if($('#section_id').val()=='all'){
		        	var settings = $('#frmBanners').validate().settings;
		        	settings.rules.sections = {
		        		required: true
		        	};
				}
				
				//Publication type
				$('[id^=publication_type_btn_]').each(function(){	
					$(this).bind('click',function(){
						$('#type').val($(this).attr('option'));
						if($('#type').val()=='calendar'){
							//Actions calendar
							
							$('#dates_container').removeClass('hide');
							$('#hits_container').hide();
							
							//Add validate rules
				        	var settings = $('#frmBanners').validate().settings;
				        	settings.rules.publish_date = {
				        		required: true
				        	};
				        	settings.rules.expire_date = {
					        		required: true
					        };
				        	delete settings.rules.hits;
		
						}else if($('#type').val()=='hits'){
							//Actions hits
							$('#dates_container').hide();
							$('#hits_container').removeClass('hide');
							//Add validate rules
				        	var settings = $('#frmBanners').validate().settings;
				        	settings.rules.hits = {
				        		required: true
				        	};
				        	delete settings.rules.publish_date;
				        	delete settings.rules.expire_date;
						}
					});		
				});	
				
				
				//Upload image Banner
				
				$('#img_1').each(function(){
					$("#hdnNameFile_"+this.id.replace("img_","")).rules("add", {				 
						 accept: "jpg,png,gif,jpeg"
					});
					
					element_sufix = this.id.replace("img_","");
					load_picture(element_sufix);
					
				});
				
				
				
				//Upload flash Banner
				$('#flash_1').each(function(){
					
					$("#hdnNameFileFlash_1").rules("add", {			 
						 accept: "swf"
					});
					
					
					element_sufix = this.id.replace("flash_","");
					load_flash(element_sufix);
					
				});
				
				
				//submit button
			    $('#save_btn').bind('click',function(){
			    	
					//Validation rules accord banner type
					
			    	switch ($('#btn_value').val()) {
				        case "image":
				        	var settings = $('#frmBanners').validate().settings;
				        	settings.rules.fileLabel_1 = {
				        		required: true
				        	};
				        	delete settings.rules.fileLabelFlash_1;
				        	delete settings.rules.html;
				            break;
				        case "flash":
				        	var settings = $('#frmBanners').validate().settings;
				        	settings.rules.fileLabelFlash_1 = {
				        		required: true
				        	};
				        	delete settings.rules.fileLabel_1;
				        	delete settings.rules.html;
				            break;

				            break;
				        case "html":
				        	var settings = $('#frmBanners').validate().settings;
				        	settings.rules.html = {
				        		required: true
				        	};
				        	delete settings.rules.fileLabel_1;
				        	delete settings.rules.fileLabelFlash_1;
				            break;
			    	} 


			    	
			    	if($("#frmBanners").valid()){
						//ajax save
						$.ajax({
							type: 'POST',
							async: false,
							url: '/banners/banners/save',
							dataType: 'json',
							data: 	$( "#frmBanners" ).serialize(),
							success: function(data) {	
								if(data['section_id']){
									$('#cms_container').load("/banners/banners/index", {
										id: data['section_id']
									},function(){							
										$.getScript('/js/modules/banners/banners/banners.js');				
									});
								}
							}								
						});		    		
			    	}
			    });
			});
			
		});	
	});
    
	//Delete banner
	$('[id^="delete_banner_"]').each(function() {
		$(this).bind('click', function() {
			var question = false;
			question = confirm(delete_question);
			if(question){
				$.ajax({
					type: 'POST',
					async: false,
					url: '/banners/banners/delete',
					dataType: 'json',
					data: 	{
						id: this.id.replace("delete_banner_",""),
						section_id: $('#section_id').val()
					},
					success: function(data) {													
						if(data['serial'])
						{
							$('#cms_container').load("/banners/banners/index", {
								id: data['serial']
							}, function() {				
								$.getScript('/js/modules/banners/banners/banners.js');
							});	
						}
					}								
				});			
			}
		});	
	});
	

	
	//save banners order
	$('#save_order').bind('click', function() {		
		var banners_list = $("#sortable").sortable("toArray");		
		$('#banner_order').val(banners_list.join(','));

		$.ajax({
			type: 'POST',
			async: false,
			url: '/banners/banners/saveorder',
			dataType: 'json',
			data: 	$( "#frmBannersOrder" ).serialize(),
			success: function(data) {
				if(data['serial'])
				{					
					$('#cms_container').load("/banners/banners/index", {
						id: data['serial']
					},function(){						
						$( 'html, body' ).animate( {scrollTop: 0}, 0 );
						$.getScript('/js/modules/banners/banners/banners.js');
					});
				}
			}								
		});
	});
	
	
	//link banner
	$('#link_banner').bind('click',function(){
		//select the option in the section bar
		//remove_selected_option();
		$(this).parent('li').addClass('selected');
		//load the corresponding view
		$('#cms_container').load("/banners/banners/link", {
			search_content: 0,
			section_tree_id: $('#section_id').val()
		}, function() {
			setSectionTreeHeight();
			$.getScript('/js/modules/banners/banners/link.js');												
		});
	});
	
    
}); //END DOCUMENT ROOT

function clearTemplates(){
	$('[id^=template_opt_]').each(function(){
		$(this).removeClass('active');
		$(this).siblings('img').removeClass('border_image');
	});
}


//uploads a banner picture			
function load_picture(element_sufix)
{
	
	new AjaxUpload('#img_'+element_sufix,{//UPLOADS FILE TO THE $_FILES VAR
		action: "/banners/banners/uploadfile",
		data:{
			directory: 'public/uploads/tmp/',
			maxSize: 2097152
		},
		name: 'section_photos',
		onSubmit : function(file, ext){
			this.disable();
		},
		onComplete: function(file, response){//ONCE THE USER SELECTS THE FILE
			this.enable();
			if(isNaN(response)){//IF THE RESPONSE OF uploadFile.rpc ITS NOT A NUMBER (NOT AN ERROR)
				//DELETING PREVIOUS PICTURE IF IT EXISTS
				if($("#hdnNameFile_"+element_sufix).val()){
					$.ajax({
						url: "/banners/banners/deletetemppicture",
						type: "post",
						data: ({
							file_tmp: function(){
								return $("#hdnNameFile_"+element_sufix).val();
							}
						}),
						success: function(data) {
						}
					});
				}								
				$('#imageprw_'+element_sufix).attr('src', "/uploads/tmp/"+response);
				$('#imageprw_'+element_sufix).removeClass('hide');										
				$('#fileLabel_'+element_sufix).val(file);
				$('#hdnNameFile_'+element_sufix).val(response);
				$("#del_img_"+element_sufix).removeClass('hide');
				
			}else{//ERRORS ON THE FILE UPLOADED
				if(response == 1){
					alert(max_size);
				}
				if(response == 2){
					alert(supported_extension);
				}
			}
		}
	});
}

//uploads a banner picture			
function load_flash(element_sufix)
{
	
	new AjaxUpload('#flash_'+element_sufix,{//UPLOADS FILE TO THE $_FILES VAR
		action: "/banners/banners/uploadfile",
		data:{
			directory: 'public/uploads/tmp/',
			maxSize: 2097152
		},
		name: 'section_photos',
		onSubmit : function(file, ext){
			this.disable();
		},
		onComplete: function(file, response){//ONCE THE USER SELECTS THE FILE
			this.enable();
			if(isNaN(response)){//IF THE RESPONSE OF uploadFile.rpc ITS NOT A NUMBER (NOT AN ERROR)
				//DELETING PREVIOUS PICTURE IF IT EXISTS
				if($("#hdnNameFileFlash_"+element_sufix).val()){
					$.ajax({
						url: "/banners/banners/deletetemppicture",
						type: "post",
						data: ({
							file_tmp: function(){
								return $("#hdnNameFileFlash_"+element_sufix).val();
							}
						}),
						success: function(data) {
						}
					});
				}								
							
				$('#fileLabelFlash_'+element_sufix).val(file);
				$('#hdnNameFileFlash_'+element_sufix).val(response);

				
			}else{//ERRORS ON THE FILE UPLOADED
				if(response == 1){
					alert(max_size);
				}
				if(response == 2){
					alert(supported_extension);
				}
			}
		}
	});
}
