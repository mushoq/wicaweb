$(document).ready(function(){
	$("[id^='content_type']").each(function(){
		$(this).bind("click",function(){
			var content_id_var = $(this).attr("content_type_id");
			
			//load the corresponding view
			$('#content_container').load("/core/content_content/new", {
				content_id:content_id_var
			}, function() {
				setSectionTreeHeight();
				$.getScript('/js/modules/core/content/new.js', function(){
					
				});								
			});			
			
		})
	});
	
});