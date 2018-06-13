function lvt(current) {    
    di = $(current).attr(d_i);
    if(typeof di !== 'undefined'){ 
        if($('body>div>#lvt'+di).length < 1){                        
            $('body>div').append('<div id="lvt'+di+'"></div>');
        }        
    }
}

function lvtok(current) {    
    setTimeout( function(){ 
        if($('body>div>#lvt'+current).length > 0){
            $('body>div>#lvt'+current).remove();            
            $('#mb'+current).click();
        } 
    }, 1200) 
}


function micon(current){
    m = $(current).attr('id');
    // $('.da-cmau').attr('class','')
    $('.cmau').removeClass('m-active')
    $(current).addClass('m-active')    
    $('.item.chicon').find('>div').attr('class',m + ' da-cmau');

    selectedSrc = $('.item.chicon.chosen-image').html()
    $(".selected-product-icon").html(selectedSrc);
    $('.selected-product-icon').attr('d-mau',m)
}


function cicon(obj){   
    data_e = $(obj).attr('data-e');

    if(typeof data_e === 'undefined'){
        data_e = 'editable';
    }

    selectedSrc = $(".selected-product-icon").html();

    if(isEmpty(selectedSrc)){
        return false
    }
  

    dt_n = $(".selected-product-icon").attr('dt-n')
    filename = $(".selected-product-icon").attr(d_lk)
    mau = $(".selected-product-icon").attr('d-mau')

    if(typeof dt_n !== 'undefined'){
        name_img = dt_n
    }else{
        name_img = d_postimg+'[]'
    }   
    if(filename != ''){
        
        $('#'+data_e).html('')
        $('#'+data_e).prepend('<li><input type="hidden" name="'+name_img+'" value="'+filename+'#'+mau+'" />'+selectedSrc+'<i class="js-remove">✖</i></li>');
        
    }           
    $(".selected-product-icon").html('');
}

function ca(obj){   
    data_e = $(obj).attr('data-e');

    if(typeof data_e === 'undefined'){
        data_e = 'editable';
    }

    selectedSrc = $(".selected-product-image").find("input").val();

    if(isEmpty(selectedSrc)){
        return false
    }

    sources = selectedSrc.split(","); 
    max = 0;
    checkone = 0;
    if($(".selected-product-image").hasClass('one')){
        checkone = 1;
        max = 1;
    }else{
        max = sources.length;    
    } 

    dt_n = $(".selected-product-image").attr('dt-n')
    if(typeof dt_n !== 'undefined'){
        name_img = dt_n
    }else{
        name_img = d_postimg+'[]'
    }

    for (var i = 0; i < max; i++) { 
        filename = sources[i].replace(/^.*[\\\/]/, '');
        filename = filename.replace(/\.[^/.]+$/, "");
        filename = filename.split("-");
        filename = filename[filename.length - 1];
        if(filename != ''){
            if(checkone == 1 && $('#'+data_e+' li').length > 0){
                $('#'+data_e).html('')
                $('#'+data_e).prepend('<li><input type="hidden" name="'+name_img+'" value="'+filename+'" /><img src="'+sources[i]+'"><i class="js-remove">✖</i></li>');
            }else{
                $('#'+data_e).prepend('<li><input type="hidden" name="'+name_img+'" value="'+filename+'" /><img src="'+sources[i]+'"><i class="js-remove">✖</i></li>');
            }
        }
    };        
    $(".selected-product-image").find("input").val('');
}

function chicon(obj){    
    // $('.da-cmau').attr('class','')

    var selectedSrc = $(obj).html();
    var value = $(obj).attr(d_lk);
    $('#list-icon .chosen-image').removeClass('chosen-image')          
    $(obj).addClass("chosen-image");            
    $(".selected-product-icon").html(selectedSrc);
    $(".selected-product-icon").attr(d_lk, value)
    
}


function chimg(obj){
    var selectedSrc = $(".selected-product-image").find("input").val();
    var src = $(obj).children("img").attr(d_lk);
    if ($(obj).hasClass("chosen-image")) {
        $(obj).removeClass("chosen-image");
        if (selectedSrc != "" && !selectedSrc.endsWith(",")) {
            selectedSrc = selectedSrc + ",";
        }
        selectedSrc = selectedSrc.replace(src + ",", "");
    } else {
        $(obj).addClass("chosen-image");
        selectedSrc += selectedSrc == "" ? src : "," + src;
    }
    if (selectedSrc.endsWith(",")) {
        selectedSrc = selectedSrc.substring(0, selectedSrc.length - 1);
    }
    $(".selected-product-image").find("input").val(selectedSrc);

    selectedSrc = $(".selected-product-image").find("input").val();
    
    if(selectedSrc == ''){
        $('.cke_dialog_title').html('Bạn chưa chọn ảnh nào');
        $('.gmisct').html('Bạn chưa chọn ảnh nào');
    }else{
        sources = selectedSrc.split(","); 
        $('.cke_dialog_title').html('Bạn đã chọn '+sources.length+' ảnh');
        $('.gmisct').html('Bạn đã chọn '+sources.length+' ảnh');
    }
}



function clk(current){
    cptclip($(current).attr(d_lk)); 
    // alert($(current).attr(d_lk));
}




function ehelpf() {    
    
    var top = $('.shelp').attr(d_tp);
    var left = $('.shelp').attr(d_lt);
    var width = $('.shelp').attr(d_wh);
    var height = $('.shelp').attr(d_ht);

    if(typeof top != 'undefined'){$('.shelp').css('top',top);}
    if(typeof left != 'undefined'){$('.shelp').css('left',left);}
    if(typeof width != 'undefined'){$('.shelp').css('width',width);}
    if(typeof height != 'undefined'){$('.shelp').css('height',height);}
    
    $('.shelp').removeAttr(d_tp);
    $('.shelp').removeAttr(d_lt);
    $('.shelp').removeAttr(d_wh);
    $('.shelp').removeAttr(d_ht);
    
    
    $('.shelp').removeClass('shelp');
    $('.hhelp').remove();
    // $('.khelp').remove();
    $('.bohelp').remove(); 
    
}

function bhelpf(current){   

    ehelpf();

    $.ajax({
            cache: false,
            url: window.location.href + 'itemhelp/view?st='+ $(current).attr(d_st) +'&gr='+ $(current).attr(d_gr),
            type: 'POST',               
            success: function (data) {   
                data = decodeURIComponent(data.replace(/\+/g, ' '));   
                Hel = data;  
                if(typeof Hel == 'undefined'){Hel = '';}
                bhelpf2(Hel);
            },
            error: function () {
                poploi();                    
            }
        });
}

function bhelpf2(data){    
    if(data != ''){        
        var act = data.split('|')[3]; 
        var foc = data.split('|')[1]; 
        var che = data.split('|')[2]; 
        var noi = data.split('|')[0];
        var stt = data.split('|')[4];
        var gro = data.split('|')[5];

        var ele = foc;
        $(act).trigger("click");

        // var ele = '#menu-top';    
        // var ele = '#pagecurrent button';
        // var ele = 'ul.pagination';
        // var ele = '#heado';
        // var ele = '.content-left';
        // var ele = '.gr tbody';
        // var ele = '.gr tbody tr:first-child';
        // var ele = '.gr tbody tr:first-child .omc .omd';

        // var ele = '.endgr .cas';    
        // $('.select-on-check-all').trigger( "click" );
        var current = $(ele);    
        do{
            $(current).addClass('phelp');
            $(current).addClass('phelp2'); 
            current = current.parent();
        }while(current.get(0).tagName != 'BODY')

        $('.phelp').removeClass('phelp');
        $('.phelp2').addClass('phelp'); 
        $('.phelp').removeClass('phelp2');

        
        // $(ele).show();
        var top = $(ele).offset().top;
        var left = $(ele).offset().left;

        var width = parseFloat(window.getComputedStyle($(ele).get(0)).width);
        var height = parseFloat(window.getComputedStyle($(ele).get(0)).height);

        $(ele).attr(d_tp,$(ele).css('top'));
        $(ele).attr(d_lt,$(ele).css('left'));
        $(ele).attr(d_wh,width);
        $(ele).attr(d_ht,height);
        
        $('.khelp').remove();
        $(ele).addClass('shelp').css('top',top).css('left',left).css('width',width).css('height',height).after('<div class="khelp"><div class="bohelp"></div><div class="ndhelp"><div class="nnhelp"></div></div></div>');
        
        $(ele).clone().prependTo($(ele).parent()).addClass('hhelp').removeAttr('style').removeClass('shelp').css('width',width).css('height',height).html('');

        $('.shelp').show();
        
        var wwidth = $(document).width();
        var wheight = $(document).height();

        top = $(ele).offset().top;
        left = $(ele).offset().left;    
        width = $(ele).outerWidth();
        height = $(ele).outerHeight(); 

        $('.bohelp').css('top',(top - 2)).css('left',(left - 2)).css('width',(width + 4) ).css('height',(height + 4));

        var t1 = top;
        var t2 = left;
        var t3 =  wheight - (top + height);
        var t4 = wwidth - (left + width);


        var max = t1;
        var maxs = 't1';
        if(t1 < 300){
            if(max <= t2){max = t2; maxs = 't2'};
            if(max <= t4){max = t4; maxs = 't4';};
        }   

        if(max <= t3){max = t3; maxs = 't3'};
            

        if(maxs == 't1'){
            left = wwidth * 0.1 ;
            top = 0;
            width = wwidth * 0.8;
            height = max;
        }else if(maxs == 't2'){
            height = wheight * 0.8;
            top = wheight * 0.1;
            left = 0;
            width = max;
        }else if(maxs == 't3'){
            left = wwidth * 0.1 ;
            top = top + height;
            width = wwidth * 0.8;
            height = max;
        }else{
            height = wheight * 0.8;
            top = wheight * 0.1;
            left = left + width;
            width = max;
        }

        stt = parseInt(stt);

        var btnnext = '<button '+d_st+'="'+(stt + 1)+'" '+d_gr+'="'+gro+'" class="btn btn-default bhelp">Tiếp theo</button>';

        $('.nnhelp').html('<button  class="btn btn-default ehelp">×</button><fieldset><legend>Hướng dẫn</legend>' + noi + btnnext + '</fieldset>');
        $('.ndhelp').css('top',top).css('left',left).css('width',width).css('height',height).show();
        // alert(gro+'-'+stt);
        //var sss = 'http://localhost/advance20170507/ad/itemhelp/view?st=1&gr=1';
    }else{
        $('.khelp').remove();
    }
}


function changeipse(idpj) {    
    // if($('#pj'+idpj).hasClass('tuyecked') === false ){
    if($('#pj'+idpj).hasClass('tuypjed') === false ){        
        $('#pj'+idpj).find('input.ca').each(function () {
            var htmlip = $(this).parent().html();
            var pa = $(this).parent();
            htmlip = '<label>'+htmlip+'</label>';
            $(pa).html(htmlip).addClass('ipslet');
        })
    }
}


function aufoerr(current) {
    // console.log($(this));
    returnparent($(current),'FORM');
    var idform = $(parent).attr('id');
    // console.log(idform);
    $('form#'+idform+ ' .has-error').each(function (index) {
        if(index == 0){
            clickbackparent(this);            
        }        
    })  
    // setTimeout( function(){ 
    //     $("form#"+idform+" .has-error:eq(0) .mulr-pa button").click(); 
    //     $("form#"+idform+" .has-error:eq(0) input").focus(); 
    //     $("form#"+idform+" .has-error:eq(0) textarea ").focus(); 
    // }, 100)       
    setTimeout( function(){         
        $("form#"+idform+" .has-error:eq(0) input").focus(); 
        $("form#"+idform+" .has-error:eq(0) textarea ").focus(); 
        $("form#"+idform+" .has-error:eq(0) .mulr-pa button").click(); 
        if($("form#"+idform+" .has-error:eq(0) .mulr-body input[type=text]").length < 1){
            $("form#"+idform+" .has-error:eq(0) .mulr-pa button").focus();    
        }        
        
    }, 300)
}

function clickbackparent(element) {    
    var parentId =$(element).parent().attr('id');
    //alert(parentId);
    var parentClass = $(element).parent().attr('class'); 
    if(parentClass == 'tab-pane fade in active ' || parentClass == 'tab-pane fade in active' || parentClass == 'tab-pane fade ' || parentClass == 'tab-pane fade' || parentClass == 'tab-pane fade in ' || parentClass == 'tab-pane fade in') 
       $('ul.nav-tabs a[href="#'+parentId+'"]').click();            
    
    if($(element).parent().get(0).tagName != 'FORM'){
        clickbackparent($(element).parent());    
    }
    
}



function clmn(current) {
    $(".menu-item.btnfocus").removeClass('btnfocus');   
    $(current).addClass('btnfocus');

    if($('.foc').length > 0){
        $(".menu-parent>.btnfocus").removeClass('btnfocus');
    }   
    $(".menu-parent>.foc").addClass('btnfocus');
    $(".menu-parent>.foc").removeClass('foc');  

    var tenmenu = $('.menu-parent li.btnfocus span').text();    
    var tenmenuitem = $(current).context.children[1].innerText;
    document.title = tenmenuitem;

    var titlesearch = '<fieldset><legend>Tìm kiếm</legend></fieldset>';

    var btnreload = '<button type="button" '+d_i+'="'+$(current).attr(d_i)+'" class="btn btn-default btre" "><span class="glyphicon glyphicon-refresh mden"></span>Làm mới</button>';

    tenmenu = titlesearch + btnreload + '<div><span>' + tenmenu + '</span> » <b>' + tenmenuitem + '</b></div>';
    $('#pagecurrent').removeClass('ch_ct');
    loadmn(current,tenmenu);
}
function clmn_ch(current) {
    $(".menu-item.btnfocus").removeClass('btnfocus');   
    $(current).addClass('btnfocus');

    if($('.foc').length > 0){
        $(".menu-parent>.btnfocus").removeClass('btnfocus');
    }   
    $(".menu-parent>.foc").addClass('btnfocus');
    $(".menu-parent>.foc").removeClass('foc');  

    var tenmenu = $('.menu-parent li.btnfocus span').text();    
    var tenmenuitem = $(current).context.children[1].innerText;
    document.title = tenmenuitem;

    var titlesearch = '';
    var btnreload = '';
    tenmenu = titlesearch + btnreload + '<div><span>' + tenmenu + '</span> » <b>' + tenmenuitem + '</b></div>';
    $('#pagecurrent').addClass('ch_ct');
    loadmn(current,tenmenu);
}




function autohimn(current) {
    $(".menu-parent>li").removeClass('foc');
    $(".menu-parent>li").removeClass('btnfocus');
    $(current).addClass('btnfocus');

    if($('.hidemenu').length > 0 ){
        $(current).parents('#menu-top').addClass('hm');
    }


    $(current).addClass('foc');
    if($('.hidemenu').length > 0){      
        $('#menu-top').removeClass('hidemenu');

        $('#minimenu span').removeClass('glyphicon-chevron-down');
        $('#minimenu span').addClass('glyphicon-pushpin');      
        // $('#menu-top').addClass('showmenu');
    }
}


function himn() {
    $('#menu-top').removeClass('hm');
    if($('.hidemenu').length > 0){      
        $('#menu-top').addClass('hidemenu');
        $('#minimenu span').removeClass('glyphicon-pushpin');
        $('#minimenu span').addClass('glyphicon-chevron-down'); 
    }
}


function mnmenu() {

    $('#menu-top').removeClass('hm');

     if($('.showmenu').length > 0){      
        $('#menu-top').addClass('hidemenu');
        $('#menu-top').removeClass('showmenu');

        $('#content').addClass('hidemenu');
        $('#pagecurrent').addClass('hidemenu');
        $('#content .content-right-top').addClass('hidemenu');
        

        // $('#minimenu').html('');
        $('#minimenu span').addClass('glyphicon-chevron-down');
        $('#minimenu span').removeClass('glyphicon-chevron-up');
    }else{  
        $('#content').removeClass('hidemenu');
        $('#pagecurrent').removeClass('hidemenu');
        $('#content .content-right-top').removeClass('hidemenu');

        $('#menu-top').removeClass('hidemenu');
        $('#menu-top').addClass('showmenu');   

        $('#minimenu span').removeClass('glyphicon-chevron-down');
        $('#minimenu span').removeClass('glyphicon-pushpin');   
        $('#minimenu span').addClass('glyphicon-chevron-up');
    }
    reload($('#content .pj').attr(d_i));   
 } 


function afmohi() { 
    $('.nnhelp.modalhelp').remove();
    $('.modalhelp').removeClass('modalhelp');
    $('.khelp .nnhelp').show();
    var close = 0
    $('.modal-content').each(function (index) { 
        if($(this).is(':visible') == false){
            $(this).animate({               
                opacity: 0   
            }); 
        }else{
            close += 1
        }
    })
    if(close > 0){
        $('body').addClass('modal-open')
    }else{
        $('body').removeClass('modal-open')
    }
    
    if($('.modal.in').length < 1){
//Dong modal neu muon refresh lai
//        reload($('#content .pj').attr(d_i));        
    }
}


function btnrl(current) {
    reload($(current).attr(d_i));
}


var ccos //chech count open select
function opbody(current) {   
    ccos = 0
        var wheight = $(document).height();
        var top = $(current).offset().top;
        var vitrielement = wheight - top;
    
    if(vitrielement < 210){
        $(current).parent().find('.mulr-body').css('bottom','24px');
    }

    if($(current).parent().parent().find('.mulropen').length > 0){
        $('.mulropen').removeClass('mulropen');
        var di = $(current).parents('.mulr-pa').attr(d_i);
        var ds = $(current).parents('.mulr-pa').attr(d_s);
        if(ds == 'rel'){
            reload(di);   
        }
        // reload($('#content .pj').attr(d_i));
        //reload($(current).attr(d_i),$(current).attr(d_u));
    }else{
        $('.mulropen').removeClass('mulropen');
        $(current).parent().addClass('mulropen');
        var dt = $(current).parent().attr(d_t);    
        if(typeof dt !== 'undefined'){  
            // if($(current).parent().parent().find('.tooltip').length < 1){
                setTimeout( function(){         
                    $(current).parent().find('input[type=text]').focus();
                },300);
            // }
        }  
    }
}

function clbody(ev) {    
    var divparents = $(ev).parents('.mulr-pa');
    if (divparents.length < 1){ 
        if($('.mulropen').length > 0){   
            if($('.mulropen').attr(d_s) == 'rel'){
                if(ccos == 1){
                    reload($('.mulropen').attr(d_i));
                    ccos = 0;
                }
            }                     
            $('.mulropen').removeClass('mulropen');
        }        
    } 
}



function changeinputsearch(ev,current){ 
    var divparents = $(ev).parents('.pj');    
    var pjid = $(divparents).attr(d_i);

    $(current).parents('.mulr-body').find('li').addClass('isea');

    $(current).parents('.mulr-body').find('li').each(function () {
        if(($(this).find('i').length) > 0){
            content = $(this).find('i').html().toUpperCase();
            strsearch = textfriendly($(current).val()).toUpperCase();
        }else{
            content = $(this).find('span').html().toUpperCase();
            strsearch = $(current).val().toUpperCase();
        }        
        
        if(strsearch == ''){
            $(this).removeClass('isea'); 
        }else{
            if(content.indexOf(strsearch) > -1){
                $(this).removeClass('isea');  
            }
        }
    });
}



function changeinpsea(ev, current){
    var divparents = $(ev).parents('.pj');    
    var pjid = $(divparents).attr(d_i);    
    

    var name = $(current).attr('name');        
    var idpa = $(current).attr('id');
    var valu = $(current).val();
    // if(valu != ''){
        valu = encodeURIComponent(valu);
        reload(pjid,updateQueryStringParameter(getdu(pjid).replace(/&amp;/g, "&").replace(/%5B%5D/g, "[]"),name,valu));
    // }
    // alert(valu);
}





function selectmur(id) { 
    $('#'+id+'.mulr').each (function () {


        $(this).parents('.ri').find('input[type="hidden"]').remove();

        var idpj = $(this).attr(d_i);

        var dicon = $(this).attr(d_icon);

        var name = $(this).attr('name');        
        var idpa = $(this).attr('id');

        var type = $(this).attr(d_ty);
        if(type == 'ra'){
            type = 'radio';
        }
        if(type == 'ch'){
            type = 'checkbox';
        }

        var dataplacement = $(this).attr('data-placement');
        if(typeof dataplacement == 'undefined'){
            dataplacement = '';
        }else{
            dataplacement = ' data-placement="' + dataplacement + '"';
        }

        var dataoriginaltitle = $(this).attr('data-original-title');
        if(typeof dataoriginaltitle == 'undefined'){
            dataoriginaltitle = '';
        }else{
            dataoriginaltitle = ' title="' + dataoriginaltitle + '"';
        }
        var datatitle = $(this).attr('title');
        if(typeof datatitle == 'undefined'){            
        }else{
            if(dataoriginaltitle == ''){
                dataoriginaltitle = ' title="' + datatitle + '"';
            }            
        }



        var datatrigger = $(this).attr('data-trigger');
        if(typeof datatrigger == 'undefined'){
            datatrigger = '';
        }else{
            datatrigger = ' data-trigger="' + datatrigger + '"';
        }
        
        var ds = $(this).attr(d_s);
        if(typeof ds == 'undefined'){ds = '';}
        var dc = $(this).attr(d_c);
        if(typeof dc == 'undefined'){dc = '';}

        var dadd = $(this).attr(d_add);
        if(typeof dadd == 'undefined'){dadd = '';}

        var check_tn = 0; 
        var addchild ='';
        var addchild1,addchild2;       
        if(dadd.split('_')[1] == 'tn'){
            check_tn = 1;
            if(dadd != ''){
                // di_add = idpa.replace(/fk-/g, "");            
                di_add = idpa.split('-')[1];
                addchild1 = '<div class="achild"><i ' + d_m+'="3"'  +d_u+'="c_tn';
                addchild2 = '" '+d_i+'="'+di_add+'"  class="glyphicon glyphicon-plus pjbm"></i></div>';
            }
        }


        var dt = $(this).attr(d_t);
        var ipsearch = '';
        showradio = '';
        if(typeof dt == 'undefined'){            
            dt = '';
        }else {
            if(dt == 'sea'){
                ipsearch = '<input checked="" type="text" value="" placeholder="Tìm kiếm"><i class="glyphicon glyphicon-search"></i>';
            }
            if(dt == 'show'){
                showradio = 'sradio';
            }
        }
        $(this).after('<div id="'+idpa+'-pa" class="mulr-pa '+showradio+' " '+dataplacement + datatrigger + dataoriginaltitle +' '+d_i+'="'+idpj+'"  '+d_icon+'="'+dicon+'"  '+d_t+'="'+dt+'"  '+d_s+'="'+ds+'" '+d_c+'="'+dc+'" '+d_ty+'="'+type+'" data-html="true" data-toggle="tooltip"></div>');       

        classdadd = '';
        if(dadd != ''){
            // di_add = idpa.replace(/fk-/g, "");            
            di_add = idpa.split('-')[1];
            dadd = '<i ' + d_m+'="2"'  +d_u+'="'+dadd+'" '+d_i+'="'+di_add+'"  class="glyphicon glyphicon-plus pjbm"></i>';
            classdadd = 'cdadd';
        }
        if(type == 'checkbox'){
            if(classdadd != ''){
                btnclear = 'creb';
            }else{
                btnclear = '';
            }
            btnclear = '<i class="brese glyphicon glyphicon-remove '+btnclear+'"></i>';
            classdadd += ' cre';
        }else{
            btnclear = '';
        }
        
        var btnxuonglen = '<i class="glyphicon glyphicon-triangle-bottom mulr-head '+classdadd+'"></i><i class="glyphicon glyphicon-triangle-top mulr-head '+classdadd+'"></i>';

        $('#'+idpa+'-pa').append(dadd + btnclear + btnxuonglen + '<button type="button" class="btn mulr-head"><ul>--- Chọn ---</ul><div></div></button>');

        $('#'+idpa+'-pa').append('<div class="mulr-body">'+ipsearch+'<ul></ul></div>'); 

        var countcheck = 0;  
        var stringselect = '';
        var dem = 0;

        var s_level0 = '';
        var s_level1 = '';
        var s_level2 = '';
        var s_level = '';
        var titlehd = '';
        $(this).find('option').each(function () {
            attrname = 'name="'+ name+'"';
            var value = $(this).val();
            if(value != ''){
                thistext = $(this).text();
                //Xu ly mau sac
                if(typeof(thistext.split('##')[1]) !== 'undefined'){
                    colo = thistext.split('##')[1];
                    thistext = thistext.split('##')[0];
                }else{
                    colo = '';
                }

                //Xu ly luon check (o trong element Chinh sach)
                dcc = '';

                if(typeof(thistext.split('#$')[1]) !== 'undefined'){
                    alway = 'alway';
                    thistext = thistext.split('#$')[0];
                    alwayelement = '<i class="glyphicon glyphicon-pushpin"></i>';
                    idcsdm = 'csdm';
                    
                    if(thistext.indexOf('<i>Tất cả</i>') >= 0){
                        attrname = '';
                    }

                    countiddmcs = 0;
                    $('li.ichecked input').each(function (index) {
                        iddmcs = $(this).attr(d_lcs);
                        if(typeof(iddmcs) !== 'undefined'){
                            iddmcs = iddmcs.split(' ');
                            for (var i = iddmcs.length - 1; i >= 0; i--) {
                                if(value == iddmcs[i]){
                                    countiddmcs += 1;
                                }
                                // alert('cs'+value+'-'+iddmcs[i]);
                            };                            
                        }
                    })  
                    // alert('-> cs'+value+'-'+countiddmcs);                  
                    if(countiddmcs > 0){ 
                        attrname = '';                       
                        dcc = d_cc+'="' + countiddmcs + '"';
                    }
                }else{
                    alway = '';
                    alwayelement = '';                    
                    idcsdm = '';                    
                }



                //Xu ly chon Danhmuc, autocheck Chinhsach tuong ung (o element Danh muc)
                if(typeof(thistext.split('#@')[1]) !== 'undefined'){  
                    fulltext = thistext;               
                    thistext = thistext.split('#@')[0];
                    listchinhsach = fulltext.replace(thistext+'#@','')+ '"';
                    listchinhsach = listchinhsach.replace(/#@/g,' ');
                    listchinhsach = d_lcs + '="' + listchinhsach;
                    // thistext = listchinhsach;

                }else{
                    listchinhsach = '';                    
                }


                

                var check_tn_class = '';
                var count_level = thistext.split("|-").length - 1;
                giatrih6_1 = '';
                giatrih6_2 = '';

                nhomh4_1 = '';
                nhomh4_2 = '';
                
                thongso_2 = '';

                if(check_tn == 1){                    

                    if( count_level < 2){
                        check_tn_class = 'etn';
                        titlehd = 'Thêm giá trị cho thông số '+thistext.replace(/\|----- /g, "");
                        if(count_level == 0) titlehd = 'Thêm thông số vào nhóm '+thistext.replace(/\|----- /g, "");
                        addchild = addchild1 + '?pa='+value+'" title="'+ titlehd + addchild2;
                        
                    }else{
                        giatrih6_1 = '<h6>';
                        giatrih6_2 = ' (Giá trị)</h6>';

                        check_tn_class = '';
                        addchild = '';
                    }   
                    

                    if(count_level == 0){
                        s_level0 = thistext;
                        s_level = s_level0;
                        nhomh4_1 = '<h4>Nhóm: ';
                        nhomh4_2 = '</h4>';
                    }
                    if(count_level == 1){                        
                        thongso_2 = ' (Thông số)';
                        s_level1 = thistext;
                        s_level = s_level0 + ' * ' + s_level1;
                    }
                    if(count_level == 2){
                        s_level2 = thistext;
                        s_level = s_level0 + ' * ' + s_level1 + ' * ' + s_level2;
                    }
                    s_level = '<i>'+ textfriendly(s_level) + '</i>';
                }
                


                //Bat dau Hien thi li input

                var liyesno_start = '<li>';
                var liyesno_end = '</li>';
                if(dc == 'one'){
                    liyesno_start = '';
                    liyesno_end = '';
                }


                if($(this).is(':checked')){
                    stringselect += liyesno_start + enxss(thistext.replace(/\(Giá trị\)/g, "")) + liyesno_end;

                   
                    countcheck += 1; 
                    if(countcheck > 200){
                        $('#'+idpa+'-pa .mulr-head ul').html(countcheck+' lựa chọn ---');    
                    }else{                        
                        shead = stringselect.replace(/\|----- /g, "");
                        $('#'+idpa+'-pa .mulr-head ul').addClass(colo);
                        if(shead.length < 3500){
                            shead = shead.replace(/<i>Tất cả<\/i>/g,'');

                            if(typeof dicon !== 'undefined'){
                                shead = '<div class="fa-2x '+value+'"></div>' + shead;
                            }

                            $('#'+idpa+'-pa .mulr-head ul').html(shead);

                        }else{
                            $('#'+idpa+'-pa .mulr-head ul').html(countcheck+' lựa chọn ---');
                        }
                    } 
                     
                    if(typeof dicon === 'undefined'){
                        $('#'+idpa+'-pa'+' .mulr-body ul').append('<li title="'+giatrih6_1+enxss(thistext.replace(/\|----- /g, ""))+giatrih6_2+'" class="'+alway+' '+colo+' ichecked"><label  class="'+check_tn_class+'"><input '+dcc+' class="ip'+idcsdm+value+'" '+listchinhsach+'  checked type="'+type+'" '+attrname+' value="'+value+'"><span>'+enxss(thistext)+'</span>'+s_level+addchild+'</label>'+alwayelement+'</li>');
                    }else{
                        _html = '<li title="'+giatrih6_1+enxss(thistext.replace(/\|----- /g, ""))+giatrih6_2+'" class="'+alway+' '+colo+' ichecked">';
                        _html += '<label  class="s_icon '+check_tn_class+'"><input '+dcc+' class="ip'+idcsdm+value+'" '+listchinhsach+'  checked type="'+type+'" '+attrname+' value="'+value+'">';
                        _html += '<div class="fa-2x '+value+'"></div>';
                        _html += '<span>'+enxss(thistext)+'</span>';
                        _html += s_level+addchild+'</label>'+alwayelement+'</li>';
                        $('#'+idpa+'-pa'+' .mulr-body ul').append(_html);
                    }   



                }else{
                    if(dem == 0 && type == 'radio' && dc != 'one'){
                        
                        $('#'+idpa+'-pa' +' button.mulr-head ul').html(thistext);
                        $('#'+idpa+'-pa'+' .mulr-body ul').append('<li title="'+enxss(thistext.replace(/\|----- /g, ""))+'"><label><input checked type="'+type+'" '+attrname+' value="'+value+'"><span>'+enxss(thistext)+'</span></label></li>');
                    }else{ 

                        if(typeof dicon === 'undefined'){
                            $('#'+idpa+'-pa'+' .mulr-body ul').append('<li title="'+enxss(thistext.replace(/\|----- /g, ""))+'" class="'+alway+' '+colo+'"><label class="'+check_tn_class+'"><input '+dcc+' class="ip'+idcsdm+value+'" '+listchinhsach+'  type="'+type+'" '+attrname+' value="'+value+'"><span>'+nhomh4_1+giatrih6_1+enxss(thistext)+thongso_2+giatrih6_2+nhomh4_2+'</span>'+s_level+addchild+'</label>'+alwayelement+'</li>'); 
                        }else{
                            _html = '<li title="'+enxss(thistext.replace(/\|----- /g, ""))+'" class="'+alway+' '+colo+'">';
                            _html += '<label class="s_icon '+check_tn_class+'"><input '+dcc+' class="ip'+idcsdm+value+'" '+listchinhsach+'  type="'+type+'" '+attrname+' value="'+value+'">';
                            _html += '<div class="fa-2x '+value+'"></div>';
                            _html += '<span>'+nhomh4_1+giatrih6_1+enxss(thistext)+thongso_2+giatrih6_2+nhomh4_2+'</span>';
                            _html += s_level+addchild+'</label>'+alwayelement+'</li>';
                            $('#'+idpa+'-pa'+' .mulr-body ul').append(_html);
                        }                        
                    }                    
                }
                dem += 1;
            }else{
                
                if(type == 'radio' && dc == 'one'){
                    $('#'+idpa+'-pa'+' .mulr-body ul').append('<li><label><input checked type="'+type+'" '+attrname+' value=""><span>--- Chọn ---</span></label></li>');                    
                }
            }

        })
        if(countcheck > 0){            
            $('#'+idpa+'-pa .mulr-head').addClass('cked');
        }
        // $('select[name="'+name+'"]').remove();
        if(type == 'radio'){
            // $('select[name="'+name+'"]').remove();
            $(this).remove();
        }
        $(this).children().removeAttr("selected");
        

        $('[data-toggle="tooltip"]').tooltip();

    })
}



function changeinputmulr(ev,current) { 
    ccos = 1
    var divparents = $(ev).parents('.pj'); 


    //Thoong so cha thi ko click duoc
    if($(current).parent().hasClass('etn') == 1){
        // alert($(current).parent().attr('class'));        
        $(current).prop( "checked", false );
        $(current).prop( "disabled", true );        
        return false;
    }
    //Alway: luon luon (Các chính sách áp dụng all thì alway)
    if($(current).parents('li').hasClass('alway') == 1){    
        // if($(current).hasClass('atclick') != 1){
            // $(current).prop( "disabled", true );
            if($(current).attr('checked')){
                $(current).prop( "checked", true );
            }else{
                $(current).prop( "checked", false );
            }
            return false;
        // }
    }

    dlcs = $(current).attr(d_lcs);
    if(typeof(dlcs) !== 'undefined' && dlcs != ''){  
        arrdlcs = dlcs.replace(/#@/g,'');
        arrdlcs = arrdlcs.split(' ');
        for (var i_arrdlcs = arrdlcs.length - 1; i_arrdlcs >= 0; i_arrdlcs--) {
            dclickcount = $('.ipcsdm'+arrdlcs[i_arrdlcs]).attr(d_cc);
            // alert(dclickcount);
            if(typeof(dclickcount) === 'undefined'){
                // alert($('.ipcsdm'+arrdlcs[i]).attr('checked'));
                if($('input.ipcsdm'+arrdlcs[i_arrdlcs]).attr('checked')){
                }else{
                    if($(current).is(':checked')){
                        $('input.ipcsdm'+arrdlcs[i_arrdlcs]).attr(d_cc, '1');
                        $('input.ipcsdm'+arrdlcs[i_arrdlcs]).prop( "checked", true );
                        changeinputmulr_child(divparents,'input.ipcsdm'+arrdlcs[i_arrdlcs]);
                    }
                }
            }else{  
                if($(current).is(':checked')){
                    dclickcount = (parseInt(dclickcount) + 1);
                }else{
                    dclickcount = (parseInt(dclickcount) - 1);                    
                }
                if(dclickcount == 0){
                    $('input.ipcsdm'+arrdlcs[i_arrdlcs]).removeAttr(d_cc);
                    $('input.ipcsdm'+arrdlcs[i_arrdlcs]).removeAttr('checked');
                    $('input.ipcsdm'+arrdlcs[i_arrdlcs]).prop( "checked", false );
                    changeinputmulr_child(divparents,'.ipcsdm'+arrdlcs[i_arrdlcs]); 
                }else{
                    $('input.ipcsdm'+arrdlcs[i_arrdlcs]).attr(d_cc,dclickcount);    
                }
            }
        };
        
    }

    
    changeinputmulr_child(divparents,current);

    
}

function changeinputmulr_child(ev,current) { 
    
    var colo = $(current).parents('li').attr('class');
    if(typeof(colo) === 'undefined'){
        colo = '';
    }
    colo = colo.replace('ichecked','');

    var value_select = $(current).val();

    var divparents = ev;    
    var pjid = $(divparents).attr(d_i);
    
    var idcha = $(current).parents('.mulr-pa').attr('id');
    // alert(idcha);
    var dscha = $(current).parents('.mulr-pa').attr(d_s);
    var dc = $(current).parents('.mulr-pa').attr(d_c);
    var dicon = $(current).parents('.mulr-pa').attr(d_icon);
    
    var countcheck = $('#'+idcha +' .mulr-body input:checked').length;  
    var name = $(current).attr('name');

    if(typeof(name) !== 'undefined'){
        var data = $('input[name="'+name+'"]').serialize().replace(/%5B%5D/g, "[]");
        data =  data.substring(name.length + 1, data.length); 
        $('select[name="'+name+'"]').children().removeAttr("selected");    
    }

    if($(current).is(':checked')){
        $(current).parents('li').addClass('ichecked');  
    }else{
        $(current).parents('li').removeClass('ichecked'); 
    }     



    // if(typeof $(ev).val() !== 'undefined' ){        
        // $('select[name="'+name+'"]').val($(ev).val()).change();  
    //     if(typeof $('select[name="'+name+'"]').attr(d_ty) !== 'undefined' ){  
    //          // $('select[name="'+name+'"]').remove();
    //     }
    // }

    if(dscha == 'rel'){        
        addurl(pjid,updateQueryStringParameter(getdu(pjid).replace(/&amp;/g, "&").replace(/%5B%5D/g, "[]"),name,data));         
        if(dc == 'one'){
            reload(pjid);
        }  
    }  

    if(dc == 'one'){
        $(current).parents('ul').find('.ichecked').removeClass('ichecked');
        $(current).parents('li').addClass('ichecked');
        $('.mulropen').removeClass('mulropen');        
    } 
    var stringselect = '';


    var liyesno_start = '<li>';
    var liyesno_end = '</li>';
    if(dc == 'one'){
        liyesno_start = '';
        liyesno_end = '';
    }
   

    $('#'+idcha +' .mulr-body input:checked').each (function (index) {        
                    // stringselect +=  (index == 0 ? '': ', ') + $(this).parent().find('span').html().replace(/\(Giá trị\)/g, "");
                    
                    if($(this).parent().find('span').html().indexOf('<i>Tất cả</i>') >= 0 ){
                        stringselect += liyesno_start + enxss($(this).parent().find('span').html().replace(/\(Giá trị\)/g, "")) + liyesno_end;
                    }else{
                        stringselect += liyesno_start + enxss($(this).parent().find('span').text().replace(/\(Giá trị\)/g, "")) + liyesno_end;    
                    }
                    
    })
    
    if(countcheck > 0){    
        if(countcheck > 200){            
            $('#'+idcha +' button.mulr-head ul').html(countcheck+' lựa chọn ---');
        }else{
            shead = stringselect.replace(/\|----- /g, "");               
            $('#'+idcha +' button.mulr-head ul').removeClass();
            $('#'+idcha +' button.mulr-head ul').addClass(colo);
            if(shead.length < 3500){
                shead = shead.replace(/<i>Tất cả<\/i>/g,'');
               
                if(typeof dicon !== 'undefined'){                    
                    shead = '<div class="fa-2x '+value_select+'"></div>' + shead ;
                }

                $('#'+idcha +' button.mulr-head ul').html(shead);
            }else{
                $('#'+idcha +' button.mulr-head ul').html(countcheck+' lựa chọn ---');
            }
            
        }
        $('#'+idcha +' button.mulr-head').addClass('cked');
    }else{
        $('#'+idcha +' button.mulr-head ul').html('--- Chọn ---');
        $('#'+idcha +' button.mulr-head').removeClass('cked');
    }
    
}




function removeselect(ev,current) {  
    var parent = $(current).parent();

    if($(parent).find('button.mulr-head ul').html() != '--- Chọn ---'){
        var rel = $(parent).attr(d_s);
        var pjid = $(parent).attr(d_i);

        var list = $(parent).find('.mulr-body li');    
        list.each(function (index) {        
            if($(this).hasClass('ichecked')){
                $(this).find('input').click();
            }        
        })   
        if(rel == 'rel'){
            reload(pjid);
        }
    }
};







//Recycle


function recyclechung(ev,current, str = '',reloadhaykhong = '') {
	if (confirm('Bạn chắc chắn muốn '+str+'?')) { 
        loadimg();

        var divparents = $(ev).parents('.pj');    
        var tenid = $(divparents).attr(d_i); 

        var count = $(current).attr(d_ct); 
        if(typeof count === 'undefined') count = 0;

        var tp = $(current).attr(d_type); 
        if(typeof tp === 'undefined'){
            tp = '';
        }
        

        //Tach type (b, md, dm, mn,,,,)
        // var du = $(current).attr(d_u); 
        // if(typeof du === 'undefined') du = "";
        // du = du.split('?')[0];
        // du = du.split('_')[1];
        // if(typeof du === 'undefined'){
        //     du = "";
        // }else{
        //     du = '_'+du;
        // }
        // irecycleid = du;        



        var idmodal = $(current).parents('.modal').attr('id');

        $.ajax({
            cache: false,
            url: creurl(current),
            type: 'POST',                               
            success: function (data) { 
                unloadimg();  
                if(reloadhaykhong == 'co'){
                    reload(tenid);   
                }
                if(data == 1){    
                    popthanhcong(str);
                    if(count > 1){    
                        reload('r'+tenid,'ir'+tp,tenid);                         
                        // reload('pjr'+tenid,url+tenid+'/indexrecycle');                        
                    }else{
                        mdie(idmodal);
                    }
                    pjelm(tenid,tp);
                    if(str === 'XÓA TẤT CẢ') mdie(idmodal);

                }else{
                    popthatbai(str);
                }
            },
            error: function () {
                reload(tenid);
                poploi();
            }                
        });
    } 
}









//Index

// 'onchange' => '  
//         ));
//     ', 
function changeipage(current,ev) {
    var divparents = $(ev).parents('.pj');    
    var idpj = $(divparents).attr(d_i);
    reload(idpj,updateQueryStringParameter(getdu(idpj).replace(/&amp;/g, "&"),"t",$(current).val()));
}


function buttonmodal(current,pjidelement = '',selects = '') {

    var zindex = $('body').attr('data-z')
    if(typeof zindex === 'undefined') {
        zindex = 1050
    }    
    zindex = parseInt(zindex) + 1
    $('body').attr('data-z',zindex)

    
	loadimg();
    var id = $(current).attr(d_m); //id modal
    if(typeof id === 'undefined'){        
        id = '';
    }    
    if(!$(current).hasClass("kcm")){
        if($("#modal"+id).hasClass("in")){ //true            
            // zindex = (parseInt($("#modal"+id+".modal").css("z-index")) + 1);

            idmoi = id +''+'1';
            htmlmodal = '<div id="modal'+idmoi+'" style="z-index: '+zindex+';" class="fade modal" role="dialog" tabindex="-1">';
            htmlmodal += $("#modal"+id).html();
            htmlmodal += '</div>';

            htmlmodal = htmlmodal.replace('modalContent'+id,'modalContent'+idmoi);
            // console.log(htmlmodal);
            if ($("#modal"+idmoi).length == 0){
                $('body>div').prepend(htmlmodal);
            }
            id = idmoi;
        }else{
           
        }
    }
    if(selects == ''){
        doituong = {
             modal:'modal'+id,
        }
    }else{
        doituong = {
             modal:'modal'+id,
             selects: selects, 
        }
    }    
    // console.log(creurl(current));
    // alert('123');
    $.ajax({
        cache: false,
        url: creurl(current),
        type: 'POST',  
        data: doituong,                             
        success: function (data) { 
            
            data = gmcode(data);
            if(data != 'nacc'){
                $('#modal'+id).modal('show')                      
                .find('#modalContent'+id)
                .html("");

                $('#modal'+id).modal('show')                      
                .find('#modalContent'+id)
                .html(data);

                $('#modal'+id).css('z-index', zindex);

                unloadimg(); 
            }else{
                popno();
            }
        },
        error: function () {
            poploi();                    
        }
    });
}


function changeinput(ev,current) {

    var divparents = $(ev).parents('.pj');    
    var pjid = $(divparents).attr(d_i); 

	var countcheck = $('#pj'+pjid+' input.ca:checked').length;
    if(countcheck > 0){      	
    	 $('#pj'+pjid+' .cas button.bra').html('Thực hiện (cho '+countcheck+' bản ghi)')
    	// if($('#pj'+pjid+' .cas span').length < 1){
    		// $('#pj'+pjid+' .cas').prepend('<span>123</span>');
    	// }
    	// $('#pj'+pjid+' .cas span').html(countcheck+' lựa chọn');

        $('#pj'+pjid+' .cas').css('display','block');
    }else{
        $('#pj'+pjid+' .cas').css('display','none');
    }
}

function showhidden(ev,current)  {
	loadimg(); 

    var divparents = $(ev).parents('.pj');    
    var tenid = $(divparents).attr(d_i);    
    

    $.ajax({
        cache: false,
        url: creurl(current),
        type: 'POST',                               
        success: function (data) {   
            unloadimg(); 
            reload(tenid);                                
            if(data == 1){                                              
                popthanhcong('');
            }else{
                popthatbai('');
            }
        },
        error: function () {          
            poploi();
        }                
    }); // end ajax call
}


function buttonrecycle(ev,current) {                                     
    loadimg(); 

    var divparents = $(ev).parents('.pj');    
    var tenid = $(divparents).attr(d_i);  
    var modal = $(current).attr(d_m);  
    modal = 'modal' + modal;

    var tp = $(current).attr(d_type);     
    if(typeof tp === 'undefined'){
        tp = '';
    }


    $.ajax({
        cache: false,
        url: creurl(current),
        type: 'POST',                               
        success: function (data) { 
            unloadimg();   
            reload(tenid);    
            pjelm(tenid,tp);                          

             if(data == 1){                                              
                poprecycle(modal);
            }else{
                popthatbai('Xóa');
            }
        },
        error: function () {           
            poploi();
        }                
    }); // end ajax call
}


function buttonrecycleall(ev,current) { 
    var divparents = $(ev).parents('.pj');    
    var tenid = $(divparents).attr(d_i); 

    var tp = $(current).attr(d_type); 
        if(typeof tp === 'undefined'){
            tp = '';
        }



    var ids = $('#gr'+tenid).aabcGridView('getSelectedRows');  
    if(ids != ''){   
    	var idthaotac =  $('#sel'+tenid).val();      
    	// var thongdiep = ''; 
        if(tenid == 'domain' && idthaotac == 10){
            $.each(ids,function (key, value) {
                // alert($('tr[data-key='+value+']>td:nth-child(2)').html());                
                window.open('http://' + $('tr[data-key='+value+']>td:nth-child(2)').html() );
            });
        }else{
        	var valu = '';
        	if(idthaotac == 1){valu = '2';}
        	if(idthaotac == 2){valu = '1';}
        	if(idthaotac == 3){valu = '1';}

            
        	if(idthaotac != ''){  
                if(idthaotac == 11){
                    valu = '11';
                    current = $('#sel'+tenid+' option:selected');                    
                    buttonmodal(current,'',ids);
                }else{  	        
    		        loadimg();
    		        $.ajax({
    		            url: creurl(current),
    		            type: $(current).attr('method'),
    		            data: {selects: ids,typ: idthaotac,valu: valu},
    		            success: function (data) {
                            unloadimg(); 
    		                reload(tenid); 
    		                if(data == 1){
    		                    popthanhcong('');  
    		                }else{
    		                    popthatbai('');
    		                }
                             pjelm(tenid,tp);
    		            },
    		            error: function () {
    		                poploi();
    		            }
    		        }); 
                }
    	    }
        }
    }
}


function changep(ev, current) {  
    var divparents = $(ev).parents('.pj');    
    var pjid = $(divparents).attr(d_i);
    
	reload(pjid,updateQueryStringParameter(getdu(pjid).replace(/&amp;/g, "&"),"p",$(current).val()));
}


function clicka(ev, current) {  
    var divparents = $(ev).parents('.pj');    
    var pjid = $(divparents).attr(d_i); 
	reload(pjid,$(current).attr(d_u));
}


function totop() {
	$('html body').animate({ scrollTop: 0 }, '1500');
}

function changea(pjid) {	
	$('#pj'+pjid+' a').each(function( index, value ) {          
        var urla = $(this).attr('href');
        $(this).attr('href', 'javascript:void(0)');
        urla = decodeURIComponent(urla);

        var res = urla.match(RegExp("\\[[0-9]\\]", "g")); 
        
        if(res){
            // console.log(res);    
            res.forEach(function(index){
                urla = urla.replace(index,'[]')
                // alert(urla);
            })
        }        
        pa_k = getParameterByUrl('k',urla)
        pa_f = getParameterByUrl('f',urla)

        // console.log(urla);
        var du = urla.substring(urla.lastIndexOf('/'),urla.length);        
        // urla = urla.replace(du, '');        
        du = du.substring(du.lastIndexOf('?')+1,du.length);                

        du = du.replace('k='+pa_k+'&','')
        du = du.replace('k='+pa_k,'')
        du = du.replace('f='+pa_f+'&','')
        du = du.replace('f='+pa_f,'')
        du = pa_f+'?'+du
        
        du = decodeURIComponent(du);
        
        di = pa_f
        
        // di = urla.substring(urla.lastIndexOf('/') + 1,urla.length);
        
        // di = 'site'

	    $(this).attr(d_u, du);
        $(this).attr(d_i, di);
	});
}


function footertable(pjid) {
	$('#pj'+pjid+' .gr table thead').attr('id','heado');
	$('#pj'+pjid+' .pagination').prependTo('#pj'+pjid+' .endgr');
	$('#pj'+pjid+' .sy').prependTo('#pj'+pjid+' .endgr  .sy0');

	var pagecut = $('#pj'+pjid+' ul.pagination li.active a').html();
	$('#pj'+pjid+' ul.pagination li.active').append('<input type="number" min="1" value="'+pagecut+'" />');

}

function cleartimkiem(pjid) {
    if($('#pj'+pjid).find("form.mone").length > 0){        
        $('#pagecurrent').addClass('hitt');
    }else{
        $('#pagecurrent').removeClass('hitt');
    }
}


function headertable(pjid) {

    if($('#pj'+pjid).hasClass('tuypjed') === false &&  $('#pj'+pjid).parents('.modal').length < 1){

	    	$('#pj'+pjid+' .gr table thead').attr('id','heado');
	    	$('#pj'+pjid+' .gr table thead').clone().prependTo('#pj'+pjid+' .gr table').attr('id','headt');
	    	$('#pj'+pjid+' .gr table>thead#heado>tr>th').each(function(index) {  
	    		// console.log('tuyen 1');
	    	    $(this).css('width',$(this).outerWidth());    
	    	});
	        $('#pj'+pjid+' .gr table>tbody>tr:first-child>td').each(function(index) { 
	            // console.log('tuyen 2');
	            $(this).css('width',$(this).outerWidth());    
	        });
	    	$('#pj'+pjid+' .gr table thead#heado').css('position','fixed').css('z-index','9').css('margin','0 0 0 0');
	    	$('#pj'+pjid+' .gr table thead#heado').css('margin-top',-($('#pj'+pjid+' .gr table thead#headt').height()));
	    	$('#pj'+pjid+' .gr table thead#heado').css('width',$('#pj'+pjid+' .gr table thead#headt').outerWidth());
	    	// }
	        // $('#grsanpham').bind('scroll', function() {   
	    	//    $('#pj'+pjid+' .gr table thead#heado').css('margin-left', -($(this).scrollLeft() + 1));
	    	// });


	        if($('.hidemenu').length > 0){
	            $('#pj'+pjid+' .gr').css('height',$(document).height() - 34 - 30 - $('.footer').height() - $('#pj'+pjid+' .endgr').height() );
	        }else{
	            $('#pj'+pjid+' .gr').css('height',$(document).height() - 127 - 30 - $('.footer').height() - $('#pj'+pjid+' .endgr').height() );
	        }
	        if($('.showmenu').length > 0){                 
	        }else{         
	            $('.content-right-top').addClass('hidemenu');
	        }	

	        chaheicli(pjid);
	        //Hàm này gây chậm, cần cải tiến
    }
}

function chaheicli(pjid){  
    setTimeout( function(){         
        $('#gr'+pjid+' .omc').each(function(index) {            
            var hpa =   $(this).parents('tr').height();
            $(this).css({'height':hpa, 'margin-top':-(hpa / 2 + 7)});        
        }); 
    }, 2000)


       
}

function chaheicliaft(pjjd) {
    $(document).on('shown.bs.modal','.modal', function () {             
        chaheicli(pjjd);
    });
}



//$('#menu-top').clone().prependTo($('#menu-top').parent()).addClass('bhelp').after('<div class="hhelp"></div>');