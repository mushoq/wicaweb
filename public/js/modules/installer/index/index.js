$(document).ready(function() {

	
	//validation
    $("#frmInstallStep1").validate({
		wrapper: "span",		
		onfocusout: false,
		onkeyup: false,
		rules: {
			selLanguage: {
				required: true
			}
		}
	});
    
    //change language
    $("#selLanguage").bind('change',function(){
    	//reload the installer index
    	$.ajax({
            type: 'POST',
            async: false,
            url: '/installer/index/setlanguage',
            dataType: 'json',
            data: 	$( "#frmInstallStep1" ).serialize(),
            success: function(data) {													
                if(data['success'])
                {
                	window.location = '/installer';
                }
            }
    	});
    });
    
    //next step button
    $('#step1_next').bind('click',function(){
       if($('#frmInstallStep1').valid()){
           $.ajax({
                type: 'POST',
                async: false,
                url: '/installer/index/step1',
                dataType: 'json',
                data: 	$( "#frmInstallStep1" ).serialize(),
                success: function(data) {													
                    if(data['success'])
                    {
                    	//reload the legend according to the selected language
                        $.ajax({
                            type: 'POST',
                            async: false,
                            url: '/installer/index/legend',
                            dataType: 'html',
                            success: function(data) {
                                $('#instaler_lengend_container').html(data);
                                notSelectedStep(1);
                                selectedStep(2);
                            }
                        });
                    	//reload the installer steps according to the selected language
                        $.ajax({
                            type: 'POST',
                            async: false,
                            url: '/installer/index/steps',
                            dataType: 'html',
                            success: function(data) {
                                $('#installer_container').html(data);
                                $('#step1_container').addClass('hide');
                                $('#step2_container').removeClass('hide');
                                //load JS actions for further steps
                                $.getScript('/js/modules/installer/index/steps.js');
                            }
                        });
                    }
                }
            });
       }
    });
    /*END STEP 1*/
});

/**
 * Remove the selected class from the last step
 */
function notSelectedStep(step){
    $('#opt_step_'+step).removeClass('selected');
    $('#opt_step_'+step).children('i').removeClass('icon-arrow-right');
    $('#opt_step_'+step).children('i').removeClass('icon-white');
    $('#opt_step_'+step).children('i').addClass('icon-minus');
}

/**
 * Add the selected class for the next step
 */
function selectedStep(step){
    $('#opt_step_'+step).addClass('selected');
    $('#opt_step_'+step).children('i').addClass('icon-arrow-right');
    $('#opt_step_'+step).children('i').addClass('icon-white');
    $('#opt_step_'+step).children('i').removeClass('icon-minus');
}