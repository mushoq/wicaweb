$(document).ready(function(){
	
	/*WIZARD*/
	count=0;
	//step number
	$(".step_containers").each(function(){
		count=count+1;
	});
	
	$(".wizard_step_bar").each(function(i) {
		$(this).attr('id', 'bar_step_'+i);				
	});	
	
	$(".step_containers").each(function(i) {
		//div that wraps a step div
		$(this).wrap("<div class='row-fluid'></div>");
		$(this).wrap("<div id='step" + i + "' class='span12'></div>");
		//div where next and previous buttons are placed
		$(this).append("<div id='step" + i + "commands'></div>");	
				
		if (i == 0) {
			createNextButton(i);
			selectStep(i);
		}else {			
			$("#step" + i).hide();
			createPrevButton(i);
			createNextButton(i);
		}
	});		
	/*END WIZARD*/
	
	//re calculate the section tree height
	setSectionTreeHeight();
	
	//step on bar
	$('[id^="bar_step_"]').each(function(){
		$(this).bind("click", function(e) {			
			if(this.id.replace('bar_step_','')!='beg'){
				//BASIC			
				j = parseInt(this.id.replace('bar_step_','')); 
				if($('#frmSection').valid())
				{		
					$("#error_container").hide();					
					if(j==0){				
						//0, 2, 3
						$("#step"+j).hide();
						$("#step"+(j+2)).hide();
						$("#step"+(j+3)).hide();
						
					}else if(j==1){
						//0, 1 , 3
						$("#step"+j).hide();
						$("#step"+(j-1)).hide();
						$("#step"+(j+2)).hide();
						
					}else if(j==2){	
						//0, 1, 2
						$("#step"+j).hide();
						$("#step"+(j-2)).hide();
						$("#step"+(j-1)).hide();
						
					}					
					addNextButton(j,"step" + (j-1));
					selectStep(j + 1);
				}
			}else{
				j = 1;				
				$("#step" + j).hide();
				$("#step"+(j+1)).hide();
				$("#step"+(j+2)).hide();				
				$("#step" + (j - 1)).show();				
				selectStep(j - 1);
			}			
		});		
	});	
	
	//cancel button
	$("#cancel_button").bind('click',function(){
		window.location = "/core/section_section/index";
	});	
	
	//save section
	$('#submit_button').bind('click', function() {
		if($("#frmSection").valid()){			
			$.ajax({
				type: 'POST',
				async: false,
				url: '/core/section_section/save',
				dataType: 'json',
				data: 	$( "#frmSection" ).serialize(),
				success: function(data) {									
					if(data['serial']){						
						$('#cms_container').load("/core/section_section/sectiondetails", {
							id: data['serial'],
							is_section_temp: data['section_temp']
						},function(){							
							$('#section_tree_container').load("/core/section_section/sectionstreedata", {
								
							}, function() {				
								 open_section(data['serial']);
								 $( 'html, body' ).animate( {scrollTop: 0}, 0 );
								 $.getScript('/js/modules/core/section/sectionlist.js');
								 $.getScript('/js/modules/core/section/sectiondetails.js');
							});														
						});
					}
				}								
			});
		}			
	});	
});

/*WIZARD FUNCTIONS*/
function createNextButton(i) {
	var stepName = "step" + i;	
	if(i == 0){
		//step basic		
		$("#frmSection").validate({
			wrapper: "span",
			onfocusout: false,
			onkeyup: false,
			rules: {
				internal_name: {
					required: true,
					remote:{
						url: "/core/section_section/checkinternalname",
						type: "POST",
						data:{
							section_id: $('#id').val()
						}
					}
				},
				title: {
					required: true
				},
				link: {
					required: true
				},
				area: {
					required: true
				},			
				homepage: {
					required: true
				}										
			},
	        messages:{
	        	internal_name:{
	        		remote: $("#repeated_section_name").val()
	        	}
	        }
		});
		
		$('textarea').expandingTextArea();
		
		if (CKEDITOR.instances['synopsis']) {
			CKEDITOR.remove(CKEDITOR.instances['synopsis']);
		}
		
		$('#synopsis').ckeditor({ 
			toolbar :		
			[
				{name: 'clipboard', items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ]},
				{name: 'editing', items : [ 'SelectAll','-','SpellChecker', 'Scayt' ]},
				{name: 'basicstyles', items : [ 'Bold','Italic','Underline','-','RemoveFormat' ]},
				{name: 'paragraph', items : [ 'Outdent','Indent','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock' ]},
				{name: 'tools', items : [ 'About' ]}
			]
		});
		
		//copying title into internal name
		$('#title').keyup(function(){
			$('#internal_name').val($(this).val());
		});
		
		//subsection_of						
		$("#subsection_opt").fancybox();
		$("#subsection_of").bind('click',function(){
			$('#subsections_container').load("/core/section_section/sectionstreelist",{
				is_temp : $('#section_temp').val()
			},function(){
				$.getScript('/js/modules/core/section/sectionstreelist.js');
				$("#subsection_opt").click();
			});			
		});
		
		//show on menu
		if(!$('#parent_show_menu').val() || $('#parent_show_menu').val()=='yes'){
			if($('#area option:selected').attr('type')=='variable'){
				if($("#menu").val()=='yes' || $("#menu").val()=='no')
					$('#menu_opt_container').show();
			}
		}
		
		//dependent field when link selected
		if($('#link').val()=="yes"){
			$('#link_container').removeClass("hide");
			$('#link_container').css('display','block');
			$("#external_link").rules("add", {
				 required: true,
				 url: true
				});			
			$("#target").rules("add", {
				 required: true
			});
		}else{
			$('#link_container').css('display','none');				
			$("#external_link").rules("remove");
			$("#external_link").val('');
			$("#target").rules("remove");	
		}
		
		//external link and target
		$('input[id^="link-"]').each(function(){
			$("#target").val("self");
			$(this).click(function(){
				$("#link").val(this.id.replace('link-',''));
				if(this.id.replace('link-','')=='yes'){
					$('#link_container').removeClass("hide");
					$('#link_container').css('display','block');					
					$("#external_link").rules("add", {
						 required: true,
						 url: true
						});			
					$("#target").rules("add", {
						 required: true
					});
					
					$("[id^='target_']").each(function(){		
						$(this).bind('click',function(){			
							$("#target").val(this.id.replace('target_',''));
						});
					});	
					
				}else{
					$('#link_container').css('display','none');				
					$("#external_link").rules("remove");
					$("#external_link").val('');
					$("#target").rules("remove");
					$("#target").val("self");
					
					$("[id^='target_']").each(function(){		
						if($(this).hasClass('active'))
							$(this).removeClass('active');
					});
				}
			});
		});	
		
		//menu
		$("#area").bind("change",function(){
			if($('#area option:selected').attr('type')=='variable'){
				
				if(!$('#parent_show_menu').val() || $('#parent_show_menu').val()=='yes'){			
					$('#menu_opt_container').removeClass("hide");
					$('#menu_opt_container').show();
					$('#menu').val('');			
					$("#menu").rules("add", {
						 required: true
					});	
					
					$("[id^='menu-']").each(function(){		
						if($(this).hasClass('active'))
							$(this).removeClass('active');
					});				
				}else{
					$('#menu').val('no');
				}
				
			}else{
				if(!$('#parent_show_menu').val() || $('#parent_show_menu').val()=='yes'){
					if($('#menu_opt_container').css('display')!='none'){
						$('#menu_opt_container').hide();				
					}
					
					$('#menu').val('no');
					$("#menu").rules("remove");
					
					$("[id^='menu-']").each(function(){		
						if($(this).hasClass('active'))
							$(this).removeClass('active');
					});
				}else{
					$('#menu').val('no');
				}
			}			
		});
		
		$('input[id^="menu-"]').each(function(){		
			$(this).click(function(){
				$("#menu").val(this.id.replace('menu-',''));			
			});
		});	
		
		//homepage
		$('input[id^="homepage-"]').each(function(){		
			$(this).click(function(){
				$("#homepage").val(this.id.replace('homepage-',''));			
			});
		});
	}
	
	if(i!=(count-1)){
		//next step
		$("#" + stepName + "commands").append("<a href='#' id='" + stepName + "Next' class='btn btn-primary next'>"+next_step+"</a>");
	}

	//actions when next buttons are clicked
	$("#" + stepName + "Next").bind("click", function(e) {		
		if(i==0){
			//BASIC										
			if($('#frmSection').valid()){				
				$("#error_container").hide();
				addNextButton(i,stepName);
				selectStep(i + 1);
			}else{
				$("#error_container").show();
			}
		}else{
			addNextButton(i,stepName);
			selectStep(i + 1);
		}			
	});		
}

function addNextButton(i, stepName){
	$("#" + stepName).hide();
	$("#step" + (i + 1)).show();
	
	$("[id^='type_']").each(function(){		
		$(this).bind('click',function(){			
			$("#type").val(this.id.replace('type_',''));
		});
	});
	
	$("[id^='btn_publish_']").each(function(){		
		$(this).bind('click',function(){			
			$("#show_publish_date").val(this.id.replace('btn_publish_',''));
		});
	});	
	
	$("[id^='feature_']").each(function(){		
		$(this).bind('click',function(){			
			$("#feature").val(this.id.replace('feature_',''));
		});
	});	
	
	$("[id^='highlight_']").each(function(){		
		$(this).bind('click',function(){			
			$("#highlight").val(this.id.replace('highlight_',''));
		});
	});	
	
	$("[id^='comments_']").each(function(){		
		$(this).bind('click',function(){			
			$("#comments").val(this.id.replace('comments_',''));
		});
	});
	
	$("[id^='rss_']").each(function(){		
		$(this).bind('click',function(){			
			$("#rss_available").val(this.id.replace('rss_',''));
		});
	});							

	//step publication dates
	//calendars
	if($("input[type=text][id=publish_date]").length > 0 && $("input[type=text][id=expire_date]").length > 0)
	{
		setDefaultCalendar($('#publish_date'),$('#expire_date'));			
	}			

	$('[id^="img_"]').each(function(){		
		$("#hdnNameFile_"+this.id.replace("img_","")).rules("add", {				 
			 accept: "jpg,png,gif,jpeg"
		});
		
		element_sufix = this.id.replace("img_","");
		load_picture(element_sufix);
		
	});
	
	$("[id^='file_img_']").each(function(){
		if($(this).val()){
			$("#del_img_"+this.id.replace("file_img_","")).show();
			delete_picture(this.id.replace("file_img_",""));
		}
	});
	
	//re calculate the section tree height
	setSectionTreeHeight();
}

function createPrevButton(i) {
	var stepName = "step" + i;
	$("#" + stepName + "commands").append("<a href='#' id='" + stepName + "Prev' class='btn btn-primary prev'>"+back_step+"</a>");		

	$("#" + stepName + "Prev").bind("click", function() {					
		$("#error_container").hide();
		$("#" + stepName).hide();
		$("#step" + (i - 1)).show();		
		selectStep(i - 1);			
	});
}

function selectStep(i) {
	if(i==0)
	{		
		$("#bar_step_0").removeClass('active');
		$("#bar_step_1").removeClass('active');
		$("#bar_step_2").removeClass('active');
		//0
		$("#bar_step_beg").addClass("active");
		
	}else if(i==1){
		//beg, 1, 2		
		$("#bar_step_beg").removeClass("active");
		$("#bar_step_" + i).removeClass('active');
		$("#bar_step_" + (i+1)).removeClass('active');
		//0
		$("#bar_step_" + (i-1)).addClass("active");
		
	}else if(i==2){
		//beg, 0, 2
		$("#bar_step_beg").removeClass("active");
		$("#bar_step_" + (i-2)).removeClass('active');
		$("#bar_step_" + i).removeClass('active');
		//1
		$("#bar_step_" + (i-1)).addClass("active");
		
	}else{
		//beg, 0, 1
		$("#bar_step_beg").removeClass('active');
		$("#bar_step_" + (i-3)).removeClass('active');
		$("#bar_step_" + (i-2)).removeClass('active');				
		//2
		$("#bar_step_" + (i-1)).addClass("active");
	}
}

//uploads a section picture			
function load_picture(element_sufix)
{
	new AjaxUpload('#img_'+element_sufix,{
		action: "/core/section_section/uploadfile",
		data:{
			directory: 'public/uploads/tmp/',
			maxSize: 2097152
		},
		name: 'section_photos',
		onSubmit : function(file, ext){
			this.disable();
		},
		onComplete: function(file, response){
			this.enable();
			if(isNaN(response)){
				//deleting previous picture if it exists
				if($("#hdnNameFile_"+element_sufix).val()){
					$.ajax({
						url: "/core/section_section/deletetemppicture",
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
				$('#imageprw_'+element_sufix).show();										
				$('#fileLabel_'+element_sufix).val(file);
				$('#hdnNameFile_'+element_sufix).val(response);
				
			}else{//errors on file uploaded
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

//deletes a section picture
function delete_picture(index)
{
	$("#del_img_"+index).bind("click", function(){	
		if($("#id_img_"+index).val()){
			$.ajax({
				url: '/core/section_section/deletepicture',
				type: "post",
				data: ({
						image_id: function(){
							return $("#id_img_"+index).val();
						},
						is_temp: function(){
							return $("#section_temp").val();
						}
					}),
					success: function(data) {
						$("#fileLabel_"+index).val("");
						$("#name_img_"+index).val("");
						$("#file_img_"+index).val("");
						$("#id_img_"+index).val("");
						$("#hdnNameFile_"+index).val("");
						$("#del_img_"+index).hide();
						$('#imageprw_'+index).attr('src', "");
						$('#imageprw_'+index).hide();
					}
			});
		}		
	});
}
