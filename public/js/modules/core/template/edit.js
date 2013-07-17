$(document).ready(function() {

	//Validation
	$("#frmTemplate").validate({
		wrapper: "span",
		onfocusout: false,
		onkeyup: false,					
        rules: {
        	name:{
				required:true,
				remote:{//check if internal name already exist
					url: "/core/template_template/checkname",
					async: false,
					type: "POST",
					data:{
						template_id : $('#template_id').val()
					}						
				}
			},
			hdn_template_file:{
				accept: 'phtml'
			},
			hdn_template_image:{
				accept: 'jpg,jpeg,png,gif'
			}
        },
        messages:{
        	name:{//message for remote validation
        		remote: $("#repeat_template").val()
        	}
        }
	});	
	
	//upload template file and template image
	if(!$("#template_file").attr('disabled'))
		load_file('template_file','template_file');
	
	load_file('template_image','template_image');

	load_multiple_file('image_files','images');
	load_multiple_file('css_files','css');	
	load_multiple_file('js_files','js');		
	
	//set inputs to show data 
	$("#input_template_file").val($("#hdn_template_file").val());
	$("#input_template_image").val($("#hdn_template_image").val());
	
	//set as empty hiddens to know if it will be a new file
	$("#hdn_template_file").val('');
	$("#hdn_template_image").val('');
	
	$("[id^='delete_css_']").each(function(){
		$(this).bind("click",function(){
			delete_template_files($(this).attr("index"), 'css', $(this).attr('old'), $(this).attr("template"));
			$("#"+$(this).attr("li")).remove();
		});
	});
	
	$("[id^='delete_js_']").each(function(){
		$(this).bind("click",function(){
			delete_template_files($(this).attr("index"), 'js', $(this).attr('old'), $(this).attr("template"));
			$("#"+$(this).attr("li")).remove();
		});
	});
	
	$("[id^='delete_image_']").each(function(){
		$(this).bind("click",function(){
			delete_template_files($(this).attr("index"), 'images', $(this).attr('old'), $(this).attr("template"));
			$("#"+$(this).attr("li")).remove();
		});
	});		
	
	
	$("#submit_btn").bind("click",function(){
		$("span[id^='error_']").each(function(){
			$(this).hide();
		});
		
		if($("#frmTemplate").valid()){
			if($('#hdn_template_file').val()){
				jQuery.get("/uploads/tmp/"+$('#hdn_template_file').val(),function(data) {
					if(data!='')
					{
						$("#div_read_file").html(data);
						var hdn_areas='';
						var error = 0;
						$("[id^='wica_area_']").each(function(){
							if($(this).parent().attr("class") && $(this).attr("id")){
								hdn_areas+=$(this).attr("id")+","+$(this).parent().attr("class").split(' ')[0]+';';
							}else{
								error = 1;
							}
						});
						$("#hdn_areas").val(hdn_areas);
						
						if(error == 0){
							if(hdn_areas)
							{	
								$("#hdn_css_files").val('');
								$("#hdn_js_files").val('');
								$("#hdn_image_files").val('');
								
								var css_detect=0;
								var js_detect=0;
								var images_detect=0;
								
								$("[id^='delete_css_']").each(function(){
									if($(this).attr('old')=='no')
										css_detect=1;
									$("#hdn_css_files").val($("#hdn_css_files").val()+$(this).attr("index")+',');
								});
								
								$("[id^='delete_js_']").each(function(){
									if($(this).attr('old')=='no')
										js_detect=1;								
									$("#hdn_js_files").val($("#hdn_js_files").val()+$(this).attr("index")+',');
								});
								
								$("[id^='delete_image_']").each(function(){
									if($(this).attr('old')=='no')
										images_detect=1;								
									$("#hdn_image_files").val($("#hdn_image_files").val()+$(this).attr("index")+',');
								});	
	
								$("[id^='media_css_']").each(function(){
									if($(this).val() == ' ')
										$("#hdn_media_css").val($("#hdn_media_css").val()+'null,');
									else
										$("#hdn_media_css").val($("#hdn_media_css").val()+$(this).val()+',');
								});
								
								if(css_detect==0)
									$("#hdn_css_files").val('');
								if(js_detect==0)
									$("#hdn_js_files").val('');
								if(images_detect==0)
									$("#hdn_image_files").val('');							
								
								$("#frmTemplate").submit();
							}
							else
								$("#error_no_area,label").show();
						}else
							$("#error_invalid_structure,label").show();
					}else
						$("#error_empty_file,label").show();
				}).error(function() { 
					$("#error_file,label").show();
				});	
			}else{
				
				$("#hdn_css_files").val('');
				$("#hdn_js_files").val('');
				$("#hdn_image_files").val('');
				
				var css_detect=0;
				var js_detect=0;
				var images_detect=0;
				
				$("[id^='delete_css_']").each(function(){
					if($(this).attr('old')=='no')
						css_detect=1;
					$("#hdn_css_files").val($("#hdn_css_files").val()+$(this).attr("index")+',');
				});
				
				$("[id^='delete_js_']").each(function(){
					if($(this).attr('old')=='no')
						js_detect=1;								
					$("#hdn_js_files").val($("#hdn_js_files").val()+$(this).attr("index")+',');
				});
				
				$("[id^='delete_image_']").each(function(){
					if($(this).attr('old')=='no')
						images_detect=1;								
					$("#hdn_image_files").val($("#hdn_image_files").val()+$(this).attr("index")+',');
				});	
				
				$("[id^='media_css_']").each(function(){
					if($(this).val() == ' ')
						$("#hdn_media_css").val($("#hdn_media_css").val()+'null,');
					else
						$("#hdn_media_css").val($("#hdn_media_css").val()+$(this).val()+',');
				});				
				
				if(css_detect==0)
					$("#hdn_css_files").val('');
				if(js_detect==0)
					$("#hdn_js_files").val('');
				if(images_detect==0)
					$("#hdn_image_files").val('');					
				
				$("#frmTemplate").submit();
			}
		}
		
	});
	
});

/**
 * load file by ajax upload
 * @param element_sufix
 * @param element_type
 */
function load_file(element_sufix, element_type)
{
	new AjaxUpload('#'+element_sufix,{//UPLOADS FILE TO THE $_FILES VAR
		action: "/core/template_template/uploadfile",
		data:{
			directory: 'public/uploads/tmp/',
			maxSize: 5097152,
			type: element_type
		},
		name: 'content_file',
		onSubmit : function(file, ext){
			this.disable();
		},
		onComplete: function(file, response){//ONCE THE USER SELECTS THE FILE
			this.enable();
			$("span[id^='error_']").each(function(){
				$(this).hide();
			});
			if(isNaN(response)){//IF THE RESPONSE OF uploadFile.rpc ITS NOT A NUMBER (NOT AN ERROR)
				//DELETING PREVIOUS PICTURE IF IT EXISTS
				if($("#hdn_"+element_sufix).val()){
					$.ajax({
						url: "/core/template_template/deletefile",
						type: "post",
						data: ({
							file: function(){
								return $("#hdn_"+element_sufix).val();
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
				
				$('#input_'+element_sufix).val(file);
				$('#hdn_'+element_sufix).val(response);
				
			}else{//ERRORS ON THE FILE UPLOADED
				
				if(response == 1){
					alert($("#file_too_big").val());
				}
				if(response == 2){
					alert($("#invalid_extension").val());
				}
				if(response == 4){
					$("#error_empty_file,label").show();
				}
			}
		}
	});
}

//deletes a section picture
function delete_file(index)
{
	$("#del_img").bind("click", function(){	
		if($("#hdnNameFile_"+index).val()){
			$.ajax({
				url: '/core/template_template/deletefile',
				type: "post",
				data: ({
						file: function(){
							return $("#hdnNameFile_"+index).val();
						}
					}),
					success: function(data) {
						$("#input_file_"+index).val("");
						$("#hdnNameFile_"+index).val("");
						$("#del_img_"+index).hide();
//						$('#imageprw_'+index).attr('src', "");
//						$('#imageprw_'+index).hide();
					}
			});
		}		
	});
}

/**
 * load multiple file by ajax upload
 * @param element_sufix
 * @param element_type
 */
function load_multiple_file(element_sufix, element_type)
{
	new AjaxUpload('#'+element_sufix,{//UPLOADS FILE TO THE $_FILES VAR
		action: "/core/template_template/uploadfile",
		data:{
			directory: 'public/uploads/tmp/',
			template: $("#template_folder").val()+'/',
			maxSize: 5097152,
			type: element_type,
			template_name: $("#template_name").val()
		},
		name: 'content_file',
		onSubmit : function(file, ext){
			this.disable();
		},
		onComplete: function(file, response){//ONCE THE USER SELECTS THE FILE
			this.enable();
			$("span[id^='error_']").each(function(){
				$(this).hide();
			});
			if(isNaN(response)){//IF THE RESPONSE OF uploadFile.rpc ITS NOT A NUMBER (NOT AN ERROR)
				if(element_type == 'css'){
					$("#css_files_list").append("<li id='css_file_"+response.replace(/[^a-zA-Z 0-9]+/g,'')+"'>"
													+response.substring(0,30)+"...<br/>"+
													"&ensp;media:&ensp;<input type='text' id='media_css_"+response.replace(/[^a-zA-Z 0-9]+/g,'')+"' name='media_css_"+response.replace(/[^a-zA-Z 0-9]+/g,'')+"'/>"+
													"&ensp;&ensp;<a id='delete_css_"+response.replace(/[^a-zA-Z 0-9]+/g,'')+"' title='Borrar' index='"+response+"' li='css_file_"+response.replace(/[^a-zA-Z 0-9]+/g,'')+"' old='no' template=''>" +
														"<i class='icon-trash'></i>" +
													"</a>" +
												"</li>");
				}else
					if(element_type == 'js'){
						$("#js_files_list").append("<li id='js_file_"+response.replace(/[^a-zA-Z 0-9]+/g,'')+"'>"
														+response.substring(0,30)+"..."+   
														"&ensp;<a id='delete_js_"+response.replace(/[^a-zA-Z 0-9]+/g,'')+"' title='Borrar' index='"+response+"' li='js_file_"+response.replace(/[^a-zA-Z 0-9]+/g,'')+"' old='no' template=''>" +
															"<i class='icon-trash'></i>" +
														"</a>" +
													"</li>");
					}else
						if(element_type == 'images'){
							$("#image_files_list").append("<li id='image_file_"+response.replace(/[^a-zA-Z 0-9]+/g,'')+"'>"
																+response.substring(0,30)+ "..."+  
																"&ensp;<a id='delete_image_"+response.replace(/[^a-zA-Z 0-9]+/g,'')+"' title='Borrar' index='"+response+"' li='image_file_"+response.replace(/[^a-zA-Z 0-9]+/g,'')+"' old='no' template=''>" +
																	"<i class='icon-trash'></i>" +
																"</a>" +
															"</li>");
						}
				
				$("[id^='delete_css_']").each(function(){
					$(this).bind("click",function(){
						delete_template_files($(this).attr("index"), 'css', $(this).attr('old'), $(this).attr("template"));
						$("#"+$(this).attr("li")).remove();
					});
				});
				
				$("[id^='delete_js_']").each(function(){
					$(this).bind("click",function(){
						delete_template_files($(this).attr("index"), 'js', $(this).attr('old'), $(this).attr("template"));
						$("#"+$(this).attr("li")).remove();
					});
				});
				
				$("[id^='delete_image_']").each(function(){
					$(this).bind("click",function(){
						delete_template_files($(this).attr("index"), 'images', $(this).attr('old'), $(this).attr("template"));
						$("#"+$(this).attr("li")).remove();
					});
				});				
				
			}else{//ERRORS ON THE FILE UPLOADED
				
				if(response == 1){
					alert($("#file_too_big").val());
				}
				if(response == 2){
					alert($("#invalid_extension").val());
				}
				if(response == 4){
					$("#error_empty_file,label").show();
				}
				if(response == 5){
					alert($("#file_already_exist").val());
				}					
			}
		}
	});
}

//deletes a section picture
function delete_template_files(index,file_type, old, template_name)
{
		if(index){
			$.ajax({
				url: '/core/template_template/deletefile',
				type: "post",
				data: ({
						file: function(){
							return index;
						},
						type: file_type+'_'+$("#template_folder").val(),
						type_saved: file_type,
						saved: old,
						template: template_name,
						template_id: $("#template_id").val()
					}),
					success: function(data) {
						
					}
			});
		}		

}