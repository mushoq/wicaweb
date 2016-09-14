$(document).ready(function(){
	$("#frmWebsite").validate({
        wrapper: "span",
        onfocusout: false,
        onkeyup: false,
        rules: {
                name: {
                    required: true,
					remote:{
						url: "/core/website_website/checkname",
						type: "POST",
						data:{
							website_id: $('#id').val()
						}
					}
                },
                description: {
                    required: true
                },
                keywords:{
                	required: true
                },
                website_url:{
                	required: true
                },
                info_email:{
                	required: true,
                	email: true
                },
                time_zone:{
                	required: true
                },
                date_format:{
                	required: true
                },
                hour_format:{
                	required: true
                },
                number_format:{
                	required: true
                },
                copyright:{
                	required: true
                },
                sitemap_level:{
                	required: true,
                	digits: true,
                	min: 1
                },
                section_images_number:{
                	required: true,
                	digits: true,
                	min: 0,
                	max: 4
                },
                smtp_hostname:{
//                	required: true
                },
                smtp_port:{
//                	required: true,
                	digits: true
                },
                smtp_username:{
//                	required: true
                },
                smtp_password:{
//                	required: true
                },
                publication_approve:{
                	required: true
                },
                prints:{
                	required: true
                },
                friendly_url:{
                	required: true
                },
                tiny_url:{
                	required: true
                },
                log:{
                	required: true
                },
                dictionary:{
                	required: true
                },
                private_section:{
                	required: true
                },
                section_expiration:{
                	required: true
                },
                section_author:{
                	required: true
                },
                section_feature:{
                	required: true
                },
                section_highlight:{
                	required: true
                },
                section_comments_management:{
                	required: true
                },
                section_rss:{
                	required: true
                },
                offline_text:{
                	required: true
                },
                coming_soon_text:{
                	required: true
                },
                logo:{
                	accept: "jpg,png,gif,jpeg"
                },
                icon:{
                	accept: "jpg,png,gif,jpeg,ico"
                },
                watermark:{
                	accept: "jpg,png,gif,jpeg"
                },
                offline_image:{
                	accept: "jpg,png,gif,jpeg"
                },
                coming_soon_image:{
                	accept: "jpg,png,gif,jpeg"
                },
                max_height:{
                	digits:true
                },
                max_width:{
                	digits:true
                }  
        },
        messages:{
        	name:{
        		remote: $("#repeated_website_name").val()
        	}
        }
	});

	//expiration time validation
	if($("#section_expiration").val()=='yes'){
		$("#section_storage_container").removeClass('hide');

		$("#section_storage").rules("add",{
			required:true
		});		
		
		if($("#section_storage").val()=='yes'){
			$("#section_expiration_time_container").removeClass('hide');
	
			$("#section_expiration_time").rules("add",{
				required:true,
				digits:true
			});
		}
	}else{
		$("#section_storage_container").hide();
		$("#section_expiration_time_container").hide();
		
		$("#section_expiration_time").rules("remove");	
		$("#section_storage").rules("remove");
	}	
	
	//submit_form
	$('#submit').bind('click',function(){
		if($('#frmWebsite').valid()){
			$('#frmWebsite').submit();
		}
		else{
			var container = '';
			$('input[type=text], textarea, select, input[type=hidden]').each(function(){
				if($(this).hasClass('error_validation')){
					container = $(this).attr('container');
					$('#'+container).addClass('in');
					$('#'+container).removeClass('collapse');
					$('#'+container).css('height','auto');
				}
			});
		}
	});
	
	//button accion
	$('#cancel').bind('click',function(){
		window.location = '/core/website_website';
	});
	
	//show first tab
	$('#collapseOptions').addClass('in');
	
	//listener continue buttons
	$('[id^=continue_]').each(function(){
		$(this).bind('click',function(){
			$('#'+$(this).attr('current')).removeClass('in');
			$('#'+$(this).attr('current')).addClass('collapse');
			$('#'+$(this).attr('current')).css('height','0px');
			$('#'+$(this).attr('next')).addClass('in');
			$('#'+$(this).attr('next')).removeClass('collapse');
			$('#'+$(this).attr('next')).css('height','auto');
		});
	});
	
	//collapse config options
	$('#collapseOptions').on('hidden', function () {
    	$(this).css('overflow','hidden');
    });
	
	$('#collapseFormats').on('hidden', function () {
    	$(this).css('overflow','hidden');
    });
	
	$('#collapseSections').on('hidden', function () {
    	$(this).css('overflow','hidden');
    });
	
	$('#collapseCommentsManagement').on('hidden', function () {
    	$(this).css('overflow','hidden');
    });
	
	$('#collapseSMTPConfig').on('hidden', function () {
    	$(this).css('overflow','hidden');
    });
	
	$('#collapseOther').on('hidden', function () {
    	$(this).css('overflow','hidden');
    });
	
	$('#collapseStates').on('hidden', function () {
    	$(this).css('overflow','hidden');
    });	
	
	
	//hide elements
	$('#section_comments_type_container').hide();
	$('#section_comments_container').hide();
	
	//listeners
	$('[id^=hour_]').each(function(){
		$(this).bind('click',function(){
			$('#hour_format').val($(this).attr('option'));
		});		
	});
	
	$(".btn-group > .btn").click(function(){
		$(this).addClass("current").siblings().removeClass("active");
	});
	
	$('[id^=private_section_]').each(function(){
		$(this).bind('click',function(){
			$('#private_section').val($(this).attr('option'));
		});		
	});
	
	$('[id^=section_feature_]').each(function(){	
		$(this).bind('click',function(){
			$('#section_feature').val($(this).attr('option'));
		});		
	});
	
	$('[id^=section_storage_]').each(function(){	
		$(this).bind('click',function(){
			$('#section_storage').val($(this).attr('option'));
			
			if($("#section_storage").val()=='yes'){
				$("#section_expiration_time_container").removeClass('hide');
		
				$("#section_expiration_time").rules("add",{
					required:true,
					digits:true
				});
			}else{
				$("#section_expiration_time").val('');
				$("#section_expiration_time_container").hide();
				
				$("#section_expiration_time").rules("remove");	
			}			
		});		
	});
	
	$('[id^=section_highlight_]').each(function(){
		$(this).bind('click',function(){
			$('#section_highlight').val($(this).attr('option'));
		});		
	});
	
	$('[id^=section_expiration_]').each(function(){	
		$(this).bind('click',function(){
			$('#section_expiration').val($(this).attr('option'));
			//expiration time validation
			if($("#section_expiration").val()=='yes'){
				$("#section_storage_container").removeClass('hide');

				$("#section_storage").rules("add",{
					required:true
				});		
				
			}else{
				$("#section_expiration_time").val('');
				$("#section_storage").val('');
				$("#section_storage_container").hide();
				$("#section_expiration_time_container").hide();
				
				$("#section_expiration_time").rules("remove");	
				$("#section_storage").rules("remove");
				
				$('input[id^=section_storage_]').each(function(){	
					$(this).removeClass('active');
				});
			}				
		});		
	});
	
	$('[id^=dictionary_]').each(function(){
		$(this).bind('click',function(){
			$('#dictionary').val($(this).attr('option'));
		});		
	});
	
	$('[id^=section_author_]').each(function(){	
		$(this).bind('click',function(){
			$('#section_author').val($(this).attr('option'));
		});		
	});
	
	$('[id^=prints_]').each(function(){	
		$(this).bind('click',function(){
			$('#prints').val($(this).attr('option'));
		});		
	});
	
	$('[id^=publication_approve_]').each(function(){
		$(this).bind('click',function(){
			$('#publication_approve').val($(this).attr('option'));
		});		
	});
	
	
	$('[id^=section_comments_management_]').each(function(){
		
		$(this).bind('click',function(){
			$('#section_comments_management').val($(this).attr('option'));
			if($(this).attr('option') == 'yes'){
				$('#section_comments_type_container').removeClass('hide');
				$('#section_comments_container').removeClass('hide');
				$('#section_comments_type').rules('add',{
					required: true
				});
			}
			else if($(this).attr('option') == 'no'){
				$('#section_comments_type_container').hide();
				$('#section_comments_container').hide();
				$('#section_comments_type').rules('remove');
				$('#section_comments').val('none');
				$('#section_comments_type').val('');
				$('[id^=section_comments_type_btn_]').each(function(){
					$(this).removeClass('active');
				});
			}
		});
		
		if($('#section_comments_management_yes').hasClass('active')){
			$('#section_comments_type_container').removeClass('hide');
			$('#section_comments_container').removeClass('hide');
			$('#section_comments_type').rules('add',{
				required: true
			});
		}
		else{
			$('#section_comments_type_container').hide();
			$('#section_comments_container').hide();
			$('#section_comments_type').rules('remove');
			$('#section_comments').val('none');
			$('#section_comments_type').val('');
		}
	});
	
	$('[id^=section_comments_type_btn_]').each(function(){	
		$(this).bind('click',function(){
			$('#section_comments_type').val($(this).attr('option'));
		});		
	});
	
	$('[id^=tiny_url_]').each(function(){
		$(this).bind('click',function(){
			$('#tiny_url').val($(this).attr('option'));
		});		
	});
	
	$('[id^=friendly_url_]').each(function(){	
		$(this).bind('click',function(){
			$('#friendly_url').val($(this).attr('option'));
		});		
	});
	
	
	$('[id^=log_]').each(function(){	
		$(this).bind('click',function(){
			$('#log').val($(this).attr('option'));
		});		
	});
	
	$('[id^=section_rss_]').each(function(){	
		$(this).bind('click',function(){
			$('#section_rss').val($(this).attr('option'));
		});		
	});
	
	
		
	//image previews
	$("#logo").bind("change",function(){
        readURL(this,'img_'+$(this).attr('id'));
	});
	
	$("#icon").bind("change",function(){
		readURL_icon(this,'img_'+$(this).attr('id'));
		$('#website_icon').html('&nbsp;&nbsp;'+$('#name').val());
		$('#deleted_icon').val('');
		$('#img_icon').removeClass('hide');
	});
	
	$("#watermark").bind("change",function(){
		$('#img_watermark').parent('div').removeClass('hide');
		readURL(this,'img_'+$(this).attr('id'));
		setTimeout("setWatermarkPos()",100);
	});
	
	$("#offline_image").bind("change",function(){
        readURL(this,'img_offline');
	});
	
	$("#coming_soon_image").bind("change",function(){
        readURL(this,'img_coming_soon');
	});
	
	//show pre-loaded images
	if($('#img_logo').attr('src')!=''){
		$('#img_logo').removeClass('hide');
	}
	if($('#img_icon').attr('src')!=''){
		$('#img_icon').removeClass('hide');
		$('#website_icon').html('&nbsp;&nbsp;'+$('#name').val());
		$('#deleted_icon').val('');
	}
	if($('#img_watermark').attr('src')!=''){
		$('#img_watermark').removeClass('hide');
	}
	if($('#img_offline').attr('src')!=''){
		$('#img_offline').removeClass('hide');
	}
	if($('#img_coming_soon').attr('src')!=''){
		$('#img_coming_soon').removeClass('hide');
	}
	
	//delete icon
	$('#delete_icon').bind('click',function(){
		$("#img_icon").attr('src','');
		$("#img_icon").attr('alt','');
		$('#img_icon').hide();
		$('#website_icon').html('');
		$('div.upload_icon').html('<input id="MAX_FILE_SIZE" type="hidden" value="2097152" name="MAX_FILE_SIZE"><input id="icon" type="file" style="width:100%" name="icon">');
		$('#deleted_icon').val('delete');
		
		$("#icon").bind("change",function(){
			readURL_icon(this,'img_'+$(this).attr('id'));
			$('#website_icon').html('&nbsp;&nbsp;'+$('#name').val());
			$('#deleted_icon').val('');
			$('#img_icon').removeClass('hide');
		});
	});
	
	//delete icon
	$('#delete_logo').bind('click',function(){
		$("#img_logo").attr('src','');
		$("#img_logo").attr('alt','');
		$('#img_logo').hide();
		$('#website_logo').html('');
		$('div.upload_logo').html('<input id="MAX_FILE_SIZE" type="hidden" value="2097152" name="MAX_FILE_SIZE"><input id="logo" type="file" style="width:100%" name="logo">');
		$('#deleted_logo').val('delete');
		
		$("#logo").bind("change",function(){
			readURL_icon(this,'img_'+$(this).attr('id'));
			
			$('#deleted_logo').val('');
			$('#img_logo').removeClass('hide');
		});
	});
	
	//website name in icon
	$('#name').bind('change',function(){
		if($("#img_icon").attr('src')!='')
			$('#website_icon').html('&nbsp;&nbsp;'+$('#name').val());
	});
	
	//template picker
	$('[id^=template_opt_]').bind('click',function(){
		clearTemplates();
		$(this).addClass('active');
		$('#template_id').val($(this).attr('template'));
		$(this).siblings('img').addClass('border_image');
	});
	
	//section number picker
	$("[id^='section_img_num_']").each(function(){
		$(this).bind("click", function(){
			$('#value_section_img_num').html($(this).attr('val'));
			$('#section_images_number').val($(this).attr('val'));
		});	
	});
	
	//sitemap levels
	$("[id^='sitemap_num_']").each(function(){
		$(this).bind("click", function(){
			$('#value_sitemap_level').html($(this).attr('val'));
			$('#sitemap_level').val($(this).attr('val'));
		});	
	});
	
	//default watermark pos
	if($('#watermark_pos').val()!=''){
		$("[id^='wmk_pos_']").each(function(){
			if($(this).attr('pos')==$('#watermark_pos').val()){
				$(this).addClass('btn-success');
				$('#img_watermark').parent('div').removeClass('hide');
				setWatermarkPos();
				$(this).click();
			}
		});
	}
	
});

function clearTemplates(){
	$('[id^=template_opt_]').each(function(){
		$(this).removeClass('active');
		$(this).siblings('img').removeClass('border_image');
	});
}

/**
 * Add action to the watermark position selector panel
 */
function setWatermarkPos(){
	//default
	$('#watermark_pos_container').removeClass('hide');
	setTimeout("setPosition($('#watermark_pos').val())",30);
	
	//listeners
	$("[id^='wmk_pos_']").each(function(){
		$(this).bind('click',function(){
			clearWatermarkPos();
			$(this).addClass('btn-success');
			//save value
			$('#watermark_pos').val($(this).attr('pos'));
			// change the position of the displayed image
			var pos = $(this).attr('pos');
			 setPosition(pos);
		});
	});
}

/**
 * Add action to the watermark position selector panel
 */
function clearWatermarkPos(){
	$("[id^='wmk_pos_']").each(function(){
		$(this).removeClass('btn-success');
	});
}

/**
 * Add action to the watermark position selector panel
 */
function setPosition(pos){
	//img size
	var height = $('#img_watermark').height();
	var top = (210/2)-(height/2);
	switch(pos){
		case 'TL':
			$('#img_watermark').css('bottom','');
			$('#img_watermark').css('right','');
			$('#img_watermark').css('top','0');
			$('#img_watermark').css('left','0');
			break;
		case 'T':
			$('#img_watermark').css('bottom','');
			$('#img_watermark').css('right','');
			$('#img_watermark').css('top','0');
			$('#img_watermark').css('left','33%');
			break;
		case 'TR':
			$('#img_watermark').css('bottom','');
			$('#img_watermark').css('left','');
			$('#img_watermark').css('top','0');
			$('#img_watermark').css('right','0');
			break;
		case 'L':
			$('#img_watermark').css('bottom','');
			$('#img_watermark').css('right','');
			$('#img_watermark').css('top',top+'px');
			$('#img_watermark').css('left','0');
			break;
		case 'C':
			$('#img_watermark').css('bottom','');
			$('#img_watermark').css('right','');
			$('#img_watermark').css('top',top+'px');
			$('#img_watermark').css('left','33%');
			break;
		case 'R':
			$('#img_watermark').css('bottom','');
			$('#img_watermark').css('left','');
			$('#img_watermark').css('top',top+'px');
			$('#img_watermark').css('right','0');
			break;
		case 'BL':
			$('#img_watermark').css('top','');
			$('#img_watermark').css('right','');
			$('#img_watermark').css('bottom','0');
			$('#img_watermark').css('left','0');
			break;
		case 'B':
			$('#img_watermark').css('top','');
			$('#img_watermark').css('right','');
			$('#img_watermark').css('bottom','0');
			$('#img_watermark').css('left','33%');
			break;
		case 'BR':
			$('#img_watermark').css('top','');
			$('#img_watermark').css('left','');
			$('#img_watermark').css('bottom','0');
			$('#img_watermark').css('right','0');
			break;
		default:
			$('#img_watermark').css('top','');
			$('#img_watermark').css('left','');
			$('#img_watermark').css('bottom','0');
			$('#img_watermark').css('right','0');
			break;
	}
}
