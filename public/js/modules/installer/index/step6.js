$(document).ready(function() {    
    //STEP6
	
	//default vals 
	$('#offline_text').val(website_offline_text);
    $('#coming_soon_text').val(website_soon_text);
	
	$("#frmWebsite").validate({
        wrapper: "span",
        onfocusout: false,
        onkeyup: false,
        rules: {
                name: {
                    required: true
                },
                description: {
                    required: true
                },
                keywords:{
                	required: true
                },
                website_url:{
                	required: true
                },
                info_email:{
                	required: true,
                	email: true
                },
                copyright:{
                	required: true
                },
                offline_text:{
                	required: true
                },
                coming_soon_text:{
                	required: true
                },
                logo:{
                	accept: "jpg,png,gif,jpeg"
                },
                icon:{
                	accept: "jpg,png,gif,jpeg,ico"
                },
                offline_image:{
                	accept: "jpg,png,gif,jpeg"
                },
                coming_soon_image:{
                	accept: "jpg,png,gif,jpeg"
                },
                template_id:{
                	required: true
                }
        },
        messages:{
        	name:{
        		remote: $("#repeated_website_name").val()
        	}
        }
	});
    
	//image previews
	$("#logo").bind("change",function(){
        readURL(this,'img_'+$(this).attr('id'));
	});
	
	$("#icon").bind("change",function(){
        readURL_icon(this,'img_'+$(this).attr('id'));
        $('#website_icon').html('&nbsp;&nbsp;'+$('#name').val());
	});

	
	$("#offline_image").bind("change",function(){
        readURL(this,'img_offline');
	});
	
	$("#coming_soon_image").bind("change",function(){
        readURL(this,'img_coming_soon');
	});
    
	
	//template picker
	$('[id^=template_opt_]').bind('click',function(){
		clearTemplates();
		$(this).addClass('active');
		$('#template_id').val($(this).attr('template'));
		$(this).siblings('img').addClass('border_image');
	});
			
	//back button
    $('#step6_back').bind('click',function(){
    	$('#step6_container').addClass('hide');
	    $('#step5_container').removeClass('hide');
	    //mark selected step in the legend
	    notSelectedStep(6);
        selectedStep(5);
    });
	
    //next
    $('#submit').bind('click',function(){
    	if($("#frmWebsite").valid()){
    		$("#frmWebsite").submit();
    	}
    });

  //copying title into internal name
	$('#name').keyup(function(){
		$('#keywords').val($(this).val());
	});
    
	//END STEP 6
    
}); //END DOCUMENT ROOT

function clearTemplates(){
	$('[id^=template_opt_]').each(function(){
		$(this).removeClass('active');
		$(this).siblings('img').removeClass('border_image');
	});
}