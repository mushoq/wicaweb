$(document).ready(function(){
	
	setSectionTreeHeight();
	clear_section_bar();
	
	$("#content_sortable").sortable(
		{
			handle: ".handler",
			axis: "y",
			cursor: "move"
		}		
	);
	$("#content_sortable").disableSelection();
	
	setTimeout("resize_content_list()",100);
	
	//saves ordered contents
	$('#save_content_order').bind('click', function() {
		var section_list = $("#content_sortable").sortable("toArray");		
		$('#content_order').val(section_list.join(','));
		
		$.ajax({
			type: 'POST',
			async: false,
			url: '/core/section_section/saveorder',
			dataType: 'json',
			data: 	$( "#frmContentOrder" ).serialize(),
			success: function(data) {													
				if(data['serial'])
				{
					if(data['owner']=='section')
					{
						$('#cms_container').load("/core/section_section/sectiondetails", {
							id: data['serial'],
							is_section_temp: data['temp']
						},function(){						
							$.getScript('/js/modules/core/section/sectiondetails.js');
							$.getScript('/js/modules/core/article/articledetails.js');
							$( 'html, body' ).animate( {scrollTop: 0}, 0 );
						});
					}
					else
					{
						$('#cms_container').load("/core/article_article/articledetails", {
							id: data['serial'],
							is_section_temp: data['temp']
						},function(){						
							$.getScript('/js/modules/core/section/sectiondetails.js');
							$.getScript('/js/modules/core/article/articledetails.js');
							$( 'html, body' ).animate( {scrollTop: 0}, 0 );
						});
					}
				}
			}
		});
	});
	
	$("#subsection_sortable").sortable(
		{
			handle: ".handler",
			axis: "y",
			cursor: "move"
		}
	);
	$("#subsection_sortable").disableSelection();
	
	//saves ordered subsections
	$('#save_subsection_order').bind('click', function() {		
		var section_list = $("#subsection_sortable").sortable("toArray");		
		$('#subsection_order').val(section_list.join(','));		
		$.ajax({
			type: 'POST',
			async: false,
			url: '/core/section_section/saveorder',
			dataType: 'json',
			data: $( "#frmSubsectionsOrder" ).serialize(),
			success: function(data) {													
				if(data['serial'])
				{
					$('#cms_container').load("/core/section_section/sectiondetails", {
						id: data['serial']
					},function(){
						$('#section_tree_container').load("/core/section_section/sectionstreedata", function(){
							section_parent = '';
							if($("#section_parent_id").val())
								section_parent = $("#section_parent_id").val();
							mark_section_selected('<a id="tree_'+$("#section_id").val()+'" section_parent="'+section_parent+'" article="no"> </a>');
							$.getScript('/js/modules/core/section/sectionlist.js');
							$.getScript('/js/modules/core/section/sectiondetails.js');
							$.getScript('/js/modules/core/article/articledetails.js');
							$( 'html, body' ).animate( {scrollTop: 0}, 0 );
						});																																	
					});
				}
			}
		});
	});
	
	//load a section to be updated
	$('[id^="edit_section_"]').each(function() {
		$(this).bind('click', function() {
			if($(this).attr("editable_section")=="yes"){
				mark_edit_section_selected($(this));
				$('#cms_container').load("/core/section_section/edit", {
					id: this.id.replace('edit_section_',''),
					is_section_temp : $(this).attr('temp')
				},function(){
					setSectionTreeHeight();	
					$.getScript('/js/modules/core/section/edit.js');
				});
			}
		});	
	});
	
	//delete section
	$('[id^="delete_section_"]').each(function() {
		$(this).bind('click', function() {
			var question = false;
			question = confirm(delete_question);
			if(question){
				$.ajax({
					type: 'POST',
					async: false,
					url: '/core/section_section/delete',
					dataType: 'json',
					data: 	{
						id: this.id.replace("delete_section_","")
					},
					success: function(data) {													
						if(data['serial'])
						{
							$.ajax({
								type: 'POST',
								async: false,
								url: '/core/section_section/sectionstreedata',
								dataType: 'html',
								success: function(data) {
									$('#section_tree_container').html(data);
								}
							});

							$('#cms_container').load("/core/section_section/sectionlist", {
								
							}, function() {				
								setSectionTreeHeight();
								$.getScript('/js/modules/core/section/sectionlist.js');
								$.getScript('/js/modules/core/section/sectiondetails.js');
							});	
						}
					}								
				});
			}			
		});	
	});
	
	//section approve
	$('[id^="approve_section_"]').each(function() {
		$(this).bind('click', function() {			
			var question = false;
			question = confirm(approve_question);
			if(question){			
				mark_edit_section_selected($(this));
				$('#cms_container').load("/core/section_section/approve", {
					id: this.id.replace('approve_section_','')
				},function(){
					$('#section_tree_container').load("/core/section_section/sectionstreedata");
					
					$('#cms_container').load("/core/section_section/sectionlist", {
						
					},function(){						
						$.getScript('/js/modules/core/section/sectionlist.js', function(){		
							setSectionTreeHeight();
						});
					});
				});
			}
		});	
	});
	
	//section keep current
	$('[id^="keep_current_"]').each(function() {
		$(this).bind('click', function() {			
			var question = false;
			question = confirm(keep_current_question);
			if(question){			
				mark_edit_section_selected($(this));
				$('#cms_container').load("/core/section_section/keepcurrent", {
					id: this.id.replace('keep_current_','')
				},function(){
					$('#section_tree_container').load("/core/section_section/sectionstreedata");
					
					$('#cms_container').load("/core/section_section/sectionlist", {
						
					},function(){						
						$.getScript('/js/modules/core/section/sectionlist.js', function(){		
							setSectionTreeHeight();
						});
					});
				});
			}
		});	
	});
	
	//section preview
	$('[id^="section_preview_"]').each(function() {
		$(this).bind('click', function() {	
			div_with = $(window).width()*0.94;
			$('#front_container').attr('style','width: '+div_with+'px;');
			$("#preview").fancybox();			
			$('#front_container').load("/core/section_section/sectionpreview", {
				section_id: this.id.replace('section_preview_','')
			},function(){
				$.getScript('/js/modules/core/section/sectionpreview.js', function(){					
					$("#preview").click();
				});				
			});
		});	
	});
	
	//Edit content
	$("[id^='edit_content_']").each(function(){
		$(this).bind("click", function(){
			var content_id = $(this).attr('content_id');
			var section_id = $(this).attr('section_id');
			$('#cms_container').load("/core/content_content/edit", {
				id: content_id,
				section_id: section_id
			},function(){
				setSectionTreeHeight();	
				$.getScript('/js/modules/core/content/edit.js');
			});		
		});
			
	});
	
	//Erase content
	$("[id^='erase_content_']").each(function(){
		$(this).bind("click", function(){
			var content_by_sec = $(this).attr('content_by_sec');
			var content_id = $(this).attr('content_id');
			var temp = $(this).attr('temp');
			$.ajax({
				async: false,
				type: "POST",
				dataType: 'json',
				url: "/core/content_content/disconnect",
				data: {
					id_cbs : content_by_sec,
					id_cont : content_id,
					temp: temp
				},
				success: function(msj){
					if(msj){
						if(msj['article']=='no'){
							$('#cms_container').load("/core/section_section/sectiondetails", {
								id : parseInt(msj['section_id']) 
							}, function() {
								setSectionTreeHeight();
								setTimeout("resize_content_list()",100);
								$.getScript('/js/modules/core/section/sectionlist.js');
								$.getScript('/js/modules/core/section/sectiondetails.js');
								$.getScript('/js/modules/core/article/articledetails.js');
							});							
						}else
						if(msj['article']=='yes'){
							$('#cms_container').load("/core/article_article/articledetails", {
								id: parseInt(msj['section_id'])
							},function(){							
								setSectionTreeHeight();
							    setTimeout("resize_content_list()",100);
								$.getScript('/js/modules/core/section/sectionlist.js');
								$.getScript('/js/modules/core/section/sectiondetails.js');
								$.getScript('/js/modules/core/article/articledetails.js');
							});							
						}
						
					}else{
						window.location='/core/section_section/index';                    
					}
				}
			});
			
		});
			
	});	
	
	//change content columns
	$("[id^='cols_']").each(function(){
		$(this).bind("click", function(){
			$('#value_cols_'+$(this).attr('content_id')).html($(this).attr('val'));
			$('#content_columns_'+$(this).attr('content_id')).val($(this).attr('val'));
		});	
	});
	//change content align
	$("[id^='align_opt_']").each(function(){
		$(this).bind("click", function(){
			$('#value_align_'+$(this).attr('content_id')).html($(this).html());
			$('#content_align_'+$(this).attr('content_id')).val($(this).attr('val'));
		});	
	});
});
