$(document).ready(function(){
        $.validator.addMethod("time24", function(value, element) { 
            return /^([01]?[0-9]|2[0-3])(:[0-5][0-9]){2}$/.test(value);
        }, "Formato de hora no v\xE1lida.");
        /*WIZARD*/
        count=0;
        //step number
        $(".step_containers").each(function(){
                count=count+1;
        });
        
        $(".wizard_step_bar").each(function(i) {
                $(this).attr('id', 'bar_step_'+i);                                
        });        
        
        $(".step_containers").each(function(i) {
                //div that wraps a step div
                $(this).wrap("<div class='row-fluid'></div>");
                $(this).wrap("<div id='step" + i + "' class='col-md-12'></div>");
                //div where next and previous buttons are placed
                $(this).append("<div id='step" + i + "commands'></div>");        
                                
                if (i == 0) {
                        createNextButton(i);
                        selectStep(i);
                }else {                        
                        $("#step" + i).addClass('hide');
                        createPrevButton(i);
                        createNextButton(i);
                }
        });                
        /*END WIZARD*/
        
        //re calculate the section tree height
        setSectionTreeHeight();
        
        //step on bar
        $('[id^="bar_step_"]').each(function(){
                $(this).bind("click", function(e) {                        
                        if(this.id.replace('bar_step_','')!='beg'){
                                //BASIC                        
                                j = parseInt(this.id.replace('bar_step_',''));
                                if($('#frmSection').valid())
                                {                
                                        $("#error_container").addClass('hide');                                        
                                        if(j==0){                                
                                                //0, 2, 3
                                                $("#step"+j).addClass('hide');
                                                $("#step"+(j+2)).addClass('hide');
                                                $("#step"+(j+3)).addClass('hide');
                                                
                                        }else if(j==1){
                                                //0, 1 , 3
                                                $("#step"+j).addClass('hide');
                                                $("#step"+(j-1)).addClass('hide');
                                                $("#step"+(j+2)).addClass('hide');
                                                
                                        }else if(j==2){        
                                                //0, 1, 2
                                                $("#step"+j).addClass('hide');
                                                $("#step"+(j-2)).addClass('hide');
                                                $("#step"+(j-1)).addClass('hide');
                                                
                                        }                                        
                                        addNextButton(j,"step" + (j-1));
                                        selectStep(j + 1);
                                }
                        }else{
                                j = 1;                                
                                $("#step" + j).addClass('hide');
                                $("#step"+(j+1)).addClass('hide');
                                $("#step"+(j+2)).addClass('hide');                                
                                $("#step" + (j - 1)).removeClass('hide');                                
                                selectStep(j - 1);
                        }                        
                });                
        });        
        
        //cancel button
        $("#cancel_button").bind('click',function(){
                window.location = "/core/section_section/index";
        });      
		
		$(".btn-group > .btn").click(function(){
			$(this).addClass("current").siblings().removeClass("active");
		});  
        
        //save section
        $('#submit_button').bind('click', function() {                
                if($("#frmSection").valid()){                        
                        $.ajax({
                                type: 'POST',
                                async: false,
                                url: '/core/section_section/save',
                                dataType: 'json',
                                data:         $( "#frmSection" ).serialize(),
                                success: function(data) {                                                                        
                                        if(data['serial']){
                                                $('#cms_container').load("/core/section_section/sectiondetails", {
                                                        id: data['serial'],
                                                        is_section_temp: data['section_temp']
                                                },function(){                                                        
                                                        $('#section_tree_container').load("/core/section_section/sectionstreedata", {
                                                                
                                                        }, function() {
                                                                //show article button
                                                                $('#article_option').removeClass('hide');
                                                                //show section button
                                                                $('#section_option').removeClass('hide');
                                                                //show section options
                                                                $('#content_option').removeClass('hide');
                                                                $('#content_link_option').removeClass('hide');
                                                                
                                                                open_section(data['serial']);
                                                                $( 'html, body' ).animate( {scrollTop: 0}, 0 );
                                                                $.getScript('/js/modules/core/section/sectionlist.js');
                                                                $.getScript('/js/modules/core/section/sectiondetails.js');
                                                        });                                                                                                                
                                                });
                                        }
                                }                                                                
                        });
                }                        
        });
        
      });

/*WIZARD FUNCTIONS*/
function createNextButton(i) {
        var stepName = "step" + i;        
        if(i == 0){
                //step basic                
                $("#frmSection").validate({
                        wrapper: "span",
                        onfocusout: false,
                        onkeyup: false,
                        rules: {
                                internal_name: {
                                        required: true,
                                        remote:{
                                                url: "/core/section_section/checkinternalname",
                                                type: "POST"                                                
                                        }
                                },
                                title: {
                                        required: true
                                },
                                link: {
                                        required: true
                                },
                                area: {
                                        required: true
                                },                        
                                homepage: {
                                        required: true
                                }                                                                                
                        },
         messages:{
                 internal_name:{
                         remote: $("#repeated_section_name").val()
                 }
         }
                });
                
                $('textarea').expandingTextArea();
                
                if (CKEDITOR.instances['synopsis']) {
                        CKEDITOR.remove(CKEDITOR.instances['synopsis']);
                }
                
                $('#synopsis').ckeditor({
                        toolbar :                
                        [
                                {name: 'clipboard', items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ]},
                                {name: 'editing', items : [ 'SelectAll','-','SpellChecker', 'Scayt' ]},
                                {name: 'basicstyles', items : [ 'Bold','Italic','Underline','-','RemoveFormat' ]},
                                {name: 'paragraph', items : [ 'Outdent','Indent','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock' ]},
                                {name: 'tools', items : [ 'About' ]}
                        ]
                });
                
                //copying title into internal name
                $('#title').keyup(function(){
                        $('#internal_name').val($(this).val());
                });
                
                //external link and target
                $('input[id^="link-"]').each(function(){
                        $("#target").val("self");
                        $(this).click(function(){
                                $("#link").val(this.id.replace('link-',''));
                                if(this.id.replace('link-','')=='yes'){
                                        $('#link_container').removeClass("hide");
                                        $('#link_container').css('display','block');                                        
                                        $("#external_link").rules("add", {
                                                 required: true,
                                                 url: true
                                                });                        
                                        $("#target").rules("add", {
                                                 required: true
                                        });
                                        
                                        $("[id^='target_']").each(function(){                
                                                $(this).bind('click',function(){                        
                                                        $("#target").val(this.id.replace('target_',''));
                                                });
                                        });        
                                        
                                }else{
                                        $('#link_container').css('display','none');                                
                                        $("#external_link").rules("remove");
                                        $("#external_link").val('');
                                        $("#target").rules("remove");
                                        $("#target").val("self");
                                        
                                        $("[id^='target_']").each(function(){                
                                                if($(this).hasClass('active'))
                                                        $(this).removeClass('active');
                                        });
                                }
                        });
                });        
                
                
                //menu
                if($('#area option:selected').attr('type')=='variable'){                        
                        if(!$('#parent_show_menu').val() || $('#parent_show_menu').val()=='yes'){
                                $('#menu').val('yes');
                                $('#menu-yes').addClass('active');
                                $("#menu").rules("add", {
                                         required: true
                                });                                                                                        
                        }else{
                                $('#menu').val('no');
                                if($('#menu_opt_container').css('display')!='none'){
                                        $('#menu_opt_container').addClass('hide');
                                }
                        }       
                        
                        if(!$('#parent_show_menu2').val() || $('#parent_show_menu2').val()=='yes'){
                                $('#menu2').val('yes');
                                $('#menu2-yes').addClass('active');
                                $("#menu2").rules("add", {
                                         required: true
                                });                                                                                        
                        }else{
                                $('#menu2').val('no');
                                if($('#menu2_opt_container').css('display')!='none'){
                                        $('#menu2_opt_container').addClass('hide');
                                }
                        }
                }                

                $("#area").bind("change",function(){
                        if($('#area option:selected').attr('type')=='variable'){
                                
                                if(!$('#parent_show_menu').val() || $('#parent_show_menu').val()=='yes'){                        
                                        $('#menu_opt_container').removeClass("hide");
                                        $('#menu_opt_container').removeClass('hide');
                                        $('#menu').val('');                        
                                        $("#menu").rules("add", {
                                                 required: true
                                        });        
                                        
                                        $("[id^='menu-']").each(function(){                
                                                if($(this).hasClass('active'))
                                                        $(this).removeClass('active');
                                        });                                
                                }else{
                                        $('#menu').val('no');
                                }
                                
                                if(!$('#parent_show_menu2').val() || $('#parent_show_menu2').val()=='yes'){                        
                                        $('#menu2_opt_container').removeClass("hide");
                                        $('#menu2_opt_container').removeClass('hide');
                                        $('#menu2').val('');                        
                                        $("#menu2").rules("add", {
                                                 required: true
                                        });        
                                        
                                        $("[id^='menu2-']").each(function(){                
                                                if($(this).hasClass('active'))
                                                        $(this).removeClass('active');
                                        });                                
                                }else{
                                        $('#menu2').val('no');
                                }
                                
                        }else{
                                if(!$('#parent_show_menu').val() || $('#parent_show_menu').val()=='yes'){
                                        if($('#menu_opt_container').css('display')!='none'){
                                                $('#menu_opt_container').addClass('hide');
                                        }
                                        
                                        $('#menu').val('no');
                                        $("#menu").rules("remove");
                                        
                                        $("[id^='menu-']").each(function(){                
                                                if($(this).hasClass('active'))
                                                        $(this).removeClass('active');
                                        });
                                }else{
                                        $('#menu').val('no');
                                }
                                
                                if(!$('#parent_show_menu2').val() || $('#parent_show_menu2').val()=='yes'){
                                        if($('#menu2_opt_container').css('display')!='none'){
                                                $('#menu2_opt_container').addClass('hide');
                                        }
                                        
                                        $('#menu2').val('no');
                                        $("#menu2").rules("remove");
                                        
                                        $("[id^='menu2-']").each(function(){                
                                                if($(this).hasClass('active'))
                                                        $(this).removeClass('active');
                                        });
                                }else{
                                        $('#menu2').val('no');
                                }
                        }                        
                });
                
                $('input[id^="menu-"]').each(function(){                
                        $(this).click(function(){
                                $("#menu").val(this.id.replace('menu-',''));                        
                        });
                });        
                
                 $('input[id^="menu2-"]').each(function(){                
                        $(this).click(function(){
                                $("#menu2").val(this.id.replace('menu2-',''));                        
                        });
                });
                
                //homepage
                $('input[id^="homepage-"]').each(function(){                
                        $(this).click(function(){
                                $("#homepage").val(this.id.replace('homepage-',''));                        
                        });
                });
        }
        
        if(i!=(count-1)){
                //next step
                $("#" + stepName + "commands").append("<a href='#' id='" + stepName + "Next' class='btn btn-primary next'>"+next_step+"</a>");
        }

        //actions when next buttons are clicked
        $("#" + stepName + "Next").bind("click", function(e) {                
                if(i==0){
                        //BASIC                                                                                
                        if($('#frmSection').valid()){                                
                                $("#error_container").addClass('hide');
                                addNextButton(i,stepName);
                                selectStep(i + 1);
                        }else{
                                $("#error_container").removeClass('hide');
                        }
                }else{
                        addNextButton(i,stepName);
                        selectStep(i + 1);
                }                        
        });                
}

function addNextButton(i, stepName){
        $("#" + stepName).addClass('hide');
        $("#step" + (i + 1)).removeClass('hide');        
        
        $("[id^='type_']").each(function(){                
                $(this).bind('click',function(){                        
                        $("#type").val(this.id.replace('type_',''));
                });
        });
        
        $("[id^='btn_publish_']").each(function(){                
                $(this).bind('click',function(){                        
                        $("#show_publish_date").val(this.id.replace('btn_publish_',''));
                });
        });        
        
        $("[id^='feature_']").each(function(){                
                $(this).bind('click',function(){                        
                        $("#feature").val(this.id.replace('feature_',''));
                });
        });        
        
        $("[id^='highlight_']").each(function(){                
                $(this).bind('click',function(){                        
                        $("#highlight").val(this.id.replace('highlight_',''));
                });
        });        
        
        $("[id^='comments_']").each(function(){                
                $(this).bind('click',function(){                        
                        $("#comments").val(this.id.replace('comments_',''));
                });
        });
        
        $("[id^='rss_']").each(function(){                
                $(this).bind('click',function(){                        
                        $("#rss_available").val(this.id.replace('rss_',''));
                });
        });        

        //step publication dates
        //calendars
        if($("input[type=text][id=publish_date]").length > 0 && $("input[type=text][id=expire_date]").length > 0)
        {
                setDefaultCalendar($('#publish_date'),$('#expire_date'));
                /*
                  * // PRENDER CUANDO SE AUMENTE CAMPOS DE HORA
                  $("#hora_inicio").rules("add", {
				 time24: true
			});
                 $("#hora_fin").rules("add", {
				 time24: true
			});*/
        }

        $('[id^="img_"]').each(function(){                
                $("#hdnNameFile_"+this.id.replace("img_","")).rules("add", {                                
                         accept: "jpg,png,gif,jpeg"
                });
                
                element_sufix = this.id.replace("img_","");
                load_picture(element_sufix);
                
        });
        
        //re calculate the section tree height
        setSectionTreeHeight();
}

function createPrevButton(i) {
        var stepName = "step" + i;
        $("#" + stepName + "commands").append("<a href='#' id='" + stepName + "Prev' class='btn btn-primary prev'>"+back_step+"</a>");                

        $("#" + stepName + "Prev").bind("click", function() {                                        
                $("#error_container").addClass('hide');
                $("#" + stepName).addClass('hide');
                $("#step" + (i - 1)).removeClass('hide');                
                selectStep(i - 1);                        
        });
}

function selectStep(i) {
        if(i==0)
        {                
                $("#bar_step_0").removeClass('active');
                $("#bar_step_1").removeClass('active');
                $("#bar_step_2").removeClass('active');
                //0
                $("#bar_step_beg").addClass("active");
                
        }else if(i==1){
                //beg, 1, 2                
                $("#bar_step_beg").removeClass("active");
                $("#bar_step_" + i).removeClass('active');
                $("#bar_step_" + (i+1)).removeClass('active');
                //0
                $("#bar_step_" + (i-1)).addClass("active");
                
        }else if(i==2){
                //beg, 0, 2
                $("#bar_step_beg").removeClass("active");
                $("#bar_step_" + (i-2)).removeClass('active');
                $("#bar_step_" + i).removeClass('active');
                //1
                $("#bar_step_" + (i-1)).addClass("active");
                
        }else{
                //beg, 0, 1
                $("#bar_step_beg").removeClass('active');
                $("#bar_step_" + (i-3)).removeClass('active');
                $("#bar_step_" + (i-2)).removeClass('active');                                
                //2
                $("#bar_step_" + (i-1)).addClass("active");
        }
}

//uploads a section picture                        
function load_picture(element_sufix)
{
        new AjaxUpload('#img_'+element_sufix,{//UPLOADS FILE TO THE $_FILES VAR
                action: "/core/section_section/uploadfile",
                data:{
                        directory: 'public/uploads/tmp/',
                        maxSize: 2097152
                },
                name: 'section_photos',
                onSubmit : function(file, ext){
                        this.disable();
                },
                onComplete: function(file, response){//ONCE THE USER SELECTS THE FILE
                        this.enable();
                        if(isNaN(response)){//IF THE RESPONSE OF uploadFile.rpc ITS NOT A NUMBER (NOT AN ERROR)
                                //DELETING PREVIOUS PICTURE IF IT EXISTS
                                if($("#hdnNameFile_"+element_sufix).val()){
                                        $.ajax({
                                                url: "/core/section_section/deletetemppicture",
                                                type: "post",
                                                data: ({
                                                        file_tmp: function(){
                                                                return $("#hdnNameFile_"+element_sufix).val();
                                                        }
                                                }),
                                                success: function(data) {
                                                }
                                        });
                                }                                                                
                                $('#imageprw_'+element_sufix).attr('src', "/uploads/tmp/"+response);
                                $('#imageprw_'+element_sufix).removeClass('hide');                                                                                
                                $('#fileLabel_'+element_sufix).val(file);
                                $('#hdnNameFile_'+element_sufix).val(response);
                                $("#del_img_"+element_sufix).removeClass('hide');
                                
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