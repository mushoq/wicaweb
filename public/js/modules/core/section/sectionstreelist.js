$(document).ready(function(){

//set section parent (subsection of)	
$("a[id^='section_selector_']").each(function(){
	$(this).bind('click',function(){			
		$('#section_parent_id').val($(this).attr('section_id'));
		$('#subsection_of').html($(this).text());
		$.fancybox.close();
	});	
});
	
});

/**
 * Toggle the section tree list
 */
function toggle_section_selector(elem){
	var section_id = $(elem).attr('section_id');//recover section id
	var article = $(elem).attr('article'); //recover articule attr

	if (!$(elem).hasClass('selected') && $(elem).parent('div.parent_section').length) {
      // show child sections and mark elements as selected, change clases and icons
      $(elem).addClass('selected');
      $(elem).parent('div').parent('li').addClass('selected');
      $(elem).parent('div').siblings('ul.section_selector_internal').removeClass('hide').show();
      $(elem + 'i#open_selector_' + section_id).removeClass('icon-plus');
      $(elem + 'i#open_selector_' + section_id).addClass('icon-minus');
      if(article=='no'){
    	  $(elem + 'i#open_folder_' + section_id).removeClass('icon-folder-close');
    	  $(elem + 'i#open_folder_' + section_id).addClass('icon-folder-open');
      }
	}
	else {
		 //return all the sections to the orginal classes and icons
	      $(elem).removeClass('selected');
	      $(elem).parent('div').parent('li').removeClass('selected');
	      $(elem).parent('div').siblings('ul.section_selector_internal').addClass('hide');
          $(elem + 'i#open_selector_' + section_id).removeClass('icon-minus icon-white');     
          $(elem + 'i#open_selector_' + section_id).addClass('icon-plus');
          $(elem + 'i#open_folder_selector_' + section_id).removeClass('icon-folder-open icon-white');
          $(elem + 'i#open_folder_selector_' + section_id).addClass('icon-folder-close');
	}
}