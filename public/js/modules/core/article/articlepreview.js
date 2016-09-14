$(document).ready(function(){	
	
	$('#wica_main_area').addClass("areas_bgcolor");
	area =	$('#section_sel').val();
	$('#'+area).addClass('areaprw_bgcolor');
	
	//add class order_selector
	$('[id^="mov_content_"]').each(function() {
		$(this).addClass("order_selector");
	});
	
	$("#content_container").sortable(
		{			
			cursor: "move",
			stop: function(event, ui){
				//$(ui.item).attr('id').replace('mov_content_', '')
				check_position();
			}
		}		
	);
	$("#content_container").disableSelection();
	
	//add carrusel feaure only for preview
	$("[id^='myCarousel_']").each(function(){
		$(this).carousel({
			interval : 2000
		});

		$(this).bind("mouseenter", function(){
			$("#carousel_left_"+this.id.replace('myCarousel_','')).removeClass('hide');
			$("#carousel_right_"+this.id.replace('myCarousel_','')).removeClass('hide');				
		});
		
		$(this).bind("mouseleave", function(){
			$("#carousel_left_"+this.id.replace('myCarousel_','')).hide();
			$("#carousel_right_"+this.id.replace('myCarousel_','')).hide();				
		});
		
		
		$("#carousel_left_"+this.id.replace('myCarousel_','')).bind("click", function() {
			$(this).carousel('prev');
		});

		$("#carousel_right_"+this.id.replace('myCarousel_','')).bind("click", function() {
			$(this).carousel('next');
		});				
	});

	//change content columns
	$("[id^='opt_cols_']").each(function(){
		$(this).bind("click", function(){			
			used_cols = parseInt($('#columns_content_'+$(this).attr('content_id')).val());
			section_cols = parseInt($('#section_cols').val());
			
			row_factor = 12 / section_cols;
			if(used_cols){
				col_factor = row_factor * used_cols;
			}else{
				col_factor = 12;
			}
			
			if($('#mov_content_'+$(this).attr('content_id')).hasClass("span"+col_factor)){
				$('#mov_content_'+$(this).attr('content_id')).removeClass("span"+col_factor);
			}
			
			//new row factor
			used_cols = parseInt($(this).attr('val'));
			if(used_cols){
				col_factor = row_factor * used_cols;
			}else{
				col_factor = 12;
			}
			$('#mov_content_'+$(this).attr('content_id')).addClass("span"+col_factor);
			$('#cols_value_'+$(this).attr('content_id')).html($(this).attr('val'));
			$('#columns_content_'+$(this).attr('content_id')).val($(this).attr('val'));
			
			clear_position();
			check_position();
		});	
	});
	
	//change content align
	$("[id^='opt_align_']").each(function(){
		if($('#alignment_cont_'+$(this).attr('content_id')+' p').length > 0){
			$('#alignment_cont_'+$(this).attr('content_id')+' p').attr('style','text-align: '+this.id.replace("opt_align_","")+';');
		}
		$(this).bind("click", function(){			
			$('#align_value_'+$(this).attr('content_id')).html($(this).html());
			$('#align_content_'+$(this).attr('content_id')).val($(this).attr('val'));			
			$('#alignment_cont_'+$(this).attr('content_id')).attr("align",this.id.replace("opt_align_",""));	
			if($('#alignment_cont_'+$(this).attr('content_id')+' p').length > 0){
				$('#alignment_cont_'+$(this).attr('content_id')+' p').attr('style','text-align: '+this.id.replace("opt_align_","")+';');
			}
		});	
	});

	//saves ordered contents
	$("[id^='save_content_order_prw_']").each(function(){
		$(this).bind('click', function() { //'#save_content_order_prw'
			var section_list = $("#content_container").sortable("toArray");		
			$('#preview_content_order').val(section_list.join(','));
			
			$.ajax({
				type: 'POST',
				async: false,
				url: '/core/article_article/savepreview',
				dataType: 'json',
				data: 	$( "#frmPreviewContentOrder" ).serialize(),
				success: function(data) {													
					if(data['serial'])
					{
						//close fancybox
						$.fancybox.close();
						$('#cms_container').load("/core/article_article/articledetails", {
							id: data['serial']
						},function(){
							$.getScript('/js/modules/core/section/sectiondetails.js');
							$.getScript('/js/modules/core/article/articledetails.js');
							$( 'html, body' ).animate( {scrollTop: 0}, 0 );
						});
					}
				}
			});
		});
	});
	
	//cancel order contents
	$("[id^='cancel_content_order_prw_']").each(function(){
		$(this).bind('click', function() {
			//close fancybox
			$.fancybox.close();
		});
	});
});

function clear_position(){	
	$('[id^="mov_content_"]').each(function() {
		if($(this).hasClass("jump")){
			$(this).removeClass("jump");
		}
	});	
}

function check_position(){
	steps= 0;
	num = parseInt($('#section_cols').val());
	$('[id^="mov_content_"]').each(function() {
		steps+= parseInt($('#columns_content_'+this.id.replace('mov_content_','')).val());
		//depends on section columns number		
		if(steps > num){		
			if(!$(this).hasClass("jump")){
				$(this).addClass("jump");
			}
			steps = parseInt($('#columns_content_'+this.id.replace('mov_content_','')).val());
		}else{
			if($(this).hasClass("jump")){
				$(this).removeClass("jump");
			}
		}		
	});	
}
