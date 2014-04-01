<?php

$lang = Zend_Registry::get('Zend_Translate');
?>
<script type="text/javascript" charset="utf-8">
    $(document).ready(
        function() {                      

        	$.fn.agileUploader.defaults = {
        			// First the Flash embed size and Flashvars (which is another object which makes it easy)
        			flashSrc: '/js/agile/agile-uploader.swf',
        			flashWidth: 25,
        			flashHeight: 22,
        			flashParams: {allowscriptaccess: 'always'},
        			flashAttributes: {id: "agileUploaderSWF"},
        			flashVars: {
        				max_height: 500,
        				max_width: 500,
        				jpg_quality: 85, 
        				preview_max_height: 50,
        				preview_max_width: 50,
        				show_encode_progress: true,
        				js_get_form_data: '$.fn.agileUploaderSerializeFormData',
        				js_event_handler: '$.fn.agileUploaderEvent',
        				return_submit_response: true,
        				file_filter: '*.jpg;*.jpeg;*.gif;*.png;*.JPG;*.JPEG;*.GIF;*.PNG;*.zip',
        				file_filter_description: 'Files',
        				// max post size is in bytes (note: all file size values are in bytes)
        				max_post_size: (1536 * 1024),
        				file_limit: -1,
        				button_up:'/js/agile/add-file.png',
        				button_over:'/js/agile/add-file.png',
        				button_down:'/js/agile/add-file.png'		
        			},
        			progressBarColor: '#000000',
        			attachScrollSpeed: 1000,		
        			removeIcon: '/js/agile/trash-icon.png',
        			genericFileIcon: '/js/agile/file-icon.png',
        			maxPostSizeMessage: '<?php echo $lang->translate('Attachments exceed maximum size limit').'.'; ?>',
        			maxFileMessage: '<?php echo $lang->translate('File limit hit, try removing a file first').'.'; ?>',
        			duplicateFileMessage: '<?php echo $lang->translate('This file has already been attached').'.'; ?>',
        			notReadyMessage: '<?php echo $lang->translate('The form can not be submitted yet because there are still files being resized').'.'; ?>',
        			removeAllText: '<?php echo $lang->translate('remove all').'.'; ?>'
        		};	

            
        }
    );
</script>