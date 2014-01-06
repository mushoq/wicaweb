$(document).ready(function(){
	$("#sortable_js").sortable(
		{
			handle: ".handler",
			axis: "y",
			cursor: "move"
		}		
	);
	$("#sortable_js").disableSelection();
	
	$("#sortable_css").sortable(
		{
			handle: ".handler",
			axis: "y",
			cursor: "move"
		}		
	);
	$("#sortable_css").disableSelection();
	
	resize_content_list();
	
	$('#save_order').bind('click', function() {
		
		if($("#sortable_js").length > 0){
			var js_list = $("#sortable_js").sortable("toArray");		
			$('#js_order').val(js_list.join(','));
		}
		
		if($("#sortable_css").length >0){
			var css_list = $("#sortable_css").sortable("toArray");		
			$('#css_order').val(css_list.join(','));
		}
		$('#frmExternalFiles').submit();
	});	
});