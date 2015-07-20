$(document).ready(function(){
	//switch types of content
	switch( $("#content_type_id").val() ){
	case '1': //Content Text
		
		//Validation
		$("#frmContent").validate({
			wrapper: "span",
			onfocusout: false,
			onkeyup: false,			
            rules: {
            	internal_name:{
					remote:{//check if internal name already exist
						url: "/core/content_content/checkinternalname",
						type: "POST",
						async: false,
						data:{
							content_id: $('#content_id').val()
						}
					},            		
					required:true

				},
				hdn_content:{
					required:true
				}
            },
            messages:{
            	internal_name:{//message for remote validation
            		remote: $("#repeat_content").val()
            	}
            }
		});	
		
		//copying title into internal name
		$('#title').keyup(function(){
			$('#internal_name').val($(this).val());
		});
	
		//ckeditor
		$('textarea').expandingTextArea();
		
		if (CKEDITOR.instances['content']) {
			CKEDITOR.remove(CKEDITOR.instances['content']);
		}
		
		$('#content').ckeditor({ 
			toolbar :		
				[					
					{ name: 'document', items : [ 'Source','-','Save','NewPage','DocProps','Preview','-','Templates' ] },
					{ name: 'clipboard', items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
					{ name: 'editing', items : [ 'Find','Replace','-','SelectAll'] },
					{ name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat' ] },
					{ name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv',
					'-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BidiLtr','BidiRtl' ] },
					{ name: 'links', items : [ 'Link','Unlink' ] },
					{ name: 'insert', items : [ 'HorizontalRule','SpecialChar','PageBreak','Image','Table' ] },
					{ name: 'styles', items : [ 'Styles','Format','Font','FontSize' ] },
					{ name: 'colors', items : [ 'TextColor','BGColor' ] },
					{ name: 'tools', items : [ 'Maximize', 'ShowBlocks','-','About' ] }
				],
				filebrowserBrowseUrl: '/js/ckeditor/ckfinder/ckfinder.html',
				filebrowserImageBrowseUrl: '/js/ckeditor/ckfinder/ckfinder.html?Type=Images',
				filebrowserFlashBrowseUrl: '/js/ckeditor/ckfinder/ckfinder.html?Type=Flash',
				filebrowserUploadUrl: '/js/ckeditor/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
				filebrowserImageUploadUrl: '/js/ckeditor/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images',
				filebrowserFlashUploadUrl: '/js/ckeditor/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash'
			},function(){
				//auto height content columns
				autoHeightContent();
				// resize tree height according content
				setSectionTreeHeight();	
			});	

		//call ckeditor_styles.js file to fill the dropdown styles on ckeditor
//		$.getScript('/js/external/ckeditor_styles.js',function(data){
//			if(data){
//				CKEDITOR.config.stylesSet = 'external:/js/external/ckeditor_styles.js';
//			}
//		});
				
		//save content through ajax
		$('#save').bind('click', function() {
			//copy content to hidden only for validation
			$("#hdn_content").val($('#content').val());
			//check if form is valid
			if($("#frmContent").valid()){
				//ajax save
				$.ajax({
					type: 'POST',
					async: false,
					url: '/core/content_content/save',
					dataType: 'json',
					data: 	$( "#frmContent" ).serialize(),
					success: function(data) {
						//check hidden 
						if($("#section_details").val()!=''){
							//explode value to catch serial section and if it is article
							var params= $("#section_details").val().split('/');
							if(params[0])
							{
								//no article
								if(params[1]=='no'){										
									//load section details with serial section just for simulate redirect
									$('#cms_container').load("/core/section_section/sectiondetails", {
										id : parseInt(params[0]),
										is_section_temp : parseInt($('#section_temp').val())
									}, function() {
										// resize tree height according content
										setSectionTreeHeight();
										//scroll top
										$( 'html, body' ).animate( {scrollTop: 0}, 0 );	
										setTimeout("resize_content_list()",100);
										$.getScript('/js/modules/core/section/sectionlist.js');
										$.getScript('/js/modules/core/section/sectiondetails.js');
										$.getScript('/js/modules/core/article/articledetails.js');
									});							
								}else
								//article	
								if(params[1]=='yes'){
									//load section details with serial section just for simulate redirect
									$('#cms_container').load("/core/article_article/articledetails", {
										id: parseInt(params[0])
									},function(){	
										// resize tree height according content
										setSectionTreeHeight();
										//scroll top
										$( 'html, body' ).animate( {scrollTop: 0}, 0 );	
									    setTimeout("resize_content_list()",100);
										$.getScript('/js/modules/core/section/sectionlist.js');
										$.getScript('/js/modules/core/section/sectiondetails.js');
										$.getScript('/js/modules/core/article/articledetails.js');
									});							
								}								
							}	
							else
							{
								$.ajax({
									type: 'POST',
									async: false,
									url: '/core/section_section/sectionstreedata',
									dataType: 'html',
									success: function(data) {										
									}
								});
								
								$('#cms_container').load("/core/section_section/sectionlist", {
									
								},function(){						
									$.getScript('/js/modules/core/section/sectionlist.js');
									setSectionTreeHeight();
									$( 'html, body' ).animate( {scrollTop: 0}, 0 );
								});
							}
						}else{//only if an error exist redirect to index
							$('#cms_container').load("/core/content_content/index", {
								
							},function(){	
								setSectionTreeHeight();
								$.getScript('/js/modules/core/content/index.js');
							});
						}
					}								
				});
			}				
		});	
		
		break;
		
	case '2'://Content Image
	
		//check if browser does have flash player 
		var flashPlayer = function(d,c){try{c=new ActiveXObject(d+c+"."+d+c).GetVariable("$version")}catch(f){c=navigator.plugins[d+" "+c];c=c?c.description:""}return c.match(/\s\d+/)}("Shockwave","Flash");

		if(flashPlayer) {
			
			//set value on radio aux validation
			$("#target-"+$("#target").val()).attr("class",'btn btn-primary active');
			$("input[id^='target']").bind("click",function(){
				$("#target").val($(this).attr('element_value'));
			});
	
			$("#resizeimg-"+$("#resizeimg").val()).attr("class",'btn btn-primary active');
			//set value on radio aux validation
			$("input[id^='resizeimg']").bind("click",function(){
				$("#resizeimg").val($(this).attr('element_value'));
			});
                        $("#watermarkimg-"+$("#watermarkimg").val()).attr("class",'btn btn-primary active');
			//set value on radio aux validation
			$("input[id^='watermarkimg']").bind("click",function(){
				$("#watermarkimg").val($(this).attr('element_value'));
			});
                        $("#zoom-"+$("#zoom").val()).attr("class",'btn btn-primary active');
			//set value on radio aux validation
			$("input[id^='zoom']").bind("click",function(){
				$("#zoom").val($(this).attr('element_value'));
			});
			//validation
			$("#frmContent").validate({
				wrapper: "span",
				onfocusout: false,
				onkeyup: false,			
	            rules: {
	            	internal_name:{
						required:true,
						remote:{//valid if internal name already exist
							url: "/core/content_content/checkinternalname",
							type: "POST",
							async: false,
							data:{
								content_id: $('#content_id').val()
							}
						}
					},
					description:{
						required:false
					},
					agileUploaderFileInputText:{
						accept: "jpg,png,gif,jpeg"
					},				
					format:{
						required:true
					}
	            },
	            messages:{
	            	internal_name:{//message for remote validation
	            		remote: $("#repeat_content").val()
	            	}
	            }
			});	
			
			//copying title into internal name
			$('#title').keyup(function(){
				$('#internal_name').val($(this).val());
			});
			
			//auto height content columns
			autoHeightContent();
			// resize tree height according content
			setSectionTreeHeight();	
			
			//call upload plugin
			$('#single_img').agileUploaderSingle({
	    		submitRedirect: 'redirect_to_content/'+$("#section_details").val()+'/'+parseInt($('#section_temp').val()),//this is a custom redirect
	    		formId: 'frmContent',
	    		progressBarColor: '#00ff00',
	    		flashVars: {
					firebug: true,
					file_limit: 1,
					max_width: parseInt($("#hdn_max_width_img").val()),
					max_height : parseInt($("#hdn_max_height_img").val()),
		    		form_action: '/core/content_content/save',
		    		preview_jpg_quality: 100,
		    		preview_max_height: 200,
		    		preview_max_width: 200,
		    		resize: 'jpg,jpeg,gif,png'
	    		}	
			}); 
			
			//save content through flash
			$("#save_image").bind("click",function(){
				if($("#frmContent").valid()){
					document.getElementById('agileUploaderSWF').submit();				
				}
	
			});
		} else {
			$("#frmContent").empty();
			$("#frmContent").hide();
			$("#div_preview").hide();
			$("#no_flash_player").show();
		  }
                  //default watermark pos
                if($('#watermark_position').val()!=''){
                        $("[id^='wmk_pos_']").each(function(){
                                if($(this).attr('pos')==$('#watermark_position').val()){
                                        $(this).addClass('btn-success');
                                        $('#img_watermark').parent('div').removeClass('hide');
                                        setWatermarkPos();
                                        $(this).click();
                                }
                        });
                }
		break;
	
	
	case '3': //Content link
		
		////set value on radio aux validation
		$("#type-"+$("#type").val()).attr("class",'btn btn-primary active');
		$("input[id^='type']").bind("click",function(){
			$("#type").val($(this).attr('element_value'));
		});				
//		
//		//validation
		$("#frmContent").validate({
			wrapper: "span",
			onfocusout: false,
			onkeyup: false,			
            rules: {
            	internal_name:{
					required:true,
					remote:{//check if internal name already exist
						url: "/core/content_content/checkinternalname",
						type: "POST",
						async: false,
						data:{
							content_id: $('#content_id').val()
						}
					}
				},
				description:{
					required:false
				},
				aux_value_selected:{
					required:true
				}
            },
            messages:{
            	internal_name:{ //message for remote validation
            		remote: $("#repeat_content").val()
            	}
            }
		});
//		
//		//copying title into internal name
		$('#title').keyup(function(){
			$('#internal_name').val($(this).val());
		});
//		
//		// if click on internal link option
		$("#type-internal_link").bind("click",function(){
			//hide other options elements
			hide_elements_link();
			
			//show internal link elementes
			$("[id^=internal_section]").show();
			$("[id^=internal_section] label").show();
			
			//modify rules on internal link elements
			$("#internal_section").attr('disabled',false);
			$("#internal_section").rules("add",{
				required:true
			});
			//auto height containers
			autoHeightContent();
			// resize tree height according content
			setSectionTreeHeight();	
		});
//		
//		// if click on external link option
		$("#type-external_link").bind("click",function(){
			//hide other options elements
			hide_elements_link();
			
			//show external link elements
			$("[id^=link]").show();
			$("[id^=link] label").show();
			
			//modify rules on external link elements
			$("#link").attr('disabled',false);
			$("#link").rules("add",{
				required:true
			});		
			//auto height containers
			autoHeightContent();
			// resize tree height according content
			setSectionTreeHeight();	
		});
//		// if click on email option
		$("#type-e_mail").bind("click",function(){
			//hide other options elements
			hide_elements_link();
			
			//show email elements
			$("[id^=email]").show();
			$("[id^=email] label").show();
			
			//modify rules on email elements
			$("#email").attr('disabled',false);
			$("#email").rules("add",{
				required:true,
				email:true
			});	
			//auto height containers
			autoHeightContent();
			// resize tree height according content
			setSectionTreeHeight();	
		});
//		
		$("#type-file").bind("click",function(){
			//hide other options elements
			hide_elements_link();
			
			//show file elements
			$("[id^='file']").show();
			$("[id^='file'] label").show();
			$("[id^=file_type]").show();
			$("[id^=file_type] label").show();
			
			// modify rules on file elements
			$("#file_type").attr('disabled',false);
			$("#hdnNameFile_file").attr('disabled',false);
			$("#file_type").rules("add",{
				required:{
					depends: function(){
						if($("#hdnNameFile_file").val()!='')
							return true;
						else
							return false;
					}
				}
			});	
			
			$("#hdnNameFile_file").rules("add", {	
				required:true
				
			});
//			
			$("#input_file_file").show();
			// add ajax upload image
			load_file('file','file');

			//auto height containers
			autoHeightContent();
			// resize tree height according content
			setSectionTreeHeight();	
		});
//		
//		// if internal link option is active
		if($("#type-internal_link").hasClass('active'))
		{
			//hide other options elements
			hide_elements_link();
			
			//show internal link elementes
			$("[id^=internal_section]").show();
			$("[id^=internal_section] label").show();
			
			//modify rules on internal link elements
			$("#internal_section").attr('disabled',false);
			$("#internal_section").rules("add",{
				required:true
			});
			//auto height containers
			autoHeightContent();
			// resize tree height according content
			setSectionTreeHeight();	
		}
//		// if external link option is active
		if($("#type-external_link").hasClass('active')){
			//hide other options elements
			hide_elements_link();
			//show external link elements
			$("[id^=link]").show();
			$("[id^=link] label").show();
			//modify rules on external link elements
			$("#link").attr('disabled',false);
			$("#link").rules("add",{
				required:true
			});					
			//auto height containers
			autoHeightContent();
			// resize tree height according content
			setSectionTreeHeight();	
		};
//		
		if($("#type-e_mail").hasClass('active')){
			//hide other options elements
			hide_elements_link();
			//show email elements
			$("[id^=email]").show();
			$("[id^=email] label").show();
			//modify rules on email elements
			$("#email").attr('disabled',false);
			$("#email").rules("add",{
				required:true
			});	
			//auto height containers
			autoHeightContent();
			// resize tree height according content
			setSectionTreeHeight();	
		};
//		
		//if($("#type-file").hasClass('active')){
//			
//			//hide other options elements
//			hide_elements_link();
//			//show file elements
//			$("[+id^='file']").show();
//			$("[id^='file'] label").show();
//			$("[id^=file_type]").show();
//			$("[id^=file_type] label").show();
//			
//			$("#input_file_file").val($("#hdnNameFile_file").val());
//			// modify rules on file elements
//			$("#file_type").attr('disabled',false);
//			$("#hdnNameFile_file").attr('disabled',false);			
//			$("#file_type").rules("add",{
//				required:true
//			});	
//			
//			$("#hdnNameFile_file").rules("add", {	
//				required:true
//				
//			});
//			
//			$("#input_file_file").show();
//			
//			load_file('file','file');
//			//auto height containers
//			autoHeightContent();
//			// resize tree height according content
//			setSectionTreeHeight();	
//		};			
//
//		//save content
		$('#save').bind('click', function() {
			if($("#frmContent").valid()){
				
				if($("#type-internal_link").hasClass('active'))
				{
					$("#link").val(' ');
					$("#email").val(' ');
					$("#hdnNameFile_file").val(' ');
					$("#file_type").val(' ');
					
					$("#link").attr('disabled',false);
					$("#email").attr('disabled',false);
					$("#hdnNameFile_file").attr('disabled',false);
					$("#file_type").attr('disabled',false);
					
					delete_file('file');
					
				}
				if($("#type-external_link").hasClass('active')){					
					$("#internal_section").val(' ');
					$("#email").val(' ');
					$("#hdnNameFile_file").val(' ');
					$("#file_type").val(' ');
					
					$("#internal_section").attr('disabled',false);
					$("#email").attr('disabled',false);
					$("#hdnNameFile_file").attr('disabled',false);
					$("#file_type").attr('disabled',false);		
					
					delete_file('file');									
				};
				
				if($("#type-e_mail").hasClass('active')){					
					$("#internal_section").val(' ');
					$("#link").val(' ');
					$("#hdnNameFile_file").val(' ');
					$("#file_type").val(' ');
					
					$("#internal_section").attr('disabled',false);
					$("#link").attr('disabled',false);
					$("#hdnNameFile_file").attr('disabled',false);
					$("#file_type").attr('disabled',false);		
					
					delete_file('file');					
				};
				
				if($("#type-file").hasClass('active')){					
					$("#internal_section").val(' ');
					$("#link").val(' ');
					$("#email").val(' ');
					
					$("#internal_section").attr('disabled',false);
					$("#link").attr('disabled',false);
					$("#email").attr('disabled',false);
				};			
				// ajax save
				$.ajax({
					type: 'POST',
					async: false,
					url: '/core/content_content/save',
					dataType: 'json',
					data: 	$( "#frmContent" ).serialize(),
					success: function(data) {	
						//check if data for redirect exist
						if($("#section_details").val()!=''){
							//explode and get section id and if it is article
							var params= $("#section_details").val().split('/');
							if(params[0])
							{
								//no article
								if(params[1]=='no'){
									//load section detail just for simulate a redirect
									$('#cms_container').load("/core/section_section/sectiondetails", {
										id : parseInt(params[0]),
										is_section_temp : parseInt($('#section_temp').val())
									}, function() {
										// resize tree height according content
										setSectionTreeHeight();
										//scroll top
										$( 'html, body' ).animate( {scrollTop: 0}, 0 );	
										setTimeout("resize_content_list()",100);
										$.getScript('/js/modules/core/section/sectionlist.js');
										$.getScript('/js/modules/core/section/sectiondetails.js');
										$.getScript('/js/modules/core/article/articledetails.js');
									});							
								}else
								//article
								if(params[1]=='yes'){
									//load section detail just for simulate a redirect
									$('#cms_container').load("/core/article_article/articledetails", {
										id: parseInt(params[0])
									},function(){				
										// resize tree height according content
										setSectionTreeHeight();
										//scroll top
										$( 'html, body' ).animate( {scrollTop: 0}, 0 );	
									    setTimeout("resize_content_list()",100);
										$.getScript('/js/modules/core/section/sectionlist.js');
										$.getScript('/js/modules/core/section/sectiondetails.js');
										$.getScript('/js/modules/core/article/articledetails.js');
									});							
								}				
								
							}
							else
							{
								$.ajax({
									type: 'POST',
									async: false,
									url: '/core/section_section/sectionstreedata',
									dataType: 'html',
									success: function(data) {										
									}
								});
								
								$('#cms_container').load("/core/section_section/sectionlist", {
									
								},function(){						
									$.getScript('/js/modules/core/section/sectionlist.js');
									setSectionTreeHeight();
									$( 'html, body' ).animate( {scrollTop: 0}, 0 );
								});
							}
						}else{
							$('#cms_container').load("/core/content_content/index", {
								
							},function(){	
								setSectionTreeHeight();
								$.getScript('/js/modules/core/content/index.js');
							});
						}
					}								
				});
			}				
		});			
		
		break;
	
	
	case '4'://Content form
		
		//set value on radio aux validation
//		$("#captcha").val($("input[id^='captcha']:checked").val());
		$("#captcha-"+$("#captcha").val()).attr("class",'btn btn-primary active');
		$("input[id^='captcha']").bind("click",function(){
			$("#captcha").val($(this).attr('element_value'));
		});
		//validate
		$("#frmContent").validate({
			wrapper: "span",
			onfocusout: false,
			onkeyup: false,			
            rules: {
            	internal_name:{
					required:true,
					remote:{  //check if internal name already exist
						url: "/core/content_content/checkinternalname",
						type: "POST",
						async: false,
						data:{
							content_id: $('#content_id').val()
						}
					}
				},
				picture_foot:{
					required:true
				},
				description:{
					required:false
				},
				captcha:{
					required:true
				},
				email:{
					required:true,
					email:true
				}
            },
            messages:{
            	internal_name:{//message for remote validation
            		remote: $("#repeat_content").val()
            	}
            }
		});		
		
		//copying title into internal name
		$('#title').keyup(function(){
			$('#internal_name').val($(this).val());
		});		
		// add fancy box feature
		$("#anchor_add").fancybox();
		var count = 0;
		// appent header of table where elements will be added
		$("#form_elements").append($("#header_table").val());
		
		//load data from database
		$.ajax({
			url: '/core/content_content/loadformelement',
			type: "post",
			dataType: 'json',
			data: {						
				content_id: $("#hdn_content_id").val()
			},
			success: function(data){
				for(var i=0; i<data.length; i++)
					{
						var arr = data[i]['options'].split(',');
						var length = arr.length-1;
						
						//append as a table style
						$("#sortable").append('<div class="row-fluid table-bordered-content even" id="element_'+i+'" element="'+count+'">' +
							'<div class="span4" id="td_name_'+i+'">'+data[i]['name']+'</div>' +
							'<div class="span2" id="td_type_'+i+'">'+data[i]['type']+'</div>' +
							'<div class="span2 handler move"><i class="icon-move"></i></div>' +
							'<div class="span3 last pointer">'+
							'	<a id="aux_edit_element_'+i+'" href="#formContent"></a><a id="edit_element_'+i+'" number="'+length+'" element="'+i+'"><i class="edit_element icon-pencil pointer"></i></a> / ' +
							'	<i id="remove_element_'+i+'" element="'+i+'" class="remove_element icon-trash pointer"></i>'+
							'</div>' +
						'</div>');
						
						// add sortable feature 
						$("#sortable").sortable(
							{
								handle: ".handler",
								axis: "y",
								cursor: "move"
							}		
						);
						$("#sortable").disableSelection();												
						
						//add elements values on hiddens 
						$("#div_hidden_elements").append("<div id='hidden_elements_"+i+"'> <input type='hidden' id='frm_name_"+i+"' name='frm_name_["+i+"]' value='"+data[i]['name']+"' />" +
								"<input type='hidden' id='frm_description_"+i+"' name='frm_description_["+i+"]' value='"+data[i]['description']+"' />" +
								"<input type='hidden' id='frm_required_"+i+"' name='frm_required_["+i+"]' value='"+data[i]['required']+"' /> " +
								"<input type='hidden' id='frm_element_type_"+i+"' name='frm_element_type_["+i+"]' value='"+data[i]['type']+"' /> " +
								"<input type='hidden' id='frm_element_weight_"+i+"' name='frm_element_weight_["+i+"]' value='0' /> </div>");						
						
						//append hidden options values on radiobutton an dropdown 
						if(length>0)
						{
							var options = 0;
							for(var j=0; j<arr.length-1; j++){
								$("#hidden_elements_"+i).append("<input type='hidden' id='hdn_frm_option_"+i+"_"+options+"' name='hdn_frm_option_["+i+"]["+options+"]' frm_element_id='frm_option_"+(options+1)+"' value='"+arr[j]+"' />"); 
								options++;
							}
						}
						//remove element click event
						$("#remove_element_"+i).bind("click",function(){
							$("#element_"+$(this).attr("element")).remove();
							$("#hidden_elements_"+$(this).attr("element")).remove();
							
							//auto height containers
							autoHeightContent();
							// resize tree height according content
							setSectionTreeHeight();	
							
							if($("#sortable").is(':empty')){
								$("#labl_no_elements").show();
								$("#elements_table, #sortable").hide();
							}
						});
						//add fancybox feature 
						$("#aux_edit_element_"+i).fancybox();
						//edit button click event
						$("#edit_element_"+i).bind("click",function(){
							var element = $(this).attr('element');
							var num = $(this).attr('number');
							//call edit function
							edit_element(element, num);
						});
						
						count++;
					}
				
				if(!$("#sortable").is(':empty')){
					$("#labl_no_elements").hide();
					$("#elements_table, #sortable").show();
					//auto height containers
					autoHeightContent();
					// resize tree height according content
					setSectionTreeHeight();	
				}
				
			}
		});
		//hide number field that is used on radio button element and select element
		$("[id^='number']").hide();
		// change event on element type
		$("#element_type").bind("change",function() {
					// check if selected element id radiobutton or dropdown 
					if ($("#element_type").val() == 'radiobutton' || $("#element_type").val() == 'dropdown') {
						//show number field and add rules
						$("[id^='number']").show();
						$("#number").rules("add",{
							required:true
						});
					} else {
						//hide number element and hide rules
						$("[id^='number']").hide();
						$("#number").rules("remove");
					}
				});
		//click even on add button
		$("#add").bind("click", function() {
			//add rules on dropdown element type
			$("#element_type").rules("add",{
				required:true
			})
			//valid if element type and number is set
			$("#element_type").valid();
			$("#number").valid();			
			
			$("#alert").hide();
			//if element is radiobutton or dropdown 
			if ($("#element_type").val() == 'radiobutton' || $("#element_type").val() == 'dropdown') {
				if($("#number").val() != ''  ){
					//load on fancy box a form for insert a new element
					$("#formContent").load("/core/content_content/loadformcontent",{
						element_type:function(){
							return $("#element_type").val();
							},
						number:function(){
							return $("#number").val();
							}	
					}, function(){
						//validate elements form
						$("#elements_form").validate({
							wrapper: "span",
							onfocusout: false,
							onkeyup: false,
							
				            rules: {
								frm_name:{
									required:true
								},
								frm_description:{
									required:false
								}
				            }

						});										
						//add rules on form element
						$("[id^='frm_option_']").each(function(){
							$(this).rules("add",{
								required:true
							});
						});	
						//simulate anchor click to display fancybox
						$("#anchor_add").click();
						$("#btn_add").bind("click",function(){
							$("#alert").hide();
							//validate elements form
							if($("#elements_form").valid())
							{
								//get values
								var form_name = $("#frm_name").val();
								var form_description = $("#frm_description").val();
								var form_required;
								$('[name^=frm_required]').each(function(){
									if($(this).hasClass('active')){
										form_required = $(this).attr('element_value');
									}
								});
								var form_element_type = $("#element_type").val();
								
								var equal_name=0;
								$("[id^='frm_name_']").each(function(){
									if(form_name == $(this).val())
										equal_name=1;
								});
								
								if(equal_name==0)
									{
									//append as a table style
									$("#sortable").append('<div class="row-fluid table-bordered-content even" id="element_'+count+'" element="'+count+'">' +
											'<div class="span4" id="td_name_'+count+'">'+form_name+'</div>' +
											'<div class="span2" id="td_type_'+count+'">'+$("#element_type").val()+'</div>' +
											'<div class="span2 handler move"><i class="icon-move"></i></div>' +
											'<div class="span3 last pointer">' +
											'	<a id="aux_edit_element_'+count+'" href="#formContent"></a> <a id="edit_element_'+count+'" number="'+$("#number").val()+'" element="'+count+'"><i class="edit_element icon-pencil pointer" ></i></a> / ' +
											'	<img id="remove_element_'+count+'" element="'+count+'" class="remove_element icon-trash pointer" ></i>' +
											'</div>' +
									'</div>');
									// add sortable feature 
									$("#sortable").sortable(
										{
											handle: ".handler",
											axis: "y",
											cursor: "move"
										}		
									);
									$("#sortable").disableSelection();												
									//add elements values on hiddens 
									$("#div_hidden_elements").append("<div id='hidden_elements_"+count+"'> <input type='hidden' id='frm_name_"+count+"' name='frm_name_["+count+"]' value='"+form_name+"' />" +
											"<input type='hidden' id='frm_description_"+count+"' name='frm_description_["+count+"]' value='"+form_description+"' />" +
											"<input type='hidden' id='frm_required_"+count+"' name='frm_required_["+count+"]' value='"+form_required+"' /> " +
											"<input type='hidden' id='frm_element_type_"+count+"' name='frm_element_type_["+count+"]' value='"+form_element_type+"' /> " +
											"<input type='hidden' id='frm_element_weight_"+count+"' name='frm_element_weight_["+count+"]' value='0' /> </div>");
									
										var options = 0;
										//append hidden options values on radiobutton an dropdown 
										$("[id^='frm_option_']").each(function(){
											$("#hidden_elements_"+count).append("<input type='hidden' id='hdn_frm_option_"+count+"_"+options+"' name='hdn_frm_option_["+count+"]["+options+"]' frm_element_id='"+$(this).attr('id')+"' value='"+$(this).val()+"' />"); 
											options++;
										});
										
										//auto height containers
										autoHeightContent();
										// resize tree height according content
										setSectionTreeHeight();	
										
										//remove element click event
										$("#remove_element_"+count).bind("click",function(){
											$("#element_"+$(this).attr("element")).remove();
											$("#hidden_elements_"+$(this).attr("element")).remove();
											
											//auto height containers
											autoHeightContent();
											// resize tree height according content
											setSectionTreeHeight();	
											
											if($("#sortable").is(':empty')){
												$("#labl_no_elements").show();
												$("#elements_table, #sortable").hide();
											}
										});
										//add fancybox feature 
										$("#aux_edit_element_"+count).fancybox();
										//edit button click event
										$("#edit_element_"+count).bind("click",function(){
											$("#repeat_frm_name").hide();
											var element = $(this).attr('element');
											//call edit function
											edit_element(element, 1);
										});												
										
										count++;
										//show elements
										$("#labl_no_elements").hide();
										$("#elements_table, #sortable").show();
										//close fancybox
										$.fancybox.close();
										//remove rules
										$("#element_type").rules("remove");
										$("#number").rules("remove");										
									}
								else{// show message if name already exist
									$("#repeat_frm_name").show();
								}
							}
						});
					});
				}								
			}else
				if($("#element_type").val() != ''){
					//load on fancy box a form for insert a new element
					$("#formContent").load("/core/content_content/loadformcontent",{
						element_type:function(){
							return $("#element_type").val();
						},
						number:function(){
							return $("#number").val();
							}
					}, function(){
						//validate element form
						$("#elements_form").validate({
							wrapper: "span",
							onfocusout: false,
							onkeyup: false,
							
				            rules: {
								frm_name:{
									required:true
								},
								frm_description:{
									required:false
								}

				            }

						});										
						//simulate anchor click to display fancybox
						$("#anchor_add").click();
						//click event on form element type save button
						$("#btn_add").bind("click",function(){
							$("#repeat_frm_name").hide();
							//validate elements form
							if($("#elements_form").valid())
							{
								//get values
								var form_name = $("#frm_name").val();
								var form_description = $("#frm_description").val();
								var form_required;
								$('[name^=frm_required]').each(function(){
									if($(this).hasClass('active')){
										form_required = $(this).attr('element_value');
									}
								});
								var form_element_type = $("#element_type").val();
								var equal_name=0;
								$("[id^='frm_name_']").each(function(){
									if(form_name == $(this).val())
										equal_name=1;
								});
								
								if(equal_name==0)
									{			
									//append as a table style
									$("#sortable").append('<div class="row-fluid table-bordered-content even" id="element_'+count+'" element="'+count+'">' +
											'<div class="span4" id="td_name_'+count+'">'+form_name+'</div>'+
											'<div class="span2" id="td_type_'+count+'">'+$("#element_type").val()+'</div>'+
											'<div class="span2 handler move"><i class="icon-move"></i></div>' +
											'<div class="span3 last">' +
											'	<a id="aux_edit_element_'+count+'" href="#formContent"></a> <a id="edit_element_'+count+'" number="'+$("#number").val()+'" element="'+count+'"><i class="edit_element icon-pencil pointer" ></i></a> / ' +
											'	<img id="remove_element_'+count+'" element="'+count+'" class="remove_element icon-trash pointer" ></i>' +
											'</div>' +
									'</div>');												
									
									// add sortable feature 
									$("#sortable").sortable(
										{
											handle: ".handler",
											axis: "y",
											cursor: "move"
										}		
									);
									$("#sortable").disableSelection();
									
									//add elements values on hiddens 
									$("#div_hidden_elements").append("<div id='hidden_elements_"+count+"'> <input type='hidden' id='frm_name_"+count+"' name='frm_name_["+count+"]' value='"+form_name+"' />" +
											"<input type='hidden' id='frm_description_"+count+"' name='frm_description_["+count+"]' value='"+form_description+"' />" +
											"<input type='hidden' id='frm_required_"+count+"' name='frm_required_["+count+"]' value='"+form_required+"' />"  +
											"<input type='hidden' id='frm_element_type_"+count+"' name='frm_element_type_["+count+"]' value='"+form_element_type+"' /> " +
											"<input type='hidden' id='frm_element_weight_"+count+"' name='frm_element_weight_["+count+"]' value='0' /> </div>");
										
									//auto height containers
									autoHeightContent();
									// resize tree height according content
									setSectionTreeHeight();	
									
									//remove element click event
										$("#remove_element_"+count).bind("click",function(){
											$("#element_"+$(this).attr("element")).remove();
											$("#hidden_elements_"+$(this).attr("element")).remove();
											
											//auto height containers
											autoHeightContent();
											// resize tree height according content
											setSectionTreeHeight();	
											
											if($("#sortable").is(':empty')){
												$("#labl_no_elements").show();
												$("#elements_table, #sortable").hide();
											}
										});
										//add fancybox feature 
										$("#aux_edit_element_"+count).fancybox();
										//edit button click event
										$("#edit_element_"+count).bind("click",function(){
											$("#repeat_frm_name").hide();
											var element = $(this).attr('element');
											edit_element(element, 0);							
										});
										
										count++;
										//show elements
										$("#labl_no_elements").hide();
										$("#elements_table, #sortable").show();
										//close fancy box
										$.fancybox.close();
										//remove rules
										$("#element_type").rules("remove");
										$("#number").rules("remove");										
									
									}else{// show message if name already exist
										$("#repeat_frm_name").show();
									}												
							}
						});
					});								
				}
		});
		
		// save through ajax
		$("#save_form").bind("click", function(){
			//save order elements
			if($("#sortable").is(':empty')===false){ 
				var section_list = $("#sortable").sortable("toArray");		
				for(var i=0; i<section_list.length;i++)
					{
						$("#frm_element_weight_"+$("#"+section_list[i]).attr('element')).val(i);
					}	
			}
			//validate
			if($("#frmContent").valid()){
				$.ajax({
					type: 'POST',
					async: false,
					url: '/core/content_content/save',
					dataType: 'json',
					data: 	$( "#frmContent" ).serialize(),
					success: function(data) {	
						//get hidden values
						if($("#section_details").val()!=''){
							//explode hidden values to get section id and article
							var params= $("#section_details").val().split('/');
							if(params[0])
							{
								//no article
								if(params[1]=='no'){
									//load section details just for redirect
									$('#cms_container').load("/core/section_section/sectiondetails", {
										id : parseInt(params[0]),
										is_section_temp : $('#section_temp').val()
									}, function() {
										// resize tree height according content
										setSectionTreeHeight();
										//scroll top
										$( 'html, body' ).animate( {scrollTop: 0}, 0 );	
										setTimeout("resize_content_list()",100);
										$.getScript('/js/modules/core/section/sectionlist.js');
										$.getScript('/js/modules/core/section/sectiondetails.js');
										$.getScript('/js/modules/core/article/articledetails.js');
									});							
								}else
								//article
								if(params[1]=='yes'){
									//load section details just for redirect
									$('#cms_container').load("/core/article_article/articledetails", {
										id: parseInt(params[0])
									},function(){	
										// resize tree height according content
										setSectionTreeHeight();
										//scroll top
										$( 'html, body' ).animate( {scrollTop: 0}, 0 );	
									    setTimeout("resize_content_list()",100);
										$.getScript('/js/modules/core/section/sectionlist.js');
										$.getScript('/js/modules/core/section/sectiondetails.js');
										$.getScript('/js/modules/core/article/articledetails.js');
									});							
								}
							}
							else
							{
								$.ajax({
									type: 'POST',
									async: false,
									url: '/core/section_section/sectionstreedata',
									dataType: 'html',
									success: function(data) {										
									}
								});
								
								$('#cms_container').load("/core/section_section/sectionlist", {
									
								},function(){						
									$.getScript('/js/modules/core/section/sectionlist.js');
									setSectionTreeHeight();
									$( 'html, body' ).animate( {scrollTop: 0}, 0 );
								});
							}
						}else{// load index if error exist
							$('#cms_container').load("/core/content_content/index", {
								
							},function(){	
								// resize tree height accordin content
								setSectionTreeHeight();
								$.getScript('/js/modules/core/content/index.js');
							});
						}
					}								
				});
			}
		});
		
		break;
		
	case '5': //Content flash
		
		//set value on radio aux validation
		$("#background-"+$("#background").val()).attr("class",'btn btn-primary active');
		$("input[id^='background']").bind("click",function(){
			$("#background").val($(this).attr('element_value'));
		});			
		
		//auto height containers
		autoHeightContent();
		//validation
		$("#frmContent").validate({
			wrapper: "span",
			onfocusout: false,
			onkeyup: false,			
            rules: {
            	internal_name:{
					required:true,
					remote:{//check if internal name already exist
						url: "/core/content_content/checkinternalname",
						type: "POST",
						async: false,
						data:{
							content_id: $('#content_id').val()
						}
					}
				},
				description:{
					required:true
				},
				background:{
					required:true
				}
            },
            messages:{
            	internal_name:{ //remote validation message
            		remote: $("#repeat_content").val()
            	}
            }
		});	
		
		//copying title into internal name
		$('#title').keyup(function(){
			$('#internal_name').val($(this).val());
		});
		//show elements
		$("#input_file_flash_file").show();
		$("#input_file_alternative_image").show();
		
		$("#input_file_flash_file").val($("#hdnNameFile_flash_file").val());
		$("#input_file_alternative_image").val($("#hdnNameFile_alternative_image").val());
		
		$("#flash_file").show();
		$("#alternative_image").show();
		
		//add rules
		$("#hdnNameFile_flash_file").rules("add",{
			required:true,
			accept: 'swf'
		});
		
		$("#hdnNameFile_alternative_image").rules("add",{
			accept: 'jpg,jpeg,png,gif'
		});			
		
		//add ajax upload
		load_file('flash_file', 'flash');
		load_file('alternative_image', 'image');		
		
		//resize tree height according content
		setSectionTreeHeight();		
		//auto height containers
		autoHeightContent();
		
		//save through ajax
		$('#save').bind('click', function() {
			//check if form is valid
			if($("#frmContent").valid()){
				$.ajax({
					type: 'POST',
					async: false,
					url: '/core/content_content/save',
					dataType: 'json',
					data: 	$( "#frmContent" ).serialize(),
					success: function(data) {	
						//get hidden values
						if($("#section_details").val()!=''){
							//explode hidden values to get section id and article
							var params= $("#section_details").val().split('/');
							if(params[0])
							{
								//no article
								if(params[1]=='no'){
									//load section details just for redirect
									$('#cms_container').load("/core/section_section/sectiondetails", {
										id : parseInt(params[0]),
										is_section_temp : $('#section_temp').val()
									}, function() {
										// resize tree height according content
										setSectionTreeHeight();
										//scroll top
										$( 'html, body' ).animate( {scrollTop: 0}, 0 );	
										setTimeout("resize_content_list()",100);
										$.getScript('/js/modules/core/section/sectionlist.js');
										$.getScript('/js/modules/core/section/sectiondetails.js');
										$.getScript('/js/modules/core/article/articledetails.js');
									});							
								}else
								//article
								if(params[1]=='yes'){
									//load section details just for redirect
									$('#cms_container').load("/core/article_article/articledetails", {
										id: parseInt(params[0])
									},function(){	
										// resize tree height according content
										setSectionTreeHeight();
										//scroll top
										$( 'html, body' ).animate( {scrollTop: 0}, 0 );	
									    setTimeout("resize_content_list()",100);
										$.getScript('/js/modules/core/section/sectionlist.js');
										$.getScript('/js/modules/core/section/sectiondetails.js');
										$.getScript('/js/modules/core/article/articledetails.js');
									});							
								}				
								
							}
							else
							{
								$.ajax({
									type: 'POST',
									async: false,
									url: '/core/section_section/sectionstreedata',
									dataType: 'html',
									success: function(data) {										
									}
								});
								
								$('#cms_container').load("/core/section_section/sectionlist", {
									
								},function(){						
									$.getScript('/js/modules/core/section/sectionlist.js');
									setSectionTreeHeight();
									$( 'html, body' ).animate( {scrollTop: 0}, 0 );
								});
							}
						}else{// load index if error exist
							$('#cms_container').load("/core/content_content/index", {
								
							},function(){	
								// resize tree height accordin content
								setSectionTreeHeight();
								$.getScript('/js/modules/core/content/index.js');
							});
						}
					}								
				});
			}				
		});			
		
		break;
		
	case '6': //Content video flash
		
		//validate
		$("#frmContent").validate({
			wrapper: "span",
			onfocusout: false,
			onkeyup: false,			
            rules: {
            	internal_name:{
					required:true,
					remote:{//check if internal name already exist
						url: "/core/content_content/checkinternalname",
						type: "POST",
						async: false,
						data:{
							content_id: $('#content_id').val()
						}
					}
				},
				url:{
					youtubeUrl:true
				}
            },
            messages:{
            	internal_name:{//remote message
            		remote: $("#repeat_content").val()
            	}
            }
		});
		
		//copying title into internal name
		$('#title').keyup(function(){
			$('#internal_name').val($(this).val());
		});
		
		if($("#url").val()!=''){
			//get youtube info from url 
			getYouTubeInfo($("#url").val());
		}
		
		$("#url").bind("keyup",function(){
			if($(this).val()!=''){
				$("#html_code").val('');
				//get youtube info from url 
				getYouTubeInfo($("#url").val());	
			}
		});
		
		$("#html_code").bind("keyup",function(){
			if($(this).val()!=''){
				$("#url").val('');
				parseresults(null);
			}
		});		
		
		//save content through ajax
		$('#save').bind('click', function() {
			//check if form is valid
			if($("#frmContent").valid()){
				//ajax save
				$.ajax({
					type: 'POST',
					async: false,
					url: '/core/content_content/save',
					dataType: 'json',
					data: 	$( "#frmContent" ).serialize(),
					success: function(data) {	
						//get hidden values
						if($("#section_details").val()!=''){
							//explode hidden values to get section id and article
							var params= $("#section_details").val().split('/');
							if(params[0])
							{
								//no article
								if(params[1]=='no'){
									//load section details just for redirect
									$('#cms_container').load("/core/section_section/sectiondetails", {
										id : parseInt(params[0]),
										is_section_temp : $('#section_temp').val()
									}, function() {
										// resize tree height according content
										setSectionTreeHeight();
										//scroll top
										$( 'html, body' ).animate( {scrollTop: 0}, 0 );	
										setTimeout("resize_content_list()",100);
										$.getScript('/js/modules/core/section/sectionlist.js');
										$.getScript('/js/modules/core/section/sectiondetails.js');
										$.getScript('/js/modules/core/article/articledetails.js');
									});							
								}else
								//article
								if(params[1]=='yes'){
									//load section details just for redirect
									$('#cms_container').load("/core/article_article/articledetails", {
										id: parseInt(params[0])
									},function(){	
										// resize tree height according content
										setSectionTreeHeight();
										//scroll top
										$( 'html, body' ).animate( {scrollTop: 0}, 0 );	
									    setTimeout("resize_content_list()",100);
										$.getScript('/js/modules/core/section/sectionlist.js');
										$.getScript('/js/modules/core/section/sectiondetails.js');
										$.getScript('/js/modules/core/article/articledetails.js');
									});							
								}					
								
							}
							else
							{
								$.ajax({
									type: 'POST',
									async: false,
									url: '/core/section_section/sectionstreedata',
									dataType: 'html',
									success: function(data) {										
									}
								});
								
								$('#cms_container').load("/core/section_section/sectionlist", {
									
								},function(){						
									$.getScript('/js/modules/core/section/sectionlist.js');
									setSectionTreeHeight();
									$( 'html, body' ).animate( {scrollTop: 0}, 0 );
								});
							}
						}else{// load index if error exist
							$('#cms_container').load("/core/content_content/index", {
								
							},function(){	
								// resize tree height accordin content
								setSectionTreeHeight();
								$.getScript('/js/modules/core/content/index.js');
							});
						}
					}								
				});
			}				
		});	
		break;
		
	case '7'://Content carrusel

                 $( "#sortable" ).sortable();  
                 $( "#deleted_array" ).sortable();
                 $( "#sortable" ).disableSelection();                
        
		
		//check if browser does have flash player 
		var flashPlayer = function(d,c){try{c=new ActiveXObject(d+c+"."+d+c).GetVariable("$version")}catch(f){c=navigator.plugins[d+" "+c];c=c?c.description:""}return c.match(/\s\d+/)}("Shockwave","Flash");

		if(flashPlayer) {
			$("[id^='myCarousel_']").each(function(){
				$(this).carousel({
					interval : 2000
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
			
			//validate
			$("#frmContent").validate({
				wrapper: "span",
				onfocusout: false,
				onkeyup: false,			
	            rules: {
	            	internal_name:{
						required:true,
						remote:{//check if internal name already exist
							url: "/core/content_content/checkinternalname",
							type: "POST",
							async:false,
							data:{
								content_id: $('#content_id').val()
							}
						}
					}
	            },
	            messages:{
	            	internal_name:{// remote message
	            		remote: $("#repeat_content").val()
	            	}
	            }
			});			
			
			//copying title into internal name
			$('#title').keyup(function(){
				$('#internal_name').val($(this).val());
			});
			//add multiupload plugin 
			$('#multiple_img').agileUploader({
	    		submitRedirect: 'redirect_to_content/'+$("#section_details").val()+'/'+parseInt($('#section_temp').val()),//this is a custom redirect
	    		formId: 'frmContent',
	    		flashVars: {
					firebug: true,
					file_limit: 10,
					max_width: parseInt($("#hdn_max_width_img").val()),
					max_height : parseInt($("#hdn_max_height_img").val()),
					resize: 'jpg,jpeg',
		    		form_action: '/core/content_content/save'
	    		}	
	    	}); 
			
			//resize tree height according content
			setSectionTreeHeight();
			
			setTimeout(function(){
				//resize tree height according content
				setSectionTreeHeight();		
				//auto height containers
				autoHeightContent();
			},5000);
                
                //remove an image from the carrusel and register the name of it
                $("[id^='delete_']").each(function(){
		    $(this).bind("click", function(){
                    var current_images = $("#sortable").sortable("toArray"); 
                    if (current_images.length > 1){
                        var imageId = this.id.replace("delete_","");                    
                        var element = document.getElementById(imageId);
                        jQuery(element).detach().appendTo('#deleted_array')      
                    } else {
                        $(this).attr('disabled','disabled');
                        alert("No puede eliminar todas las imágenes");
                    }
                    		
		                        
		    });	
	        });

			
                //save content through flash
	    	$("#save_image").bind("click",function(){
                        //for saving image order
                        var section_list = $("#sortable").sortable("toArray");           
			$('#images_order').val(section_list.join(','));
                        //for deleting images from the directory
                        var deleted_list= $("#deleted_array").sortable("toArray");           
			$('#deleted_images').val(deleted_list.join(','));
	    		if($("#frmContent").valid())
	    			document.getElementById('agileUploaderSWF').submit();
	    	});	
		  }
		  else {
			$("#frmContent").empty();
			$("#frmContent").hide();
			$("#div_preview").hide();
			$("#no_flash_player").show();
		  }					
		break;	
	}		

});

/**
 * Edit elements that have been added on form content
 * @param element
 * @param type
 */
function edit_element(element, type){
	// load on fancy box data of element to edit
	$("#formContent").load("/core/content_content/loadformcontent",{
		element_type:function(){
			return $("#frm_element_type_"+element).val();
		},
		number:function(){
			return $("#edit_element_"+element).attr('number');
			}
	}, function(){
		//validation
		$("#elements_form").validate({
			wrapper: "span",
			onfocusout: false,
			onkeyup: false,
			
            rules: {
				frm_name:{
					required:true
				},
				frm_description:{
					required:false
				}

            }

		});		
		//get data 
		$("#frm_name").val($('#frm_name_'+element).val());
		$("#frm_description").val($('#frm_description_'+element).val());
		if($('#frm_required_'+element).val() == 'yes')
			$('#frm_required_yes').attr("class",'btn btn-primary active');
		else
			$('#frm_required_no').attr("class",'btn btn-primary active');
		$("#element_type").val($('#frm_element_type_'+element).val());
		
		if(type>0)
		{
			$("[id^='hdn_frm_option_"+element+"']").each(function(){
				$('#'+$(this).attr('frm_element_id')).val($(this).val());
			});
		}
		//simulate click to show fancybox
		$("#aux_edit_element_"+element).click();
		//click event on save button
		$("#btn_add").bind("click",function(){
			$("#repeat_frm_name").hide();
			//check if form is valid
			if($("#elements_form").valid())
			{
				//get data
				var form_name = $("#frm_name").val();
				var form_description = $("#frm_description").val();
				var form_required='';
				$('[name^=frm_required]').each(function(){
					if($(this).hasClass('active')){
						form_required = $(this).attr('element_value');
					}
				});
				
				//check if name already exist
				var equal_name=0;
				$("[id^='frm_name_']").each(function(){
					if(form_name == $(this).val() && $(this).attr('id') != 'frm_name_'+element)
						equal_name=1;
				});
				
				if(equal_name==0)
					{
						//set values
						$('#td_name_'+element).empty();
						$('#td_name_'+element).append(form_name);
						
						$('#frm_name_'+element).val(form_name);
						$('#frm_description_'+element).val(form_description);
						$('#frm_required_'+element).val(form_required);
						
						if(type>0)
						{
							//set options values
							var options = 0;
							$("[id^='frm_option_']").each(function(){
								$("#hdn_frm_option_"+element+"_"+options).val($(this).val()); 
								options++;
							});
						}
						//close fancybox
						$.fancybox.close();
						//remove rules
						$("#element_type").rules("remove");
						$("#number").rules("remove");						
					}
				else{
					// show an error if name already exist
						$("#repeat_frm_name").show();
					}
			}
		});
	});	
	
}

/**
 * Hide elements and remove rules on link content
 */
function hide_elements_link(){

	//hide elements on link content
	
	//internal 
	$("[id^=internal_section]").hide();
	$("#internal_section").rules("remove");
	$("#internal_section").attr('disabled',true);
	$("label[for^='internal_section']").remove();
	
	//external link 
	$("[id^=link]").hide();
	$("#link").rules("remove");
	$("#link").attr('disabled',true);
	$("label[for^='link']").remove();
	
	//email
	$("[id^=email]").hide();
	$("#email").rules("remove");
	$("#email").attr('disabled',true);
	$("label[for^='email']").remove();
	
	//file
	$("[id^=file]").hide();
	$("label[for='file']").hide();
	$("#input_file_file").hide();
	$("#hdnNameFile_file").rules("remove");
	$("#hdnNameFile_file").attr('disabled',true);
	$("label[for^='file']").remove();
	
	$("[id^=file_type]").hide();
	$("#file_type").rules("remove");
	$("#file_type").attr('disabled',true);
	$("label[for^='file_type']").remove();
	
	//auto height containers
//	autoHeightContent();
	
}

/**
 * load file by ajax upload
 * @param element_sufix
 * @param element_type
 */
function load_file(element_sufix, element_type)
{
	new AjaxUpload('#'+element_sufix,{//UPLOADS FILE TO THE $_FILES VAR
		action: "/core/content_content/uploadfile",
		data:{
			directory: 'public/uploads/tmp/',
			maxSize: 2097152,
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
						url: "/core/content_content/deletefile",
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
					setTimeout(function(){
						//resize tree height according content
						setSectionTreeHeight();		
						//auto height containers
						autoHeightContent();
					},500);
				}
				
				$('#input_file_'+element_sufix).val(file);
				$('#hdnNameFile_'+element_sufix).val(response);
				$("#del_img_"+element_sufix).show();
				

				
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

//deletes a section picture
function delete_file(index)
{

		if($("#hdnNameFile_"+index).val()){
			$.ajax({
				url: '/core/content_content/deletefile',
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

}
/**
 * get info from youtube url
 * @param youtube
 */
function getYouTubeInfo(youtube) {
 //get youtube id 
 var results; 
 //large url
    results = youtube.match("[\\?&]v=([^&#]*)");
 if( results === null ) //short url
  results = youtube.match("youtu.be/([^&#]*)");
 if(results !== null){
  //check if data exist through ajax
  $.ajax({
                    url:"https://www.googleapis.com/youtube/v3/videos?id="+results[1]+"&key=AIzaSyCA4j53nO0C6DTmDXwskV1CFtl28itdsy8&part=snippet,contentDetails,statistics,status",
      error: function()
      {
       parseresults(null); 
      },
      success: function()
      {
       //get data through ajax
       $.ajax({
              url:"https://www.googleapis.com/youtube/v3/videos?id="+results[1]+"&key=AIzaSyCA4j53nO0C6DTmDXwskV1CFtl28itdsy8&part=snippet,contentDetails,statistics,status",
              dataType: "json",
              success: function (data) {
               parseresults(data); 
      // resize tree height according content
      setSectionTreeHeight(); 
              }
       }); 
      }
  });  
  
 }

}

function parseresults(data) {
    //console.log(JSON.parse(JSON.stringify(data)));
 //set data
 if(data){
        var title = data['items'][0]['snippet']['title'];
        var description = data['items'][0]['snippet']['description'];
        var img = data['items'][0]['snippet']['thumbnails']['high']['url'];
        $("#img_youtube").attr('src',img);
        $('#title_youtube').html('<b>'+title+'</b>');
        $('#url_youtube').html($("#url").val());
        $('#description_youtube').html('<p>'+description.substring(0,160)+'...</p>' );
 }else{
        $("#img_youtube").attr('src','');
        $('#title_youtube').html('');
        $('#url_youtube').html('');
        $('#description_youtube').html('');  
 }
}

/**
 * Add action to the watermark position selector panel
 */
function setWatermarkPos(){
	//default
	//$('#watermark_pos_container').removeClass('hide');
	setTimeout("setPosition($('#watermark_position').val())",30);
	
	//listeners
	$("[id^='wmk_pos_']").each(function(){
		$(this).bind('click',function(){
                    //alert('entra');
			clearWatermarkPos();
			$(this).addClass('btn-success');
			//save value
			$('#watermark_position').val($(this).attr('pos'));
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
