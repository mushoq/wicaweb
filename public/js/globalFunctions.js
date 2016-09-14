// JavaScript Document
$(document).ready(function(){
    $(".btn-group > .btn").click(function(){
        $(this).addClass("current").siblings().removeClass("active");
    });
    
    $(".fancybox").fancybox();
    $(".wicabox").fancybox();

    $(".various").fancybox({
            maxWidth	: 800,
            maxHeight	: 600,
            fitToView	: false,
            width		: '70%',
            height		: '70%',
            autoSize	: false,
            closeClick	: false,
            openEffect	: 'none',
            closeEffect	: 'none'
    });
});


/**
 * Read the real path from file input
 * @param Object input
 * @param String preview
 */
function readURL(input, preview) {
	
    if (input.files && input.files[0]) {
    	
        var reader = new FileReader();

        reader.onload = function (e) {
            $('#'+preview)
                .attr('src', e.target.result)
                .width(200);
            $('#'+preview).removeClass('hide');
        };

        reader.readAsDataURL(input.files[0]);
    }
}

/**
 * Read the real path from file input apply for icons
 * @param Object input
 * @param String preview
 */
function readURL_icon(input, preview) {
	
    if (input.files && input.files[0]) {
    	
        var reader = new FileReader();

        reader.onload = function (e) {
            $('#'+preview)
                .attr('src', e.target.result)
                .width(16)
            	.height(16);
            $('#'+preview).removeClass('hide');
        };

        reader.readAsDataURL(input.files[0]);
    }
}

/**
 * Toggle the section tree
 */
function toggle_section_tree(elem){
	
	var section_id = $(elem).attr('section_id');//recover section id
	var article = $(elem).attr('article'); //recover articule attr

	if (!$(elem).hasClass('selected') && $(elem).parent('div.parent_section').length) {
		
      // show child sections and mark elements as selected, change clases and icons
      $(elem).addClass('selected');
      $(elem).parent('div').parent('li').addClass('selected');
      $(elem).parent('div').siblings('ul.sections_tree_internal').removeClass('hide').show();
      $(elem + 'i#open_' + section_id).removeClass('icon-plus');
      $(elem + 'i#open_' + section_id).addClass('icon-minus');
      if(article=='no'){
    	  $(elem + 'i#open_folder_' + section_id).removeClass('glyphicon glyphicon-folder-close');
    	  $(elem + 'i#open_folder_' + section_id).addClass('glyphicon glyphicon-folder-open');
      }
      //check if the selected section should be marked or not
      check_mark_section(section_id);
    } else {
      //return all the sections to the orginal classes and icons
      $(elem).removeClass('selected');
      $(elem).parent('div').parent('li').removeClass('selected');
      $(elem).parent('div').siblings('ul.sections_tree_internal').addClass('hide');
      //if the section is selected but the children are closed
      if($(elem).parent('div').hasClass('selected')){
    	  $(elem + 'i#open_' + section_id).removeClass('icon-minus');
    	  $(elem + 'i#open_' + section_id).addClass('icon-plus');
      }
      else{
          $(elem + 'i#open_' + section_id).removeClass('icon-minus icon-white');     
          $(elem + 'i#open_' + section_id).addClass('icon-plus');
          $(elem + 'i#open_folder_' + section_id).removeClass('glyphicon glyphicon-folder-open icon-white');
          $(elem + 'i#open_folder_' + section_id).addClass('glyphicon glyphicon-folder-close');
      }
    }
	
}

/**
 * Mark the section as selected
 */
function mark_section_selected(elem){
	var section_id = $(elem).attr('section_id'); //recover section id
	var article = $(elem).attr('article'); //recover articule attr

	if(!section_id){
		//element from the section list was selected
		var id = $(elem).attr('id');
		$('ul#sections_tree li div.parent_section a#'+id).addClass('selected');
		$('ul#sections_tree li div.parent_section a#'+id).parent('span').parent('div').addClass('selected');
		$('ul#sections_tree li div.parent_section a#'+id).parent('span').siblings('a.open_sections').children('i').addClass('icon-white');
		if(article=='yes')
			$('ul#sections_tree li div.parent_section a#'+id).parent('span').siblings('i').addClass('icon-white');
		else
			$('ul#sections_tree li div.parent_section a#'+id).parent('span').siblings('i').addClass('glyphicon glyphicon-folder-open icon-white');

		//open the parent sections of the selected one
		var parent_id = $(elem).attr('section_parent');
		if(parent_id)
			open_parent_section(parent_id);
	}
	else{
		//Element from the tree was selected
		if (!$(elem).hasClass('selected') && $(elem).parent('span').parent('div.parent_section').length) {
			remove_old_selected_section(elem); // removes all other marked sections
			
			//mark section as selected, change classes and icons
			$(elem).addClass('selected');
			$(elem).parent('span').parent('div').addClass('selected');
			$(elem).parent('span').siblings('a.open_sections').children('i#open_' + section_id).addClass('icon-white');
			if(article=='yes')
				$(elem).parent('span').siblings('i#open_folder_' + section_id).addClass('icon-white');
			else
				$(elem).parent('span').siblings('i#open_folder_' + section_id).addClass('glyphicon glyphicon-folder-open icon-white');
			
			if($(elem).attr('section_parent'))
				mark_parent_section_selected($(elem).attr('section_parent')); //mark the parent section as selected
		}
		else{
			// same section clicked or an already opened section clicked
			$('ul#section_tree_internal_'+section_id+' a[id^="tree_"]').each(function() {
				if($(this).hasClass('selected')){
					//remove selected section attr to all children of the displayed section
					$(this).removeClass('selected');
					$(this).parent('span').parent('div').removeClass('selected');
					$(this).parent('span').siblings('a.open_sections').children('i#open_' + $(this).attr('section_id')).removeClass('icon-white');
					$(this).parent('span').siblings('i#open_folder_' + $(this).attr('section_id')).removeClass('icon-white');
					if($(this).parent('span').siblings('div').hasClass('no-icon')){ // checks if has o no children to add icon-plus or not
						if($(this).attr('article')=='no'){
							$(this).parent('span').siblings('i#open_folder_' + $(this).attr('section_id')).removeClass('glyphicon glyphicon-folder-open icon');
							$(this).parent('span').siblings('i#open_folder_' + $(this).attr('section_id')).addClass('glyphicon glyphicon-folder-close');
						}
					}
					if($(this).parent('span').siblings('a.open_sections').children('i#open_' + $(this).attr('section_id')).hasClass('icon-plus')){
						$(this).parent('span').siblings('i#open_folder_' + $(this).attr('section_id')).removeClass('glyphicon glyphicon-folder-open icon');
						$(this).parent('span').siblings('i#open_folder_' + $(this).attr('section_id')).addClass('glyphicon glyphicon-folder-close');
					}
				}
			});
		}
		
	}
}

/**
 * Mark the parent section as selected
 */
function mark_parent_section_selected(parent_id){
	if(parent_id){ // checks if the section has parent or if it is a root section
		
		var section_id = parent_id;
		var elem = $('#tree_'+section_id);
		
		if (!$(elem).hasClass('selected') && $(elem).parent('span').parent('div.parent_section').length) {
			//mark section as selected, change classes and icons
			$(elem).addClass('selected');
			$(elem).parent('span').parent('div').addClass('selected');
			$(elem).parent('span').siblings('a.open_sections').children('i#open_' + section_id).addClass('icon-white');
			$(elem).parent('span').siblings('i#open_folder_' + section_id).addClass('glyphicon glyphicon-folder-open icon-white');
			//mark the parent section as selected
			mark_parent_section_selected($(elem).attr('section_parent')); 
		}
	}
}


/**
 * Unmark the section as selected
 */
function remove_old_selected_section(elem){
	$('a[id^="tree_"]').each(function() {
		if($(this).attr('id')!= elem.attr('id')){
			var article = $(this).attr('article'); //recover articule attr
			
			$(this).removeClass('selected');
			$(this).parent('span').parent('div').removeClass('selected');
			$(this).parent('span').siblings('a.open_sections').children('i#open_' + $(this).attr('section_id')).removeClass('icon-white');
			$(this).parent('span').siblings('i#open_folder_' + $(this).attr('section_id')).removeClass('icon-white');
			
			//if the section has no child change icon to closed folder
			if($(this).parent('span').siblings('div').hasClass('no-icon')){
				//if it is article, the icon doesn't need to change
				if(article=='no'){
					$(this).parent('span').siblings('i#open_folder_' + $(this).attr('section_id')).removeClass('glyphicon glyphicon-folder-open');
					$(this).parent('span').siblings('i#open_folder_' + $(this).attr('section_id')).addClass('glyphicon glyphicon-folder-close');
				}
			}
			//parent section but not opened
			if($(this).parent('span').siblings('a.open_sections').children('i#open_' + $(this).attr('section_id')).hasClass('icon-plus')){
				$(this).parent('span').siblings('i#open_folder_' + $(this).attr('section_id')).removeClass('glyphicon glyphicon-folder-open');
				$(this).parent('span').siblings('i#open_folder_' + $(this).attr('section_id')).addClass('glyphicon glyphicon-folder-close');
			}
			//checks if the section is a root section
			if(!$(this).attr('section_parent') && $(this).parent('span').parent('div').siblings('ul.sections_tree_internal').hasClass('hide')){
				$(this).parent('span').siblings('i#open_folder_' + $(this).attr('section_id')).removeClass('glyphicon glyphicon-folder-open');
				$(this).parent('span').siblings('i#open_folder_' + $(this).attr('section_id')).addClass('glyphicon glyphicon-folder-close');
			}
		}
	});
}


/**
 * set section tree height according to the content height in each page
 */
function setSectionTreeHeight(){
	if ($(window).width() > 969) {
		var height_bar = 0;
		height_bar = $('#section_content').height();
		
		if(height_bar>0)
			$('#section_tree_container').css('min-height',(height_bar-20));
	}
}

/**
 * auto height containers
 */
function autoHeightContent(){
	
	//get height data 
	var height_content = 0;
	var height_preview = 0;
	height_content = $('#frmContent').height();
	height_preview = $('#div_preview').height();
	
	//compare height between divs
	if(height_content>height_preview){
		$('#div_preview').css('height',(height_content));
	}else
		if(height_content<height_preview){
			$('#frmContent').css('height',(height_preview));
		}
}

/**
 * open the new section
 */
function open_section(section_id){
	var elem = $('#tree_'+section_id);
	//article
	var article = $(elem).attr('article'); //recover articule attr
	//mark section as selected, change classes and icons
	$(elem).addClass('selected');
	$(elem).parent('span').parent('div').addClass('selected');
	$(elem).parent('span').siblings('a.open_sections').addClass('selected');
	$(elem).parent('span').siblings('a.open_sections').children('i#open_' + section_id).addClass('icon-white');
	if(article == 'yes')
		$(elem).parent('span').siblings('i#open_folder_' + section_id).addClass('icon-white');
	else
		$(elem).parent('span').siblings('i#open_folder_' + section_id).addClass('glyphicon glyphicon-folder-open icon-white');
	
	//call to function that opens the parte section tree
	open_parent_section($(elem).attr('section_parent'));
}

/**
 * Opens the parents of the new created section
 */
function open_parent_section(parent_id){
	
	if(parent_id){ // checks if the section has parent or if it is a root section
		var section_id = parent_id;
		var elem = $('#tree_'+section_id);
		
		if (!$(elem).hasClass('selected') && $(elem).parent('span').parent('div.parent_section').length) {
			//mark section as selected, change classes and icons
			$(elem).addClass('selected');
			$(elem).parent('span').parent('div').addClass('selected');
			//open the parent section tree
			$(elem).parent('span').siblings('a.open_sections').addClass('selected');
			$(elem).parent('span').parent('div').parent('li').addClass('selected');
			$(elem).parent('span').parent('div').siblings('ul.sections_tree_internal').removeClass('hide').show();
			//change icons
			$(elem).parent('span').siblings('a.open_sections').children('i#open_' + section_id).removeClass('icon-plus');
			$(elem).parent('span').siblings('a.open_sections').children('i#open_' + section_id).addClass('icon-minus');
			$(elem).parent('span').siblings('a.open_sections').children('i#open_' + section_id).addClass('icon-white');
			$(elem).parent('span').siblings('i#open_folder_' + section_id).addClass('glyphicon glyphicon-folder-open icon-white');
			//mark the parent section as selected
			open_parent_section($(elem).attr('section_parent')); 
		}
		else{
			//open parents when the parent is already selected. This happens when the child section is selected from the subsections list
			if ($(elem).hasClass('selected') && $(elem).parent('span').parent('div.parent_section').length) {
				//mark section as selected, change classes and icons
				$(elem).addClass('selected');
				$(elem).parent('span').parent('div').addClass('selected');
				//open the parent section tree
				$(elem).parent('span').parent('div').parent('li').addClass('selected');
				$(elem).parent('span').parent('div').siblings('ul.sections_tree_internal').removeClass('hide').show();
				//change icons
				$(elem).parent('span').siblings('a.open_sections').children('i#open_' + section_id).removeClass('icon-plus');
				$(elem).parent('span').siblings('a.open_sections').children('i#open_' + section_id).addClass('icon-minus');
				//mark the parent section as selected
				open_parent_section($(elem).attr('section_parent'));
			}
		}
	}
}

/**
 * Check if the section has to be marked as selected or not depending on its children
 */
function check_mark_section(section_id){
	$('ul#section_tree_internal_'+section_id+' a[id^="tree_"]').each(function() {
		if($(this).hasClass('selected')){
			var elem = $('#tree_'+section_id);
			var article = $(elem).attr('article'); //recover articule attr
			
			//mark section as selected, change classes and icons
			$(elem).addClass('selected');
			$(elem).parent('span').parent('div').addClass('selected');
			$(elem).parent('span').siblings('a.open_sections').children('i#open_' + section_id).addClass('icon-white');
			if(article=='no')
				$(elem).parent('span').siblings('i#open_folder_' + section_id).addClass('glyphicon glyphicon-folder-open icon-white');
		}
	});
}

/**
 * Function to resize the table-bordered-content divs in order to adapt to its content
 */
function resize_content_list(){
	if($('div.table-bordered-content').length && $('div.table-bordered-content').children('div')){
		var size = 36;
		$('div.table-bordered-content').each(function(){
			size = 36;
			$(this).children('div').each(function(){
				if($(this).height()>size){
					size = $(this).height();
				}
				
			});
			$(this).children('div').each(function(){
				$(this).css('height',size);
			});
		});
	}
}

/**
 * Mark the section as selected
 */
function mark_edit_section_selected(elem){
	
	var id = elem.attr('section_id');//recover id
	var article = elem.attr('article'); //recover articule attr
	remove_old_selected_section(elem);
	$('ul#sections_tree li div.parent_section a#tree_'+id).addClass('selected');
	$('ul#sections_tree li div.parent_section a#tree_'+id).parent('span').parent('div').addClass('selected');
	$('ul#sections_tree li div.parent_section a#tree_'+id).parent('span').siblings('a.open_sections').children('i').addClass('icon-white');
	if(article=='yes')
		$('ul#sections_tree li div.parent_section a#tree_'+id).parent('span').siblings('i').addClass('icon-white');
	else
		$('ul#sections_tree li div.parent_section a#tree_'+id).parent('span').siblings('i').addClass('glyphicon glyphicon-folder-open icon-white');
		
	//open the parent sections of the selected one
	var parent_id = $(elem).attr('section_parent');
	if(parent_id)
		open_parent_section(parent_id);
}

/**
 * Removes the selected class to all elements in the section bar
 */
function clear_section_bar(){
	if($('#new_section').parent('li').hasClass('selected'))
		$('#new_section').parent('li').removeClass('selected');
	
	if($('#new_article').parent('li').hasClass('selected'))
		$('#new_article').parent('li').removeClass('selected');
	
	if($('#new_content').parent('li').hasClass('selected'))
		$('#new_content').parent('li').removeClass('selected');
	
	if($('#content_link').parent('li').hasClass('selected'))
		$('#content_link').parent('li').removeClass('selected');
}