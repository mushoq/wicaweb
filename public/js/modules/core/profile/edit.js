$(document).ready(function(){
	
	websites_name = new Array();
	websites_id = new Array();
	
	/*WIZARD*/
	count=0;
	//step number
	$(".step_containers").each(function(){
		count=count+1;
	});
	
	$(".step_containers").each(function(i) {
		//div that wraps a step div
		$(this).wrap("<div class='row-fluid'></div>");
		$(this).wrap("<div id='step" + i + "' class='span12'></div>");
		//div where next and previous buttons are placed
		$(this).append("<div id='step" + i + "commands'></div>");	
		
		step_name = $(this).find("span h2").html();			
		$("#steps").append("<li id='stepDesc" + i + "'>" + (i + 1) +'. '+ step_name + "</li>");
		
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
		
	$("#frmCreateProfile").validate({
		errorLabelContainer: "#alerts",
		wrapper: "span",		
		onfocusout: false,
		onkeyup: false,
		rules: {
			sel_website: {
				required: true
			},
			profile: {
				required: true
			}
		},
		invalidHandler: function() {			
			$("#error_container").show();
		}
	});
	
});

/*WIZARD FUNCTIONS*/
function createNextButton(i) {
	var stepName = "step" + i;
	if (i == count-1){		
		//last step
		$("#" + stepName + "commands").append('<input type="button" class="btn btn-success next" id="btnCreate" value="'+last_step+'"/>');
				
		$("#btnCreate").bind("click", function(){
			//close alert
			$('#alerts').html('<a class="close pointer" id="close_icon">×</a>');
			$('#close_icon').bind('click',function(){
				$('#alerts').html('');
				$('#error_container').hide();
			});
			
			//add rule for section is required
			$('#section_sel').rules('add',{
				required: true
			});
			
			if($('#frmCreateProfile').valid()){		
				$("#error_container").hide();
				$('#frmCreateProfile').submit();
			}
			
		});		
	}else if(i == 1){	
		//Module step
		$('[id^="module_"]').each(function(){
			$(this).bind('click',function(){				
				if($(this).attr('parent'))
				{	
					//parent used for module id
					parent_id = $(this).attr('parent');
					
					if($(this).is(':checked')){
						//module action checked
						$('#module_'+$(this).attr('parent')).attr('checked',true);					
					}else{
						//if no actions checked either module
						var actions_num = 0;
						$('[id^="module_'+$(this).attr('parent')+'_"]').each(function(e){
							if($(this).is(':checked')){
								actions_num++;
							}							
						});						
						if(parseInt(actions_num)==0)
							$('#module_'+parent_id).attr('checked',false);
					}					
				}else{
						//if an action is checked module must be checked					
						if($(this).is(':checked')){
							val = true;
						}else{
							val = false;
						}						
						$('[id^="module_'+this.id.replace('module_','')+'"]').each(function(){
							$(this).attr('checked',val);								
						});					
				}			
			});				
		});		
		//next step
		$("#" + stepName + "commands").append("<a href='#' id='" + stepName + "Next' class='btn btn-primary next'>"+next_step+" ></a>");		
		
	}else{
		//next step
		$("#" + stepName + "commands").append("<a href='#' id='" + stepName + "Next' class='btn btn-primary next'>"+next_step+" ></a>");		
	}	

	//actions when next buttons are clicked
	$("#" + stepName + "Next").bind("click", function(e) {
		//close alert
		$('#alerts').html('<a class="close pointer" id="close_icon">×</a>');
		$('#close_icon').bind('click',function(){
			$('#alerts').html('');
			$('#error_container').hide();
		});
		
		if(i==0){
			//website step
			//hidden for website selection
			$('#sel_website').val('');
			//hidden for module selection
			$('#module_sel').rules('remove');
			
			pos = 0;
			$('[id^="website_"]').each(function(){		
				//alert('num');
				//websites checked will be used in dropdown to load specific sections
				if($(this).is(':checked')){
					$('#sel_website').val('valid');			
					pos++;
					websites_name[pos] = $(this).attr('nick');
					websites_id[pos] = this.id.replace('website_','');
				}else{
					var delete_pos = new Array();
					//drop from hidden if website sections are not included
					web_id = this.id.replace('website_','');										
					sel_options = $('#section_sel').val().split('|');
					for(var k=0;k<sel_options.length;k++){	
						delete_opt = sel_options[k].split(',');	
						if(delete_opt[0]==web_id){																						
							delete_pos[k] = k;
						}						
					}
					
					var it = 0;
					var count_del = 0;
					
					while(it < delete_pos.length)
					{						
						delete_opt = sel_options[it].split(',');
						if(delete_opt[0]==web_id){								
							sel_options.splice(it,1);
							count_del++;
							it=0;
						}else{
							it++;
						}
						
						if(count_del==delete_pos.length)
							it = delete_pos.length;
					}
					$('#section_sel').val(sel_options.join('|'));					
				}
			});						
				
			//website dropdown			
			html='<select id="web_list">';
				html+='<option id="">-Seleccione-</option>';
			for( var j =1; j <websites_id.length; j++) {			
				html+='<option value="'+websites_id[j]+'">'+websites_name[j]+'</option>';
			}		
			html+='</select>';
							
			if($('#frmCreateProfile').valid()){		
				$("#error_container").hide();
				addNextButton(i,stepName);
				selectStep(i + 1);
			}

		}else if(i==1){		
			//module step
			
			$('#section_sel').rules('remove');			
			$('#module_sel').val('');			
			$('#module_sel').rules('add',{
				required: true
			});			
			
			//var to show or not website dropdown
			sections_fl = false;
			
			//cms module could load sections
			$('[id^="module_"]').each(function(e){

				if($(this).is(':checked')){
					$('#module_sel').val('valid');
					//cms module id = 1 
					if(parseInt(this.id.replace('module_',''))==2){						
						sections_fl = true;
					}
				}					
			});
			
			if(sections_fl){
				
				$('#messages').hide();
				$('#web_container').show();
				$('#websites').html(html);
				$('#section_container').html('');																				
				
				//load sections according website
				$('#web_list').bind('change',function(){
					$('#section_container').load("/core/section_section/sectionsbywebsite",{
						website_id: $(this).val()
					},function(){												
						
						//selected sections are written on hidden
						if($('#section_sel').val()){
							sel_options = $('#section_sel').val().split('|');
							for(var k=0;k<sel_options.length;k++){
								//grouped as (website,section)
								combine = sel_options[k].split(',');															
								if(combine[0]==$('#website_id').val()){
									if(!combine[1])
										$('#section_'+combine[0]).attr('checked',true);
									else
										$('#section_'+combine[0]+'_'+combine[1]).attr('checked',true);
								}								
							}
						}
						
						$('[id^="section_"]').each(function(){
							$(this).bind('click',function(){
								if($(this).attr('parent'))
								{	
									//sections
									if($(this).is(':checked')){
										//copied to hidden as (website,section)				
										$('#section_sel').val($('#section_sel').val()+$('#website_id').val()+','+this.id.replace('section_'+$('#website_id').val()+'_','')+'|');
									}else{
										//drop from hidden if section is not included
										unselect = $('#website_id').val()+','+this.id.replace('section_'+$('#website_id').val()+'_','');
										sel_options = $('#section_sel').val().split('|');
										for(var k=0;k<sel_options.length;k++){							
											if(sel_options[k]==unselect){
												sel_options.splice(k,1);
											}
										}
										$('#section_sel').val(sel_options.join('|'));
																		
										//it total control is checked or not
										var actions_num = 0;										
										//sections
										$('[id^="section_'+$(this).attr('parent')+'_"]').each(function(e){
											if($(this).is(':checked')){
												actions_num++;
											}
										});
										
										if(parseInt(actions_num)==0){
											//total control is not checked
											$('#section_'+$(this).attr('parent')).attr('checked',false);
											//deleted from hidden
											unselect = $(this).attr('parent');
											sel_options = $('#section_sel').val().split('|');
											for(var k=0;k<sel_options.length;k++){										
												if(sel_options[k]==unselect){
													sel_options.splice(k,1);
												}
											}
											$('#section_sel').val(sel_options.join('|'));
										}
									}									
								}else{
										//total control checks sections									
										if($(this).is(':checked')){
											val = true;
										}else{
											val = false;
										}
										
										$('[id^="section_'+this.id.replace('section_','')+'"]').each(function(n){											
											$(this).attr('checked',val);
											//drop from hidden when there is no total control
											if(val){
												if($(this).attr('parent'))
													criteria = $(this).attr('parent')+','+this.id.replace('section_'+$(this).attr('parent')+'_','');
												else													
													criteria = this.id.replace('section_','');
												//search if it is already on hidden
												insert = true;												
												sel_options = $('#section_sel').val().split('|');
												for(var k=0;k<sel_options.length;k++){																										
													//no insertar																																									
														if(sel_options[k]==criteria){															
																insert = false;
														}
												}												
												if(insert)
													$('#section_sel').val($('#section_sel').val()+criteria+'|');
											}else{												
												//remove when website id equal
												remove_id = this.id.replace('section_','');
												sel_options = $('#section_sel').val().split('|');
												for(var k=0;k<sel_options.length;k++){																									
													delete_opt = sel_options[k].split(',');
																										
													if(parseInt(delete_opt[0])==parseInt(remove_id)){															
															sel_options.splice(k,1);
													}
												}
												$('#section_sel').val(sel_options.join('|'));
											}
										});									
									}			
								});				
							});																						
						});
					});
				}else{	
					//if there is not CMS module selected
					$('#messages').show();
					$('#web_container').hide();
					$('#websites').html('');
					$('#section_container').html('');
					$('#section_sel').val('valid');
				}
				
				if($('#frmCreateProfile').valid()){		
					$("#error_container").hide();
					addNextButton(i,stepName);
					selectStep(i + 1);
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

}

function createPrevButton(i) {
	var stepName = "step" + i;
	$("#" + stepName + "commands").append("<a href='#' id='" + stepName + "Prev' class='btn btn-primary prev'>< "+back_step+"</a>");		

	$("#" + stepName + "Prev").bind("click", function() {					
		$("#error_container").hide();
		$("#" + stepName).hide();
		$("#step" + (i - 1)).show();		
		selectStep(i - 1);
	});
}

function selectStep(i) {
    $("#steps li").removeClass("current");
    $("#stepDesc" + i).addClass("current");
}
