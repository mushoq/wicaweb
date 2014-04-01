<?php

$lang = Zend_Registry::get('Zend_Translate');

$required = $lang->translate("This field is required.");
$remote = $lang->translate("Please fix this field.");
$email = $lang->translate("Please enter a valid email address.");
$url = $lang->translate("Please enter a valid URL.");
$date = $lang->translate("Please enter a valid date.");
$dateISO = $lang->translate("Please enter a valid date (ISO).");
$number = $lang->translate("Please enter a valid number.");
$percentage = $lang->translate("Please enter a valid percentage.");
$digits = $lang->translate("Please enter only digits.");
$textOnly = $lang->translate("Please enter only characters.");
$creditcard = $lang->translate("Please enter a valid credit card number.");
$equalTo = $lang->translate("Please enter the same value again as in field above.");
$notEqualTo = $lang->translate("Please enter a different value.");
$accept = $lang->translate("Please enter a value with a valid extension. Valid Extensions are {0}");
$maxlength = $lang->translate("Please enter no more than {0} characters.");
$minlength = $lang->translate("Please enter at least {0} characters.");
$rangelength = $lang->translate("Please enter a value between {0} and {1} characters long.");
$range = $lang->translate("Please enter a value between {0} and {1}.");
$max = $lang->translate("Please enter a value less than or equal to {0}.");
$min = $lang->translate("Please enter a value greater than or equal to {0}.");
$cedulaEcuador = $lang->translate("Please enter valid CI.");
$dateLessEqThan = $lang->translate("Please enter a Date less than or equal to {0}.");
$dateMoreThan = $lang->translate("Please enter a Date more than to {0}.");
$dateMoreEqThan = $lang->translate("Please enter a Date more than or equal to {0}.");
$alphaNumeric = $lang->translate("Please enter only alphanumeric characters.");

?>
<script type="text/javascript" charset="utf-8">
    $(document).ready(
        function() {
            // translate validator error msg
            $.extend($.validator.messages, {
                required: "<?php echo $required;?>",
        		remote: "<?php echo $remote;?>",
        		email: "<?php echo $email;?>",
        		url: "<?php echo $url;?>",
        		date: "<?php echo $date;?>",
        		dateISO: "<?php echo $dateISO;?>",
        		number: "<?php echo $number;?>",
        		percentage: "<?php echo $percentage;?>",
        		digits: "<?php echo $digits;?>",
        		textOnly: "<?php echo $textOnly;?>",
        		creditcard: "<?php echo $creditcard;?>",
        		equalTo: "<?php echo $equalTo;?>",
        		notEqualTo: "<?php echo $notEqualTo;?>",
        		accept: "<?php echo $accept;?>",
        		maxlength: $.validator.format("<?php echo $maxlength;?>"),
        		minlength: $.validator.format("<?php echo $minlength;?>"),
        		rangelength: $.validator.format("<?php echo $rangelength;?>"),
        		range: $.validator.format("<?php echo $range;?>"),
        		max: $.validator.format("<?php echo $max;?>"),
        		min: $.validator.format("<?php echo $min;?>"),
        		cedulaEcuador: "<?php echo $cedulaEcuador;?>",
        		dateLessThan: $.validator.format("<?php echo $dateLessEqThan;?>"),
                dateMoreThan: $.validator.format("<?php echo $dateMoreThan;?>"),
        		dateMoreEqThan: $.validator.format("<?php echo $dateMoreEqThan;?>"),
        		alphaNumeric: $.validator.format("<?php echo $alphaNumeric;?>")
            });                          
           
        }
    );
</script>