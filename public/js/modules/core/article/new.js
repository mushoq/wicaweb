$(document).ready(function(){
         $.validator.addMethod("time24", function(value, element) { 
            return /^([01]?[0-9]|2[0-3])(:[0-5][0-9]){2}$/.test(value);
        }, "Formato de hora no v\xE1lida.");
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
		$(this).wrap("<div id='step" + i + "' class='col-md-12'></div>");
		//div where next and previous buttons are placed
		$(this).append("<div id='step" + i + "commands'></div>");	
				
		if (i == 0) {
			createNextButton(i);
			selectStep(i);
		}else {			
			$("#step" + i).addClass('hide');
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
				if($('#frmArticle').valid())
				{		
					$("#error_container").addClass('hide');					
					if(j==0){				
						//0, 2, 3
						$("#step"+j).addClass('hide');
						$("#step"+(j+2)).addClass('hide');
						$("#step"+(j+3)).addClass('hide');
						
					}else if(j==1){
						//0, 1 , 3
						$("#step"+j).addClass('hide');
						$("#step"+(j-1)).addClass('hide');
						$("#step"+(j+2)).addClass('hide');
						
					}else if(j==2){	
						//0, 1, 2
						$("#step"+j).addClass('hide');
						$("#step"+(j-2)).addClass('hide');
						$("#step"+(j-1)).addClass('hide');
						
					}					
					addNextButton(j,"step" + (j-1));
					selectStep(j + 1);
				}
			}else{
				j = 1;				
				$("#step" + j).addClass('hide');
				$("#step"+(j+1)).addClass('hide');
				$("#step"+(j+2)).addClass('hide');				
				$("#step" + (j - 1)).removeClass('hide');				
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
		if($("#frmArticle").valid()){			
			$.ajax({
				type: 'POST',
				async: false,
				url: '/core/article_article/save',
				dataType: 'json',
				data: 	$( "#frmArticle" ).serialize(),
				success: function(data) {
					if(data['serial']){
						$('#cms_container').load("/core/article_article/articledetails", {
							id: data['serial'],
							is_article_temp: data['article_temp']
						},function(){							
							$('#section_tree_container').load("/core/section_section/sectionstreedata", {
								
							}, function() {
								//hide article button
								$('#article_option').addClass('hide');
								//hide section button
								$('#section_option').addClass('hide');
								
								 open_section(data['serial']);
								 $.getScript('/js/modules/core/section/sectionlist.js');
								 $.getScript('/js/modules/core/article/articledetails.js');
							});														
						});
					}
				}								
			});
		}			
	});	
        //Orden de art�culos
        $('#order_feature').bind('click', function() {
            //alert($('#section_parent_id').val());
             $.fancybox({
            'width': '80%',
            'height': '80%',
            'autoScale': true,
            'transitionIn': 'fade',
            'transitionOut': 'fade',
            'href': '/core/article_article/order/feature/1',
            'type': 'ajax',
            'onClosed': function() {
                //window.location.href = "f?p=&APP_ID.:211:&SESSION.::&DEBUG.::";
            }

        });

            return false;
            
        });
        $('#order_highlight').bind('click', function() {
            //alert($('#section_parent_id').val());
             $.fancybox({
            'width': '80%',
            'height': '80%',
            'autoScale': true,
            'transitionIn': 'fade',
            'transitionOut': 'fade',
            'href': '/core/article_article/order/highlight/1',
            'type': 'ajax',
            'onClosed': function() {
                //window.location.href = "f?p=&APP_ID.:211:&SESSION.::&DEBUG.::";
            }

        });

            return false;
            
        });
        checkOptions('highlight', 'order_highlight');
});

/*WIZARD FUNCTIONS*/
function createNextButton(i) {
	var stepName = "step" + i;	
	if(i == 0){
		//step basic		
		$("#frmArticle").validate({
			wrapper: "span",
			onfocusout: false,
			onkeyup: false,
			rules: {
				internal_name: {
					required: true,
					remote:{
						url: "/core/article_article/checkinternalname",
						type: "POST"						
					}
				},
				title: {
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
	}
	
	if(i!=(count-1)){
		//next step
		$("#" + stepName + "commands").append("<a href='#' id='" + stepName + "Next' class='btn btn-primary next'>"+next_step+"</a>");
	}

	//actions when next buttons are clicked
	$("#" + stepName + "Next").bind("click", function(e) {		
		if(i==0){
			//BASIC										
			if($('#frmArticle').valid()){				
				$("#error_container").addClass('hide');
				addNextButton(i,stepName);
				selectStep(i + 1);
			}else{
				$("#error_container").removeClass('hide');
			}
		}else{
			addNextButton(i,stepName);
			selectStep(i + 1);
		}			
	});		
}

function addNextButton(i, stepName){	
	$("#" + stepName).addClass('hide');
	$("#step" + (i + 1)).removeClass('hide');	
	
	$("[id^='type_']").each(function(){		
		$(this).bind('click',function(){			
			$("#type").val(this.id.replace('type_',''));
		});
	});
	
	$("[id^='publish_']").each(function(){		
		$(this).bind('click',function(){			
			$("#show_publish_date").val(this.id.replace('publish_',''));
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
                 /*
                  * // PRENDER CUANDO SE AUMENTE CAMPOS DE HORA
                  $("#hora_inicio").rules("add", {
				 time24: true
			});
                 $("#hora_fin").rules("add", {
				 time24: true
			});*/
	}
	
	//re calculate the section tree height
	setSectionTreeHeight();
}

function createPrevButton(i) {
	var stepName = "step" + i;
	$("#" + stepName + "commands").append("<a href='#' id='" + stepName + "Prev' class='btn btn-primary prev'>"+back_step+"</a>");		

	$("#" + stepName + "Prev").bind("click", function() {					
		$("#error_container").addClass('hide');
		$("#" + stepName).addClass('hide');
		$("#step" + (i - 1)).removeClass('hide');		
		selectStep(i - 1);			
	});
}

function selectStep(i) {
	if(i==0)
	{		
		$("#bar_step_0").removeClass('active');
		$("#bar_step_1").removeClass('active');
		$("#bar_step_2").removeClass('active');
		//beg
		$("#bar_step_beg").addClass("active");
		
	}else if(i==1){
		//beg, 1, 2		
		$("#bar_step_beg").removeClass("active");
		$("#bar_step_" + i).removeClass('active');
		$("#bar_step_" + (i+1)).removeClass('active');
		//0
		$("#bar_step_" + (i-1)).addClass("active");
		
	}else{
		//beg, 0, 2
		$("#bar_step_beg").removeClass("active");
		$("#bar_step_" + (i-2)).removeClass('active');
		$("#bar_step_" + i).removeClass('active');
		//1
		$("#bar_step_" + (i-1)).addClass("active");
		
	}
}

/**
 * FUNCI�N PARA MOSTAR EL BOT�N DE ORDEN
 * @param {obj} obj
 * @param {obj} btnOrden
 * @returns {undefined}
 */

function checkOptions(obj, btnOrden){
   
    if($("#" + obj).val()=='yes'){
         //$("#" + btnOrden).removeClass('hide');
         if(obj=='highlight'){
             $("#position_highlight").removeClass('hide');
         }
    }
    $('#' + obj + '_yes').bind('click', function(){
         //$("#" + btnOrden).removeClass('hide');
          if(obj=='highlight'){
             $("#position_highlight").removeClass('hide');
         }
    });
     $('#' + obj + '_no').bind('click', function(){
        
        $("#" + btnOrden).addClass('hide');
        if(obj=='highlight'){
             $("#position_highlight").addClass('hide');
         }
    });
                        
    
}
