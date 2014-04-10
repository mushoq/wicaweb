$(document).ready(function(){
	$("#frmDictionary").validate({
        wrapper: "span",
        onfocusout: false,
        onkeyup: false,
        rules: {
                name: {
                     required: true,
                 	 remote: {
                 		url: "/core/dictionary_dictionary/validatedictionaryname",
                         type: "post",
                         dataType: 'json',
                         async:false,
                         data: {
 	                        name: function() {
 	                          return $("#name").val();
 	                        },
 	                       id: function() {
 	                          return $("#id").val();
 	                        }
                         }
                 	}
                },
                website: {
			        required: true			        
			   },
			   words: {
				   required: true
			   }
        },
        messages:{
        	name:{
        		remote: dictionaryname_remote_message
        	}
        }
	});
	
	
    
	//button submit
	$('#submit').bind('click',function(){
		if($('#frmDictionary').valid()){
			$("#frmDictionary").submit();
		}
	});
	
	//button accion
	$('#cancel').bind('click',function(){
		window.location = '/core/dictionary_dictionary';
	});
		
	
});
        