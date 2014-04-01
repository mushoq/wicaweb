$(document).ready(function() {
	
	$("#sortable").sortable(
		{
			handle: ".handler",
			axis: "y",
			cursor: "move"
		}
	);	
	$("#sortable").disableSelection();
	
	setTimeout("resize_content_list()",100);
		
	//loads section details to update it
	$('[id^="tree_"]').each(function() {
		$(this).unbind('click');
		$(this).bind('click', function() {			
			if($(this).attr("editable_section")=="yes"){
				mark_section_selected($(this));
				//show section options
				$('#content_option').removeClass('hide');
				$('#content_link_option').removeClass('hide');
				if($(this).attr("article")=="no"){
					//show article button
					$('#article_option').removeClass('hide');
					//show section button
					$('#section_option').removeClass('hide');
					
					//section details
					$('#cms_container').load("/core/section_section/sectiondetails", {
						id : this.id.replace('tree_',''),
						is_section_temp : $(this).attr('temp')
					}, function() {
						$( 'html, body' ).animate( {scrollTop: 0}, 0 );
						setSectionTreeHeight();
						setTimeout("resize_content_list()",100);
						$.getScript('/js/modules/core/section/sectionlist.js');
						$.getScript('/js/modules/core/section/sectiondetails.js');
						$.getScript('/js/modules/core/article/articledetails.js');
					});
				}else{
					//hide article button
					$('#article_option').addClass('hide');
					//hide section button
					$('#section_option').addClass('hide');
					
					//article details
					$('#cms_container').load("/core/article_article/articledetails", {
						id: this.id.replace('tree_',''),
						is_section_temp : $(this).attr('temp')
					},function(){
						$( 'html, body' ).animate( {scrollTop: 0}, 0 );
						setSectionTreeHeight();
					    setTimeout("resize_content_list()",100);
						$.getScript('/js/modules/core/section/sectionlist.js');
						$.getScript('/js/modules/core/section/sectiondetails.js');
						$.getScript('/js/modules/core/article/articledetails.js');
					});
				}
			}
		});
	});
	
	//save sections order
	$('#save_order').bind('click', function() {		
		var section_list = $("#sortable").sortable("toArray");		
		$('#section_order').val(section_list.join(','));
		
		$.ajax({
			type: 'POST',
			async: false,
			url: '/core/section_section/saveorder',
			dataType: 'json',
			data: 	$( "#frmSectionsOrder" ).serialize(),
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
						
					},function(){						
						$.getScript('/js/modules/core/section/sectionlist.js');
						$( 'html, body' ).animate( {scrollTop: 0}, 0 );
					});
				}
			}								
		});
	});	

});
