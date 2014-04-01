$(document).ready(function() {

	//search bar
	$("#frmSearchBar").validate({
		errorLabelContainer: "#alerts",
		wrapper: "li",		
		onfocusout: false,
		onkeyup: false,				
		rules: {
			nameField: {
				required: true
			}	
		}
	});
	
	$("#submit_search_form").click('',function(){	
		if($("#frmSearchBar").valid()){
			var action = "";
			if($('#select_search_form-section').is(':checked')){
				action = '/core/section_section/search';			
			}
			else if($('#select_search_form-content').is(':checked')){
				action = '/core/content_content/search';			
			}
			
			//order section list
			$('#cms_container').load(action, {
				nameField: $('#nameField').val()
			}, function() {				
				$.getScript('/js/modules/core/section/sectiondetails.js');		
			});
		}
	});
				
	//new section
	$('#new_section').bind('click',function(){
		//select the option in the section bar
		remove_selected_option();
		$(this).parent('li').addClass('selected');
		//load the corresponding view
		$('#cms_container').load("/core/section_section/new", {
		}, function() {
			$.getScript('/js/modules/core/section/new.js');								
		});
	});
	
	//new content
	$('#new_content').bind('click',function(){
		//select the option in the section bar
		remove_selected_option();
		$(this).parent('li').addClass('selected');
		//load the corresponding view
		$('#cms_container').load("/core/content_content/index", {
		}, function() {
			setSectionTreeHeight();
			$.getScript('/js/modules/core/content/index.js');								
		});
	});
	
	//new article
	$('#new_article').bind('click',function(){
		//select the option in the section bar
		remove_selected_option();
		$(this).parent('li').addClass('selected');
		//load the corresponding view
		$('#cms_container').load("/core/article_article/new", {
		}, function() {			
			$.getScript('/js/modules/core/article/new.js');								
		});		
	});
	
	//link content
	$('#content_link').bind('click',function(){
		//select the option in the section bar
		remove_selected_option();
		$(this).parent('li').addClass('selected');
		//load the corresponding view
		$('#cms_container').load("/core/content_content/link", {
			search_content: 0
		}, function() {
			setSectionTreeHeight();
			$.getScript('/js/modules/core/content/link.js');												
		});
	});
	
	//order section list
	$('#cms_container').load("/core/section_section/sectionlist", {
		
	}, function() {				
		setSectionTreeHeight();
		$.getScript('/js/modules/core/section/sectionlist.js');		
		$.getScript('/js/modules/core/section/sectiondetails.js');		
	});
	
});

//removes the attribute selected of all options
function remove_selected_option(){
	$('#new_section').parent('li').removeClass('selected');
	$('#new_article').parent('li').removeClass('selected');
	$('#new_content').parent('li').removeClass('selected');
	$('#content_link').parent('li').removeClass('selected');
}
