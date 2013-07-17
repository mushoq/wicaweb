$(document).ready(function(){
	$("#frmExternalModules").validate({
        wrapper: "span",
        onfocusout: false,
        onkeyup: false,
        rules: {
                file: {
			        required: true,
			        accept: "zip"
			   },
			   image: {
				   accept: "jpeg,jpg,png,gif"
			   },
			   action: {
				   required: true
			   },
			   name: {
				   required: true
			   }
        }
	});
	
    //button accion
	$('#cancel').bind('click',function(){
		window.location = '/core/externalmodules_externalmodules';
	});
	
	//image previews
	$("#image").bind("change",function(){
        readURL(this,'preview_img');
	});
		
});
        