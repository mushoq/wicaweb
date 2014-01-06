$(document).ready(function() {

	$("#anchor_render").fancybox();
	
	$("[id^='img_render_']").each(function(){
		$(this).bind("click", function(){
			var template_id = $(this).attr("template_id");
			
			$("#render_template").load('/core/template_template/rendertemplate',
				{id: template_id},
				function(){
					$("#anchor_render").click();
					
					$("[id^='wica_area_']").each(function(){
						$(this).css('height','50px');
						$(this).css('border-style', 'dashed');
						$(this).css('border-width', '0.5px');
						$(this).css('width', $(this).parent().width()-2);
						
						$(this).html('<label class="center">'+$(this).attr('id')+'</label>');
					});
					
					if($("#header") != null){
						$('#header').css('height','50px');
						$('#header').css('border-style', 'dashed');
						$('#header').css('border-width', '0.5px');
						$('#header').css('width', $('#header').width()-2);
					}
					
					if($("#footer") != null){
						$('#footer').css('height','50px');
						$('#footer').css('border-style', 'dashed');
						$('#footer').css('border-width', '0.5px');
						$('#footer').css('width', $('#footer').width()-2);
					}		

					if($("#menu") != null){
						$('#menu').css('height','50px');
						$('#menu').css('border-style', 'dashed');
						$('#menu').css('border-width', '0.5px');
						$('#menu').css('width', $('#menu').width()-2);
						
						$('#menu').html('<label class="center" style="color:#FFFFFF">'+$('#menu').attr('id')+'</label>');
					}						
				}
			);

		});
	});
	
});