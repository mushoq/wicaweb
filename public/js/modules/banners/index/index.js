$(document).ready(function() {    
	
		//Change href attribute of website tree link in tree navigator
		$('#website_tree_link').attr("href", "/banners"); 
		
		//Add website tree link pointer class
		$('#website_tree_link').attr('class','pointer');

		//Hide searchbar of banner module
		$('.well-searchbar').hide();
		
		//Show banners list for first time 
		$('#cms_container').load("/banners/banners/index", {
			id : 'all'
		}, function() {
			$('#parent_section').addClass('selected');
			//$('.option_bar_container').hide();
			$.getScript('/js/modules/banners/banners/banners.js');
		});
	
		$("#sortable").sortable(
				{
				handle: ".handler",
				axis: "y",
				cursor: "move"
				}
		);	
		$("#sortable").disableSelection();
		
		setTimeout("resize_content_list()",200);
			
		//loads section details to update it
		$('[id^="tree_"]').each(function() {
			$(this).unbind('click');
			$(this).bind('click', function() {
				$('#parent_section').removeClass('selected');
				var parent_section = "#parent_section_"+this.id.replace('tree_','');
				$(parent_section).addClass('selected');
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
						$('#cms_container').load("/banners/banners/index", {
							id : this.id.replace('tree_','')
						}, function() {
							$( 'html, body' ).animate( {scrollTop: 0}, 0 );
							setSectionTreeHeight();
							setTimeout("resize_content_list()",200);
							$.getScript('/js/modules/banners/banners/banners.js');
						});
					}else{
						//hide article button
						$('#article_option').addClass('hide');
						//hide section button
						$('#section_option').addClass('hide');
						
						//article details
						$('#cms_container').load("/banners/banners/index", {
							id: this.id.replace('tree_','')
						},function(){
							$( 'html, body' ).animate( {scrollTop: 0}, 0 );
							setSectionTreeHeight();
						    setTimeout("resize_content_list()",200);
						    $.getScript('/js/modules/banners/banners/banners.js');
						});
					}
				}
			});
		});
		
    
}); //END DOCUMENT ROOT


