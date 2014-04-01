$(document).ready(function(){
		
	$("#frmLinkObjects").validate({
		errorLabelContainer: "#alerts-inner",
		wrapper: "div",
		/*wrapper: "span",*/
		onfocusout: false,
		onkeyup: false,
		rules: {
			content_sel: {
				required: true
			},
			section_id: {
				required: true
			}				
		}
	});		
	
	//errors
	$('#alerts-inner').hide();
	
	$("#related_section").keyup(function(event){
		if(event.keyCode != 13){
			$('#section_id').val('');
		}
		else{
	        $("#btnLink").click();
		}
	});

	/*AUTOCOMPLETERS*/
	$("#related_section").autocomplete("/banners/banners/sectionautocompleter",{
		cacheLength: 0,
		max: 10,
		scroll: false,
		matchContains:true,
		minChars: 2,		
		formatItem: function(row) {
			if(row[0]){
				return row[0] ;
			}
		}
	}).result(function(event, row) {
		if(row[1]==0){
			$('#related_section').removeClass('error_validation');
			$(this).val('');
			$('#section_id').val('');
		}else{
			$('#related_section').removeClass('error_validation');
			$(this).val(row[0]);
			$('#section_id').val(row[1]);
		}
	});
	
	$("#related_section").bind('click',function(){
		$('#related_section').removeClass('error_validation');
	});
	
	/*END AUTOCOMPLETER*/
	
	//search button
	$('#submit_link_search_form').bind('click',function(){
		$('#cms_container').load("/banners/banners/link", {
			text: $('#text').val(),
			section: $('#section').val(),
			search_content: 1,
			section_tree_id: $('#section_tree_id').val()
		}, function() {
			setSectionTreeHeight();
			$.getScript('/js/modules/banners/banners/link.js', function(){		
				
			});	
		});
	});	
	
	//Link banners
	$('#btnLink').bind('click',function(){
		//close alert
		$('#alerts-inner').html('<a class="close pointer" id="close_icon">×</a>');
		$('#close_icon').bind('click',function(){
			$('#alerts-inner').html('');
			$('#alerts-inner').hide();
		});
		
		$('[id^="object_"]').each(function(){
			$('#content_sel').val('');
			if($(this).is(':checked')){
				$('#content_sel').val('valid');
				return false;
			}
		});
		
		if($("#frmLinkObjects").valid()){
			
			$.ajax({
				type: 'POST',
				async: false,
				url: '/banners/banners/linkbanners',
				dataType: 'json',
				data: 	$( "#frmLinkObjects" ).serialize(),
				success: function(data) {									
					if(data['serial'])
					{
						$('#cms_container').load("/banners/banners/index", {
							id: 'all'
						},function(){						
							$( 'html, body' ).animate( {scrollTop: 0}, 0 );
							$.getScript('/js/modules/banners/banners/banners.js');
						});
					}
				}								
			});
		}			
		else{
			$('#submit_link_search_form').focus();
			$('#alerts-inner').show();
			if($('#section_id').val()==''){
				$('#related_section').addClass('error_validation');
			}
		}
		
	});
	
	
	
});