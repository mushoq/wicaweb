$(document).ready(function() {
	//Sortable banner list
	$("#sortable").sortable(
			{
			handle: ".handler",
			axis: "y",
			cursor: "move"
			}
	);	
	$("#sortable").disableSelection();

	//New banner action
	$("#new_product").bind("click",function(){
		$('#cms_container').load("/products/products/new", {
			id : $("#section_id").val()
		},function(){

			if (CKEDITOR.instances['description']) {
				CKEDITOR.remove(CKEDITOR.instances['description']);
			}
			
			$('#description').ckeditor({ 
				toolbar :		
				[
					{name: 'clipboard', items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ]},
					{name: 'editing', items : [ 'SelectAll','-','SpellChecker', 'Scayt' ]},
					{name: 'basicstyles', items : [ 'Bold','Italic','Underline','-','RemoveFormat' ]},
					{name: 'paragraph', items : [ 'Outdent','Indent','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock' ]},
					{name: 'tools', items : [ 'About' ]}
				]
			});
						
			//Validate	
			$("#frmProducts").validate({
		        wrapper: "span",
		        onfocusout: false,
		        onkeyup: false,
		        rules: {
		                name: {
		                 	required: true,
		                 	remote: {
		                 		url: "/products/products/validateproductname",
		                         type: "post",
		                         async:false,
		                         data: {
		 	                        name: function() {
		 	                          return $("#name").val();
		 	                        }
		                         }		                 	
		                 	 }
		                }
		        	},
                  messages:{
	                    name:{
	                     	remote: productdescription_remote_message
	                    }
	              }
		    });
			
			//available
			$('input[id^="available-"]').each(function(){		
				$(this).click(function(){
					$("#available").val(this.id.replace('available-',''));			
				});
			});
			//status
			$('input[id^="status-"]').each(function(){		
				$(this).click(function(){
					$("#status").val(this.id.replace('status-',''));			
				});
			});
			//feature
			$('input[id^="feature-"]').each(function(){		
				$(this).click(function(){
					$("#feature").val(this.id.replace('feature-',''));			
				});
			});
			
			//Add validation rule
			$("#product_hdnNameFile").rules("add", {				 
				 accept: "jpg,png,gif,jpeg"
			});
			//Get image id
			load_picture('product');
					
			//add products
		    $("#anchor_add_product").fancybox();
		    
		    $("#add_feature").bind("click",function(){		    	
				$('#products_container').load("/products/products/loadproductfeature",{
					
				},function(){
					
					var div_with = $(window).width()*0.85;
					$('#products_container').attr('style','width: '+div_with+'px;');

					$("#btnAddproduct").show();
					$("#btnEditproduct").hide();	
					
					//VALIDATION
					$('#product_form').validate({
						rules: {
							product_code:{
								required:true
							},
							product_price:{
								required:true,
								number:true
							},
							product_price_sale:{
								number:true
							},
							product_weight:{
								number:true
							}
							
						}
					});
					
					$("#catalog_hdnNameFile").rules("add", {				 
						 accept: "jpg,png,gif,jpeg"
					});
					
					load_picture('catalog');
					
					$("#btnAddproduct").bind("click",function(){
						
						var equal_description=0;
						$("#repeated_product_code").hide();
						$("[id^='hdn_product_code_']").each(function(){
							if($('#product_code').val() == $(this).val() && $(this).attr('id')!='hdn_product_code_'+index)
								equal_description=1;
						});

						if(equal_description==0)
						{
							if($('#product_form').valid())
							{
								$("#tmp_products tbody").append('<tr id="tmp_product_'+count+'">'+
												'<td id="tmp_product_code_'+count+'" name="tmp_product_code_'+count+'">'+$('#product_code').val()+'</td>'+
												'<td id="tmp_product_description_'+count+'" name="tmp_product_description_'+count+'">'+$('#product_description').val()+'</td>'+
												'<td id="tmp_product_price_'+count+'" name="tmp_product_price_'+count+'">'+$('#product_price').val()+'</td>'+
												'<td id="tmp_product_price_sale_'+count+'" name="tmp_product_price_sale_'+count+'">'+$('#product_price_sale').val()+'</td>'+
												'<td id="actions_tmp_product_'+count+'" name="actions_tmp_product_'+count+'"><i id="edit_element_'+count+'" element="'+count+'" class="pointer icon-pencil" title="Edit"></i>|<i id="remove_element_'+count+'" element="'+count+'" class="pointer icon-trash" title="Delete"></i></td>'+
											'</tr>');  

								$("#hdn_products").append('<div id="hdn_product_'+count+'">'+
												'<input type="hidden" id="hdn_product_code_'+count+'" name="hdn_product_code_['+count+']" value="'+$('#product_code').val()+'" />'+
												'<input type="hidden" id="hdn_product_description_'+count+'" name="hdn_product_description_['+count+']" value="'+$('#product_description').val()+'" />'+
												'<input type="hidden" id="hdn_product_price_'+count+'" name="hdn_product_price_['+count+']" value="'+$('#product_price').val()+'" />'+
												'<input type="hidden" id="hdn_product_price_sale_'+count+'" name="hdn_product_price_sale_['+count+']" value="'+$('#product_price_sale').val()+'" />'+
												'<input type="hidden" id="hdn_product_weight_'+count+'" name="hdn_product_weight_['+count+']" value="'+$('#product_weight').val()+'" />'+
												'<input type="hidden" id="hdn_product_Filelabel_'+count+'" name="hdn_product_Filelabel_['+count+']" value="'+$('#catalog_fileLabel').val()+'" />'+
												'<input type="hidden" id="hdn_product_hdnNameFile_'+count+'" name="hdn_product_hdnNameFile_['+count+']" value="'+$('#catalog_hdnNameFile').val()+'" />'+
												'<input type="hidden" id="hdn_product_file_img_'+count+'" name="hdn_product_file_img_['+count+']" value="'+$('#catalog_file_img').val()+'" />'+
												
											'</div>');
								
								//'<input type="hidden" id="hdn_product_id_img_'+count+'" name="hdn_product_id_img_['+count+']" value="'+$('#catalog_id_img').val()+'" />'+

								//remove element click event
								$("#remove_element_"+count).bind("click",function(){
									$("#tmp_product_"+$(this).attr("element")).remove();
									$("#hdn_product_"+$(this).attr("element")).remove();

									if($("#tmp_products tbody tr").length==0){
										$("#lbl_no_products").show();
										$("#tmp_products").hide();
									}
								});

								//EDIT
								$("[id^='edit_element_']").each(function(){
									var div_with = $(window).width()*0.85;
									$('#products_container').attr('style','width: '+div_with+'px;');                    
									$(this).bind("click",function(){
										$("#btnEditproduct").show();
										$("#btnAddproduct").hide();

										var index = $(this).attr('element');
										$("#product_code").val($("#hdn_product_code_"+index).val());
										$("#product_description").val($("#hdn_product_description_"+index).val());
										$("#product_price").val($("#hdn_product_price_"+index).val());
										$("#product_price_sale").val($("#hdn_product_price_sale_"+index).val());
										$("#product_weight").val($("#hdn_product_weight_"+index).val());
										$("#product_image").val($("#hdn_product_image_"+index).val());
										$('#catalog_fileLabel').val($("#hdn_product_Filelabel_"+index).val());
										$('#catalog_hdnNameFile').val($("#hdn_product_hdnNameFile_"+index).val());
										$('#catalog_file_img').val($("#hdn_product_file_img_"+index).val());
										$('#catalog_id').val($("#hdn_product_id_"+index).val());
										if($('#hdn_product_hdnNameFile_'+index).val())
										$('#catalog_imageprw').attr('src', "/uploads/tmp/"+$('#hdn_product_hdnNameFile_'+index).val());
										
										$("#anchor_add_product").click();
										
										$("#btnEditproduct").bind("click",function(){
											
											var equal_description=0;
											$("#repeated_product_code").hide();
											$("[id^='hdn_product_code_']").each(function(){
												if($('#product_code').val() == $(this).val() && $(this).attr('id')!='hdn_product_code_'+index)
													equal_description=1;
											});

											if(equal_description==0)
											{
												if($('#product_form').valid()){

													$("#tmp_product_code_"+index).html($('#product_code').val());
													$("#tmp_product_description_"+index).html($('#product_description').val());
													$("#tmp_product_price_"+index).html($('#product_price').val());
													$("#tmp_product_weight_"+index).html($('#product_weight').val());

													$("#hdn_product_code_"+index).val($('#product_code').val());
													$("#hdn_product_description_"+index).val($('#product_description').val());
													$("#hdn_product_price_"+index).val($('#product_price').val());
													$("#hdn_product_price_sale_"+index).val($('#product_price_sale').val());
													$("#hdn_product_weight_"+index).val($('#product_weight').val());													
													$("#hdn_product_Filelabel_"+index).val($('#catalog_fileLabel').val());
													$("#hdn_product_hdnNameFile_"+index).val($('#catalog_hdnNameFile').val());
													$("#hdn_product_file_img_"+index).val($('#catalog_file_img').val());
													$("#hdn_product_id_"+index).val($('#catalog_id').val());
													$.fancybox.close();
												}
											}else{
												$("#repeated_product_code").show();
											}
										});                

									});
								});
								count++;
								//show table
								$("#tmp_products").show();
								$("#lbl_no_products").hide();
								$.fancybox.close();
							}
						}else{
							$("#repeated_product_code").show();
						}
					});
					
					//remove element click event
					$("[id^='remove_element_']").each(function(){
						$(this).bind("click",function(){
							$("#tmp_product_"+$(this).attr("element")).remove();
							$("#hdn_product_"+$(this).attr("element")).remove();

							if($("#tmp_products tbody tr").length==0){
								$("#lbl_no_products").show();
								$("#tmp_products").hide();
							}
						});
					});
					
					var count=1;
					$("[id^='tmp_product_code_']").each(function(){
						count++;
					});
					
					$("#anchor_add_product").click();
					
				});
			});
					    			
			//submit button
		    $('#submit_button').bind('click',function(){
		    	if($("#frmProducts").valid())
		    	{
					
		    		//ajax save
					$.ajax({
						type: 'POST',
						async: false,
						url: '/products/products/save',
						dataType: 'json',
						data: 	$( "#frmProducts" ).serialize(),
						success: function(data) {	
							if(data['section_id']){
								$('#cms_container').load("/products/products/index", {
									id: data['section_id']
								},function(){							
									$.getScript('/js/modules/products/products/products.js');				
								});
							}
						}								
					});		    		
		    	}
		    });
		});
	});	
	
	//Edit Banner 
	$('[id^="edit_product_"]').each(function() {
		$(this).bind('click', function() {
			//mark_edit_section_selected($(this));
			$('#cms_container').load("/products/products/edit", {
				product_id: this.id.replace('edit_product_',''),
				section_id: $('#section_id').val()
			},function(){
				 if($("#tmp_products tbody tr").length>0){
					$("#lbl_no_products").hide();
			     	$("#tmp_products").show();
				 }else if($("#tmp_products tbody tr").length==0){
				    $("#lbl_no_products").show();
				    $("#tmp_products").hide();   
				 }
				 
				 if (CKEDITOR.instances['description']) {
						CKEDITOR.remove(CKEDITOR.instances['description']);
					}
					
					$('#description').ckeditor({ 
						toolbar :		
						[
							{name: 'clipboard', items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ]},
							{name: 'editing', items : [ 'SelectAll','-','SpellChecker', 'Scayt' ]},
							{name: 'basicstyles', items : [ 'Bold','Italic','Underline','-','RemoveFormat' ]},
							{name: 'paragraph', items : [ 'Outdent','Indent','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock' ]},
							{name: 'tools', items : [ 'About' ]}
						]
					});
								
					//Validate	
					$("#frmProducts").validate({
				        wrapper: "span",
				        onfocusout: false,
				        onkeyup: false,
				        rules: {
				                name: {
				                 	required: true,
				                 	remote: {
				                 		url: "/products/products/validateproductname",
				                         type: "post",
				                         async:false,
				                         data: {
				 	                        name: function() {
				 	                          return $("#name").val();
				 	                        },
				 	                        id: function() {
				 	                          return $("#id").val();
				 	                        },
				                         }		                 	
				                 	 }
				                }
				        	},
		                  messages:{
			                    name:{
			                     	remote: productdescription_remote_message
			                    }
			              }
				    });
					
					//available
					$('input[id^="available-"]').each(function(){		
						$(this).click(function(){
							$("#available").val(this.id.replace('available-',''));			
						});
					});
					//status
					$('input[id^="status-"]').each(function(){		
						$(this).click(function(){
							$("#status").val(this.id.replace('status-',''));			
						});
					});
					//feature
					$('input[id^="feature-"]').each(function(){		
						$(this).click(function(){
							$("#feature").val(this.id.replace('feature-',''));			
						});
					});
					
					//Add validation rule
					$("#product_hdnNameFile").rules("add", {				 
						 accept: "jpg,png,gif,jpeg"
					});
					//Get image id
					load_picture('product');
							
					//add products
				    $("#anchor_add_product").fancybox();
				    
				    //EDIT
					$("[id^='edit_element_']").each(function(){						
						                 
						$(this).bind("click",function(){
							var index = $(this).attr('element');
							
							$('#products_container').load("/products/products/loadproductfeature",{
								
							},function(){
								
								var div_with = $(window).width()*0.85;
								$('#products_container').attr('style','width: '+div_with+'px;');   
							
							$("#btnEditproduct").show();
							$("#btnAddproduct").hide();

							//var index = $(this).attr('element');
							$("#product_code").val($("#hdn_product_code_"+index).val());
							$("#product_description").val($("#hdn_product_description_"+index).val());
							$("#product_price").val($("#hdn_product_price_"+index).val());
							$("#product_price_sale").val($("#hdn_product_price_sale_"+index).val());
							$("#product_weight").val($("#hdn_product_weight_"+index).val());
							$("#product_image").val($("#hdn_product_image_"+index).val());
							$('#catalog_fileLabel').val($("#hdn_product_Filelabel_"+index).val());
							$('#catalog_hdnNameFile').val($("#hdn_product_hdnNameFile_"+index).val());
							$('#catalog_file_img').val($("#hdn_product_file_img_"+index).val());
							$('#catalog_id').val($("#hdn_product_id_"+index).val());
							if($('#hdn_product_hdnNameFile_'+index).val()){
								$('#catalog_imageprw').attr('src', "/uploads/tmp/"+$('#hdn_product_hdnNameFile_'+index).val());
								$('#catalog_imageprw').show();
							}
							else if($("#hdn_product_file_img_"+index).val()){
								$('#catalog_imageprw').attr('src', "/uploads/products/"+$("#hdn_product_file_img_"+index).val());
								$('#catalog_imageprw').show();
							}
							
							//VALIDATION
							$('#product_form').validate({
								rules: {
									product_code:{
										required:true
									},
									product_price:{
										required:true,
										number:true
									},
									product_price_sale:{
										number:true
									},
									product_weight:{
										number:true
									}
									
								}
							});
							
							$("#catalog_hdnNameFile").rules("add", {				 
								 accept: "jpg,png,gif,jpeg"
							});
							
							load_picture('catalog');
							
							$("#anchor_add_product").click();
							
							$("#btnEditproduct").bind("click",function(){
								
								var equal_description=0;
								$("#repeated_product_code").hide();
								$("[id^='hdn_product_code_']").each(function(){
									if($('#product_code').val() == $(this).val() && $(this).attr('id')!='hdn_product_code_'+index)
										equal_description=1;
								});

								if(equal_description==0)
								{
									if($('#product_form').valid()){

										$("#tmp_product_code_"+index).html($('#product_code').val());
										$("#tmp_product_description_"+index).html($('#product_description').val());
										$("#tmp_product_price_"+index).html($('#product_price').val());
										$("#tmp_product_weight_"+index).html($('#product_weight').val());

										$("#hdn_product_code_"+index).val($('#product_code').val());
										$("#hdn_product_description_"+index).val($('#product_description').val());
										$("#hdn_product_price_"+index).val($('#product_price').val());
										$("#hdn_product_price_sale_"+index).val($('#product_price_sale').val());
										$("#hdn_product_weight_"+index).val($('#product_weight').val());													
										$("#hdn_product_Filelabel_"+index).val($('#catalog_fileLabel').val());
										$("#hdn_product_hdnNameFile_"+index).val($('#catalog_hdnNameFile').val());
										$("#hdn_product_file_img_"+index).val($('#catalog_file_img').val());
										$("#hdn_product_id_"+index).val($('#catalog_id').val());
										$.fancybox.close();
									}
								}else{
									$("#repeated_product_code").show();
								}
							});                

							});
						});
					});
				    
				    $("#add_feature").bind("click",function(){		    	
						$('#products_container').load("/products/products/loadproductfeature",{
							
						},function(){
							
							var div_with = $(window).width()*0.85;
							$('#products_container').attr('style','width: '+div_with+'px;');

							$("#btnAddproduct").show();
							$("#btnEditproduct").hide();	
							
							//VALIDATION
							$('#product_form').validate({
								rules: {
									product_code:{
										required:true
									},
									product_price:{
										required:true,
										number:true
									},
									product_price_sale:{
										number:true
									},
									product_weight:{
										number:true
									}
									
								}
							});
							
							$("#catalog_hdnNameFile").rules("add", {				 
								 accept: "jpg,png,gif,jpeg"
							});
							
							load_picture('catalog');
							
							$("#btnAddproduct").bind("click",function(){
								
								var equal_description=0;
								$("#repeated_product_code").hide();
								$("[id^='hdn_product_code_']").each(function(){
									if($('#product_code').val() == $(this).val() && $(this).attr('id')!='hdn_product_code_'+index)
										equal_description=1;
								});

								if(equal_description==0)
								{
									if($('#product_form').valid())
									{
										$("#tmp_products tbody").append('<tr id="tmp_product_'+count+'">'+
														'<td id="tmp_product_code_'+count+'" name="tmp_product_code_'+count+'">'+$('#product_code').val()+'</td>'+
														'<td id="tmp_product_description_'+count+'" name="tmp_product_description_'+count+'">'+$('#product_description').val()+'</td>'+
														'<td id="tmp_product_price_'+count+'" name="tmp_product_price_'+count+'">'+$('#product_price').val()+'</td>'+
														'<td id="tmp_product_price_sale_'+count+'" name="tmp_product_price_sale_'+count+'">'+$('#product_price_sale').val()+'</td>'+
														'<td id="actions_tmp_product_'+count+'" name="actions_tmp_product_'+count+'"><i id="edit_element_'+count+'" element="'+count+'" class="pointer icon-pencil" title="Edit"></i>|<i id="remove_element_'+count+'" element="'+count+'" class="pointer icon-trash" title="Delete"></i></td>'+
													'</tr>');  

										$("#hdn_products").append('<div id="hdn_product_'+count+'">'+
														'<input type="hidden" id="hdn_product_code_'+count+'" name="hdn_product_code_['+count+']" value="'+$('#product_code').val()+'" />'+
														'<input type="hidden" id="hdn_product_description_'+count+'" name="hdn_product_description_['+count+']" value="'+$('#product_description').val()+'" />'+
														'<input type="hidden" id="hdn_product_price_'+count+'" name="hdn_product_price_['+count+']" value="'+$('#product_price').val()+'" />'+
														'<input type="hidden" id="hdn_product_price_sale_'+count+'" name="hdn_product_price_sale_['+count+']" value="'+$('#product_price_sale').val()+'" />'+
														'<input type="hidden" id="hdn_product_weight_'+count+'" name="hdn_product_weight_['+count+']" value="'+$('#product_weight').val()+'" />'+
														'<input type="hidden" id="hdn_product_Filelabel_'+count+'" name="hdn_product_Filelabel_['+count+']" value="'+$('#catalog_fileLabel').val()+'" />'+
														'<input type="hidden" id="hdn_product_hdnNameFile_'+count+'" name="hdn_product_hdnNameFile_['+count+']" value="'+$('#catalog_hdnNameFile').val()+'" />'+
														'<input type="hidden" id="hdn_product_file_img_'+count+'" name="hdn_product_file_img_['+count+']" value="'+$('#catalog_file_img').val()+'" />'+
														
													'</div>');
										
										//'<input type="hidden" id="hdn_product_id_img_'+count+'" name="hdn_product_id_img_['+count+']" value="'+$('#catalog_id_img').val()+'" />'+

										//remove element click event
										$("#remove_element_"+count).bind("click",function(){
											$("#tmp_product_"+$(this).attr("element")).remove();
											$("#hdn_product_"+$(this).attr("element")).remove();

											if($("#tmp_products tbody tr").length==0){
												$("#lbl_no_products").show();
												$("#tmp_products").hide();
											}
										});

										//EDIT
										$("[id^='edit_element_']").each(function(){
											var div_with = $(window).width()*0.85;
											$('#products_container').attr('style','width: '+div_with+'px;');                    
											$(this).bind("click",function(){
												$("#btnEditproduct").show();
												$("#btnAddproduct").hide();

												var index = $(this).attr('element');
												$("#product_code").val($("#hdn_product_code_"+index).val());
												$("#product_description").val($("#hdn_product_description_"+index).val());
												$("#product_price").val($("#hdn_product_price_"+index).val());
												$("#product_price_sale").val($("#hdn_product_price_sale_"+index).val());
												$("#product_weight").val($("#hdn_product_weight_"+index).val());
												$("#product_image").val($("#hdn_product_image_"+index).val());
												$('#catalog_fileLabel').val($("#hdn_product_Filelabel_"+index).val());
												$('#catalog_hdnNameFile').val($("#hdn_product_hdnNameFile_"+index).val());
												$('#catalog_file_img').val($("#hdn_product_file_img_"+index).val());
												$('#catalog_id').val($("#hdn_product_id_"+index).val());
												if($('#hdn_product_hdnNameFile_'+index).val())
												$('#catalog_imageprw').attr('src', "/uploads/tmp/"+$('#hdn_product_hdnNameFile_'+index).val());
												
												$("#anchor_add_product").click();
												
												$("#btnEditproduct").bind("click",function(){
													
													var equal_description=0;
													$("#repeated_product_code").hide();
													$("[id^='hdn_product_code_']").each(function(){
														if($('#product_code').val() == $(this).val() && $(this).attr('id')!='hdn_product_code_'+index)
															equal_description=1;
													});

													if(equal_description==0)
													{
														if($('#product_form').valid()){

															$("#tmp_product_code_"+index).html($('#product_code').val());
															$("#tmp_product_description_"+index).html($('#product_description').val());
															$("#tmp_product_price_"+index).html($('#product_price').val());
															$("#tmp_product_weight_"+index).html($('#product_weight').val());

															$("#hdn_product_code_"+index).val($('#product_code').val());
															$("#hdn_product_description_"+index).val($('#product_description').val());
															$("#hdn_product_price_"+index).val($('#product_price').val());
															$("#hdn_product_price_sale_"+index).val($('#product_price_sale').val());
															$("#hdn_product_weight_"+index).val($('#product_weight').val());													
															$("#hdn_product_Filelabel_"+index).val($('#catalog_fileLabel').val());
															$("#hdn_product_hdnNameFile_"+index).val($('#catalog_hdnNameFile').val());
															$("#hdn_product_file_img_"+index).val($('#catalog_file_img').val());
															$("#hdn_product_id_"+index).val($('#catalog_id').val());
															$.fancybox.close();
														}
													}else{
														$("#repeated_product_code").show();
													}
												});                

											});
										});
										count++;
										//show table
										$("#tmp_products").show();
										$("#lbl_no_products").hide();
										$.fancybox.close();
									}
								}else{
									$("#repeated_product_code").show();
								}
							});
							
							//remove element click event
							$("[id^='remove_element_']").each(function(){
								$(this).bind("click",function(){
									$("#tmp_product_"+$(this).attr("element")).remove();
									$("#hdn_product_"+$(this).attr("element")).remove();

									if($("#tmp_products tbody tr").length==0){
										$("#lbl_no_products").show();
										$("#tmp_products").hide();
									}
								});
							});
							
							var count=1;
							$("[id^='tmp_product_code_']").each(function(){
								count++;
							});
							
							$("#anchor_add_product").click();
							
						});
					});
					//remove element click event
					$("[id^='remove_element_']").each(function(){
						$(this).bind("click",function(){
							$("#tmp_product_"+$(this).attr("element")).remove();
							$("#hdn_product_"+$(this).attr("element")).remove();

							if($("#tmp_products tbody tr").length==0){
								$("#lbl_no_products").show();
								$("#tmp_products").hide();
							}
						});
					});		    			
					//submit button
				    $('#submit_button').bind('click',function(){
				    	if($("#frmProducts").valid())
				    	{
							//ajax save
							$.ajax({
								type: 'POST',
								async: false,
								url: '/products/products/save',
								dataType: 'json',
								data: 	$( "#frmProducts" ).serialize(),
								success: function(data) {	
									if(data['section_id']){
										$('#cms_container').load("/products/products/index", {
											id: data['section_id']
										},function(){							
											$.getScript('/js/modules/products/products/products.js');				
										});
									}
								}								
							});		    		
				    	}
				    });
				 

			});
			
		});	
	});
    
	//Delete banner
	$('[id^="delete_product_"]').each(function() {
		$(this).bind('click', function() {
			$.ajax({
				type: 'POST',
				async: false,
				url: '/products/products/delete',
				dataType: 'json',
				data: 	{
					id: this.id.replace("delete_product_",""),
					section_id: $('#section_id').val()
				},
				success: function(data) {													
					if(data['serial'])
					{
						$('#cms_container').load("/products/products/index", {
							id: data['serial']
						}, function() {				
							$.getScript('/js/modules/products/products/products.js');
						});	
					}
				}								
			});			
		});	
	});	
	
	//save products order
	$('#save_order').bind('click', function() {		
		var products_list = $("#sortable").sortable("toArray");		
		$('#product_order').val(products_list.join(','));

		$.ajax({
			type: 'POST',
			async: false,
			url: '/products/products/saveorder',
			dataType: 'json',
			data: 	$( "#frmProductsOrder" ).serialize(),
			success: function(data) {
				if(data['serial'])
				{					
					$('#cms_container').load("/products/products/index", {
						id: data['serial']
					},function(){						
						$( 'html, body' ).animate( {scrollTop: 0}, 0 );
						$.getScript('/js/modules/products/products/products.js');
					});
				}
			}								
		});
	});
}); //END DOCUMENT ROOT


//uploads a banner picture			
function load_picture(element_prefix)
{	
	new AjaxUpload('#'+element_prefix+'_img',{//UPLOADS FILE TO THE $_FILES VAR
		action: "/products/products/uploadfile",
		data:{
			directory: 'public/uploads/tmp/',
			maxSize: 2097152
		},
		name: 'product_photos',
		onSubmit : function(file, ext){
			this.disable();
		},
		onComplete: function(file, response){//ONCE THE USER SELECTS THE FILE
			this.enable();
			if(isNaN(response)){//IF THE RESPONSE OF uploadFile.rpc ITS NOT A NUMBER (NOT AN ERROR)
				//DELETING PREVIOUS PICTURE IF IT EXISTS
				if($("#"+element_prefix+"_hdnNameFile").val()){
					$.ajax({
						url: "/products/products/deletetemppicture",
						type: "post",
						data: ({
							file_tmp: function(){
								return $("#"+element_prefix+"_hdnNameFile").val();
							}
						}),
						success: function(data) {
						}
					});
				}								
				$('#'+element_prefix+'_imageprw').attr('src', "/uploads/tmp/"+response);
				$('#'+element_prefix+'_imageprw').show();										
				$('#'+element_prefix+'_fileLabel').val(file);
				$('#'+element_prefix+'_hdnNameFile').val(response);
				//$("#'+element_prefix+'_del_img").show();
				
			}else{//ERRORS ON THE FILE UPLOADED
				if(response == 1){
					alert(max_size);
				}
				if(response == 2){
					alert(supported_extension);
				}
			}
		}
	});
}

