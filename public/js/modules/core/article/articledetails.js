$(document).ready(function(){

	setSectionTreeHeight();	
	
	$("#article_sortable").sortable(
		{
			handle: ".handler",
			axis: "y",
			cursor: "move"
		}
	);	
	$("#article_sortable").disableSelection();
	
	//saves ordered articles
	$('#save_article_order').bind('click', function() {
		var section_list = $("#article_sortable").sortable("toArray");		
		$('#section_order').val(section_list.join(','));
		
		$.ajax({
			type: 'POST',
			async: false,
			url: '/core/section_section/saveorder',
			dataType: 'json',
			data: 	$( "#frmArticlesOrder" ).serialize(),
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
	
	//load an article to be updated
	$('[id^="edit_article_"]').each(function() {		
		$(this).bind('click', function() {
			mark_edit_section_selected($(this));
			//hide article button
			$('#article_option').addClass('hide');
			//hide section button
			$('#section_option').addClass('hide');
			
			$('#cms_container').load("/core/article_article/edit", {
				id: this.id.replace('edit_article_',''),
				is_section_temp: $(this).attr('temp')
			},function(){
				//setSectionTreeHeight();	
				$.getScript('/js/modules/core/article/edit.js');
			});
		});	
	});
	
	//delete article
	$('[id^="delete_article_"]').each(function() {
		$(this).bind('click', function() {		
                     if(!confirm('¡El artículo seleccionado sera borrado!')) return false;
			$.ajax({
				type: 'POST',
				async: false,
				url: '/core/article_article/delete',
				dataType: 'json',
				data: 	{
					id: this.id.replace("delete_article_","")
				},
				success: function(data) {													
					if(data['serial'])
					{
							$('#section_tree_container').load("/core/section_section/sectionstreedata", function(){
								id = parseInt(data['section_id']);
								section_parent = parseInt(data['section_parent']);
								mark_section_selected('<a id="tree_'+id+'" section_parent="'+section_parent+'" article="yes"> </a>');
								$( 'html, body' ).animate( {scrollTop: 0}, 0 );
							});	
							
							$('#cms_container').load("/core/section_section/sectiondetails", {
								id: parseInt(data['section_id'])
							},function(){							
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
	});
	
	//article preview
	$('[id^="article_preview_"]').each(function() {
		$(this).bind('click', function() {	
			div_with = $(window).width()*0.94;
			$('#article_front_container').attr('style','width: '+div_with+'px;');
			$("#article_prw").fancybox();			
			$('#article_front_container').load("/core/article_article/articlepreview", {
				article_id: this.id.replace('article_preview_','')
			},function(){
				$.getScript('/js/modules/core/article/articlepreview.js', function(){
					$("#article_prw").click();
				});				
			});
		});	
	});
	
});