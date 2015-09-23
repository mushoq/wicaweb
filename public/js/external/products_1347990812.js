//$(document).ready(function(){
//	
//	$("#anchor_view_catalog").fancybox();
//	
//	$("[id^='catalog_prod_']").each(function(){
//		
//		var prod_id = $(this).attr("product_id");
//		$(this).bind("click",function(){
//			
////				var url = ""+window.location;
//			$("#wica_main_area").load("/products/products/viewcatalog", {
//				product_id: prod_id
//			},function(){
//				$("[id^='catalog_']").each(function(){
//					$(this).bind("click",function(){
//						$("#big_catalog_img").attr('src',$(this).attr('src'));
//						$("#features_section").html($(this).attr('description'));
//						$("#price_section").html($(this).attr('price'));
//					});
//				});
//				
//			});
//		});
//	});
//
//});
