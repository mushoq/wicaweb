<?php
/**
 *	Jquery datepicker allows user to choose a date
 *
 * @category   WicaWeb
 * @package    Core_js
 * @copyright  Copyright (c) WicaWeb - Mushoq
 * @license    GNP
 * @version    1.0
 * @author	   David Rosales
 */

$lang = Zend_Registry::get ( 'Zend_Translate' );

$january = $lang->translate ( "January" );
$february = $lang->translate ( "February" );
$march = $lang->translate ( "March" );
$april = $lang->translate ( "April" );
$may = $lang->translate ( "May" );
$june = $lang->translate ( "June" );
$july = $lang->translate ( "July" );
$august = $lang->translate ( "August" );
$september = $lang->translate ( "September" );
$october = $lang->translate ( "October" );
$november = $lang->translate ( "November" );
$december = $lang->translate ( "December" );
$jan = $lang->translate ( "January abbreviation" );
$feb = $lang->translate ( "February abbreviation" );
$mar = $lang->translate ( "March abbreviation" );
$apr = $lang->translate ( "April abbreviation" );
$may = $lang->translate ( "May abbreviation" );
$jun = $lang->translate ( "June abbreviation" );
$jul = $lang->translate ( "July abbreviation" );
$aug = $lang->translate ( "August abbreviation" );
$sep = $lang->translate ( "September abbreviation" );
$oct = $lang->translate ( "October abbreviation" );
$nov = $lang->translate ( "November abbreviation" );
$dec = $lang->translate ( "December abbreviation" );
$mon = $lang->translate ( "Monday abbreviation" );
$tue = $lang->translate ( "Tuesday abbreviation" );
$wed = $lang->translate ( "Wednesday abbreviation" );
$thu = $lang->translate ( "Thursday abbreviation" );
$fri = $lang->translate ( "Friday abbreviation" );
$sat = $lang->translate ( "Saturday abbreviation" );
$sun = $lang->translate ( "Sunday abbreviation" );

//default date format
$format = 'yy/mm/dd';

// session
$session = new Zend_Session_Namespace ( 'id' );
if($session->website_id )
{	
	// date format from website config
	$website = new Core_Model_Website ();
	$website_obj = $website->find ( 'wc_website', array ( 'id' => $session->website_id ) );
	
	if(count($website_obj)>0){
		$website_arr = get_object_vars ( $website_obj [0] );	
			
		switch ($website_arr ['date_format']) 
		{
			case 'dd/mm/yyyy' :
				$format = 'dd/mm/yy';
				break;
			case 'mm/dd/yyyy' :
				$format = 'mm/dd/yy';
				break;
			case 'mm/dd/yy' :
				$format = 'mm/dd/y';
				break;
			case 'm/d/y' :
				$format = 'm/d/y';
				break;
			case 'yyyy/mm/dd' :
				$format = 'yy/mm/dd';
				break;
			case 'yy/mm/dd' :
				$format = 'y/mm/dd';
				break;		
			default :
				$format = 'yy/mm/dd';
				break;
		}
	}
}
?>

<script type="text/javascript" charset="utf-8">

function setDefaultCalendar(object1, object2){   
	 var dates =  object1.datepicker({
    	defaultDate: 0,
			changeMonth: true,
			numberOfMonths: 1,
			showOn: 'button',
			buttonImage: '/images/calendar.gif',
			buttonImageOnly: true,
			dateFormat: '<?php echo $format; ?>',
			monthNames: ['<?php echo $january;?>','<?php echo $february;?>','<?php echo $march;?>','<?php echo $april;?>','<?php echo $may;?>','<?php echo $june;?>','<?php echo $july;?>','<?php echo $august;?>','<?php echo $september;?>','<?php echo $october;?>','<?php echo $november;?>','<?php echo $december;?>'],
			monthNamesShort: ['<?php echo $jan;?>','<?php echo $feb;?>','<?php echo $mar;?>','<?php echo $apr;?>','<?php echo $may;?>','<?php echo $jun;?>','<?php echo $jul;?>','<?php echo $aug;?>','<?php echo $sep;?>','<?php echo $oct;?>','<?php echo $nov;?>','<?php echo $dec;?>'],
			dayNamesMin: ['<?php echo $sun;?>', '<?php echo $mon;?>', '<?php echo $tue;?>', '<?php echo $wed;?>', '<?php echo $thu;?>', '<?php echo $fri;?>', '<?php echo $sat;?>'],
			onSelect: function( selectedDate ) {
			var option = "minDate",
				instance = $( this ).data( "datepicker" ),
				date = $.datepicker.parseDate(
					instance.settings.dateFormat ||
					$.datepicker._defaults.dateFormat,
					selectedDate, instance.settings );
			dates.not( this ).datepicker( "option", option, date );
		}			
     });

	 if(object2)
	 {
		 var dates =  object2.datepicker({
	      	defaultDate: 0,
				changeMonth: true,
				numberOfMonths: 1,
				showOn: 'button',
				buttonImage: '/images/calendar.gif',
				buttonImageOnly: true,
				dateFormat: '<?php echo $format; ?>',
				monthNames: ['<?php echo $january;?>','<?php echo $february;?>','<?php echo $march;?>','<?php echo $april;?>','<?php echo $may;?>','<?php echo $june;?>','<?php echo $july;?>','<?php echo $august;?>','<?php echo $september;?>','<?php echo $october;?>','<?php echo $november;?>','<?php echo $december;?>'],
				monthNamesShort: ['<?php echo $jan;?>','<?php echo $feb;?>','<?php echo $mar;?>','<?php echo $apr;?>','<?php echo $may;?>','<?php echo $jun;?>','<?php echo $jul;?>','<?php echo $aug;?>','<?php echo $sep;?>','<?php echo $oct;?>','<?php echo $nov;?>','<?php echo $dec;?>'],
				dayNamesMin: ['<?php echo $sun;?>', '<?php echo $mon;?>', '<?php echo $tue;?>', '<?php echo $wed;?>', '<?php echo $thu;?>', '<?php echo $fri;?>', '<?php echo $sat;?>'],
				onSelect: function( selectedDate ) {
					var option = "maxDate",
						instance = $( this ).data( "datepicker" ),
						date = $.datepicker.parseDate(
							instance.settings.dateFormat ||
							$.datepicker._defaults.dateFormat,
							selectedDate, instance.settings );
					dates.not( this ).datepicker( "option", option, date );
				}			
	       });
	 }
 }   

function setDefaultCalendarOldPbl(object1){   
	 var dates =  object1.datepicker({
  	defaultDate: 0,
  	maxDate: "-1d +0m +0w",
			changeMonth: true,
			numberOfMonths: 1,
			showOn: 'button',
			buttonImage: '/images/calendar.gif',
			buttonImageOnly: true,
			dateFormat: 'yy-mm-dd',
			monthNames: ['<?php echo $january;?>','<?php echo $february;?>','<?php echo $march;?>','<?php echo $april;?>','<?php echo $may;?>','<?php echo $june;?>','<?php echo $july;?>','<?php echo $august;?>','<?php echo $september;?>','<?php echo $october;?>','<?php echo $november;?>','<?php echo $december;?>'],
			monthNamesShort: ['<?php echo $jan;?>','<?php echo $feb;?>','<?php echo $mar;?>','<?php echo $apr;?>','<?php echo $may;?>','<?php echo $jun;?>','<?php echo $jul;?>','<?php echo $aug;?>','<?php echo $sep;?>','<?php echo $oct;?>','<?php echo $nov;?>','<?php echo $dec;?>'],
			dayNamesMin: ['<?php echo $sun;?>', '<?php echo $mon;?>', '<?php echo $tue;?>', '<?php echo $wed;?>', '<?php echo $thu;?>', '<?php echo $fri;?>', '<?php echo $sat;?>'],
			onSelect: function( selectedDate ) {
				var option = "minDate",
					instance = $( this ).data( "datepicker" ),
					date = $.datepicker.parseDate(
						instance.settings.dateFormat ||
						$.datepicker._defaults.dateFormat,
						selectedDate, instance.settings );
				dates.not( this ).datepicker( "option", option, date );

				$.ajax({
					type: 'POST',
					async: false,
					url: '/default/index/createdatesession',
					dataType: 'json',
					data: 	{ show_publish_date : selectedDate.replace(/\//g,'-') },
					success: function(data) {
						if(data === true)
							window.location = '/default/indexold_indexold/index';
					}
				});
				

				
			}			
   });
}
 
</script>
