$(document).ready(function(){

	$("#frmLogin").validate({
        wrapper: "span",
        onfocusout: false,
        onkeyup: false,
        rules: {
                username: {
                     required: true
                },
                password: {
                     required: true
                }
        }
	});
});