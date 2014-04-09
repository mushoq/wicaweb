$(document).ready(function(){
	$("#frmExternalFiles").validate({
        wrapper: "span",
        onfocusout: false,
        onkeyup: false,
        rules: {
                name: {
                     required: true
                },
                file: {
			        required: true,
			        accept: "css,js"
			   }
        }
	});
	
	
	//other validations
	if($('#add_to_all').length>0){
		$('#add_to_all').rules('add',{
			required: true
		});
		
	}
	
	$('[id^=add_to_all_]').each(function(){	
		$(this).bind('click',function(){
			$('#add_to_all').val($(this).attr('option'));
		});		
	});
	
    //button accion
	$('#cancel').bind('click',function(){
		window.location = '/core/user_user';
	});
		
});
        