$(document).ready(function() {
	mainmenu();
	
	$(".carousel").carousel({
		interval : 2000
	});

	$("#carousel_left").bind("click", function() {
		$(".carousel").carousel('prev');
	});

	$("#carousel_right").bind("click", function() {
		$(".carousel").carousel('next');
	});
});

function mainmenu() {
	$(" #nav ul ").css({
		display : "none"
	}); // Opera Fix
	$(" #nav li").hover(function() {
		$(this).find('ul:first').css({
			visibility : "visible",
			display : "none"
		}).show(400);
	}, function() {
		$(this).find('ul:first').css({
			visibility : "hidden"
		});
	});
}