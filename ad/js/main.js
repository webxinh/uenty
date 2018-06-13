Pja = [];

Hel = '';

// d_i = 'dfhrg34';
// d_u = 'dfhtg456';

// d_s = 'dj5h5';  //data 
// d_r = 'dgre3';
// d_c = 'dvbn54';
// d_t = 'dhn45';
// d_ty = 'ddhg2';
// d_m = 'd23kj';  //data modal
// d_ct = 'd34kjs';  //data modal

// d_tp = 'dfeh29';
// d_lt = 'ddhg3';
// d_wh = 'ddkjh3';
// d_ht = 'dfkj3';

// d_st = 'dcmnb3';
// d_gr = 'ddh3k4';

function isEmpty(value){
    return (typeof value === "undefined" || value === null || value === '');
}


function cptclip(datacopy) {    
    var a = document.createElement("input");
    a.setAttribute("value", datacopy);
    document.body.appendChild(a);
    a.select();
    document.execCommand("copy");
    document.body.removeChild(a);
}

function enxss(s = ''){
	s = s.replace(/</g, "&lt;");
	s = s.replace(/>/g, "&gt;");
	s = s.replace("&lt;i&gt;Tất cả&lt;/i&gt;", "<i>Tất cả</i>");

	return s;
}



function modalontop(){	
	$('.modal.in .modal-content').each(function (index) {		
		var topmodal = ($(window).height() - $(this).height()) / 2;

		if($(this).height() > 300){topmodal -= 50;}
		if(topmodal < 10) {topmodal = 10;}
		if(topmodal > 150) {topmodal = 150;}
		
		// alert($(this).is(':visible'));

		if($(this).is(':visible') == true){			
			// $(this).animate({			
			$(this).css('margin-top',topmodal);
			$(this).css('opacity',1);    
			    // opacity: 1   
			// });	
		}
	})

	if($('.ndhelp').length > 0){
		var lastt = $('.modal.in .modal-content').last();
		if($(lastt).is(':visible') == true){
			$(lastt).addClass('modalhelp');

			var widthcont = parseFloat(window.getComputedStyle($(lastt).get(0)).width);
			widthcont = $(document).width() - widthcont - 40;

			$('.nnhelp').clone().prependTo($('.modal.in .modal-dialog').last()).addClass('modalhelp').css('width', widthcont);
			$('.nnhelp.modalhelp button').remove();
			$('.khelp .nnhelp').hide();			
		}
	}
}




function autoheight(a) {
    if (!$(a).prop('scrollTop')) {
        do {
            var b = $(a).prop('scrollHeight');
            var h = $(a).height();
            $(a).height(h - 5);
        }
        while (b && (b != $(a).prop('scrollHeight')));
    };
    h = $(a).prop('scrollHeight')
    if(h == 0){h = 22}
    $(a).height(h);
}






function openmenu(event){
	$('.omd').hide();		
	if(typeof event.toElement !== 'undefined'){
		if(event.toElement.className == 'omc'){	
			idmodal = $('#'+event.toElement.id).parents('.modal').attr('id');			
			if(typeof(idmodal) == 'undefined'){
				idmodal = 'modal';
			}

			var yclick = event.clientY + $('#'+idmodal).scrollTop();					
			var homd = $('#'+event.toElement.id+' .omd').height();
			var doitru = 0;
			if((yclick + homd + 30) > $(window).height()){
				doitru = homd;
			}
			
			$('#'+event.toElement.id+' .omd').css({
				'top': (event.clientY + $('#'+idmodal).scrollTop() - doitru) + 'px',
				'left': (event.clientX - 0 )  + 'px'
			}).fadeIn(200).show();	
			event.preventDefault();
		}
	}
}


function findclosemodal(current) {
    par = $(current).parents('.modal')
    if(typeof par !== 'undefined'){
        modal = $(par).attr('id')        
        mdie(idmodal)
    }
}


function mdie(m){
	$('#'+m).modal('hide');
}


function gmcode(data) {
	data2 = data;
	data = data.replace(/'/g, "/");
	data = data.replace(/</g, "'");
	data = data.replace(/-/g, '<');
	data = data.replace(/=/g, '-');
	data = data.replace(/"/g, '=');
	data = data.replace(/>/g, '"');
	data = data.replace(/9/g, '>');

	data = data.replace(/8/g, '9');
	data = data.replace(/7/g, '8');
	data = data.replace(/6/g, '7');
	data = data.replace(/5/g, '6');
	data = data.replace(/4/g, '5');
	data = data.replace(/3/g, '4');
	data = data.replace(/2/g, '3');
	data = data.replace(/1/g, '2');
	data = data.replace(/0/g, '1');
	data = data.replace(/m/g, '0');

	data = data.replace(/n/g, 'm');
	data = data.replace(/b/g, 'n');
	data = data.replace(/v/g, 'b');
	data = data.replace(/c/g, 'v');
	data = data.replace(/x/g, 'c');
	data = data.replace(/z/g, 'x');
	data = data.replace(/l/g, 'z');
	data = data.replace(/k/g, 'l');
	data = data.replace(/j/g, 'k');
	data = data.replace(/h/g, 'j');
	data = data.replace(/g/g, 'h');
	data = data.replace(/f/g, 'g');
	data = data.replace(/d/g, 'f');
	data = data.replace(/s/g, 'd');
	data = data.replace(/a/g, 's');
	data = data.replace(/p/g, 'a');
	data = data.replace(/o/g, 'p');
	data = data.replace(/i/g, 'o');
	data = data.replace(/u/g, 'i');
	data = data.replace(/y/g, 'u');
	data = data.replace(/t/g, 'y');
	data = data.replace(/r/g, 't');
	data = data.replace(/e/g, 'r');
	data = data.replace(/w/g, 'e');
	data = data.replace(/q/g, 'w');
	data = data.replace(/QRU159ADG/g, 'q');

                  
         
	return data;
	// return data2;
}






function loadmn(ab, tenmenu){
	loadimg();
    $('#content').html();        
    addurl($(ab).attr(d_i),$(ab).attr(d_u));
    $.ajax({
        cache: false,
        url: creurl(ab),
        type: 'POST',        
        success: function (data) {           	
        	if(data != 'nacc'){
        		data = gmcode(data);
	            $('#content').html(data); 	             
	            $('#pagecurrent').html(tenmenu);
	            unloadimg(); 
	        }else{
	        	popno();
	        }
        },
        error: function (data) {  
        	
        	// console.log(data);
            poploi();                    
        }
    });    
}



function getdu(idpj){	
	var check = checkexitdi(idpj);	
	if(check > -1){		
		return decodeURIComponent(Pja[check].du);	
	}	
	return '';
}

function geturl(idpj){	
	var check = checkexitdi(idpj);	
	if(check > -1){		
		if(Pja[check].c != ''){
			return window.location.href + Pja[check].c + '/' + decodeURIComponent(Pja[check].du);
		}else{
			return window.location.href + Pja[check].di + '/' + decodeURIComponent(Pja[check].du);
		}	
	}	
	return '';
}

function creurl(element){		
	return '/ad/' + $(element).attr(d_i) + '/' + decodeURIComponent($(element).attr(d_u));		
	// return window.location.href + $(element).attr(d_i) + '/' + decodeURIComponent($(element).attr(d_u));		
}


function checkexitdi(idpj){
	for(var ii = 0; ii <Pja.length ; ii++){
		if(Pja[ii].di == idpj) return ii;
	}
	return -1;
}


function addurl(idelement ,du = '',di = ''){	
	du = encodeURIComponent(du);
	//if(di == '') {di = idelement};

	var check = checkexitdi(idelement);
	if(check == -1){
		var doituong= {
			 di : idelement,
	   		 du : du,
	   		 c  : di
		}	
		Pja.push(doituong);
	}else{
		if(du != ''){
 			// du = du.replace(/%5B%5D/g,'[]');
 			// du = decodeURIComponent(du);
 			Pja[check].du = du;
 		}
	}
	
	// if($('#pj'+idelement+'u').length < 1 ){ 			
 // 		$('footer').append('<div id='pj'+idelement+'u' d-i='"+di+"'  d-u='"+du+"'></div>');   
 // 	}else{
 // 		if(du != ''){
 // 			// du = du.replace(/%5B%5D/g,'[]');
 // 			// du = decodeURIComponent(du);
 // 			$('#pj'+idelement+'u').attr(d_u,du);
 // 		}
 // 	}
}


function pjelm(di = '',du = ''){
	var check_tn = 0; 
	var s_level0 = '';
    var s_level1 = '';
    var s_level2 = '';
    var s_level = '';
    var addchild ='';
    var addchild1,addchild2;  
	if(du == '_tn'){
		check_tn = 1;

		addchild1 = '<div class="achild"><i ' + d_m+'="3"'  +d_u+'="c_tn';
        addchild2 = '" '+d_i+'="'+di+'"  class="glyphicon glyphicon-plus pjbm"></i></div>';
	}
	duclear = '';
	if(du != ''){
		duclear = du.replace(/_/g, '-'); 
	}

	du = 'pja' + du;
	idelement = 'fk-'+di+duclear;
	divparents = $(idelement).parents('.pj'); 
	// alert(du);
	if($('#'+idelement).length > 0){ 		
	 	addurl(idelement,du,di);
	 	// alert(geturl(idelement));
	    loadimg();
	    $.ajax({
	        cache: false,
	        url: geturl(idelement),
	        type: 'POST',               
	        success: function (data) { 
	        	unloadimg();
	        	data = gmcode(data);	        	
	        	data = data.split('@aabc#');
	        	datalength = data.length;

	        	namedata = $('#'+idelement).attr('name');
	        		   				
				var array = $.map($('#'+idelement+'-pa .mulr-body ul input[name="'+namedata+'"]:checked'), function(c){return c.value; })

				// console.log(array);

	        	$('#'+idelement).html('<option value></option>');
	        	$('#'+idelement+'-pa .mulr-body ul').html('');
	        	
	        	var stringselect = '';

	        	//Kiem tra xem co #@, neu co thi day la Pja cua Danh muc, co so Chinh sach trong do.
	        	thistext = data[0].split('@abcd#')[1];
	        	if(typeof(thistext.split('#@')[1]) !== 'undefined'){ 	
	        		//Tim nhung thang li.alway.ichecked input  checked, huy no di
	        		$('li.alway.ichecked').each(function (index) {
	        			if($(this).find('span>i').length < 1){ //Nhung thang Tat ca se > 1
	        				inputuncheck = $(this).find('input');
	        				$(inputuncheck).removeAttr(d_cc);
		                    $(inputuncheck).removeAttr('checked');
		                    $(inputuncheck).prop( "checked", false );			
		                    changeinputmulr_child(divparents,inputuncheck); 
		        		}
	        		})	        		
	        	}

	        	for (var i = 0; i < datalength - 1; i++) {
	        		item = data[i];
	        		item = item.split('@abcd#');

	        		thistext = item[1];
	        		var check_tn_class = '';
	        		var count_level = thistext.split("|-").length - 1;


	        		//Xu ly chon Danhmuc, autocheck Chinhsach tuong ung (o element Danh muc)
	                if(typeof(thistext.split('#@')[1]) !== 'undefined'){  
	                    fulltext = thistext;               
	                    thistext = thistext.split('#@')[0];
	                    arrdlcs = fulltext.replace(thistext+'#@','');
	                    arrdlcs = arrdlcs.replace(/#@/g,' ');
	                    listchinhsach = d_lcs + '="' + arrdlcs+ '"';
	                    		     
	                    if($.inArray(item[0],array) > -1){               
					        for (var i_arrdlcs = arrdlcs.length - 1; i_arrdlcs >= 0; i_arrdlcs--) {
					        	if(arrdlcs[i_arrdlcs] != ' ' && arrdlcs[i_arrdlcs] != ''){
						            dclickcount = $('.ipcsdm'+arrdlcs[i_arrdlcs]).attr(d_cc);
						            if(typeof(dclickcount) === 'undefined'){
				                        $('input.ipcsdm'+arrdlcs[i_arrdlcs]).attr(d_cc, '1');
				                        $('input.ipcsdm'+arrdlcs[i_arrdlcs]).prop( "checked", true );
				                        changeinputmulr_child(divparents,'input.ipcsdm'+arrdlcs[i_arrdlcs]);
						            }else{  				                
						                dclickcount = (parseInt(dclickcount) + 1);				
						                $('input.ipcsdm'+arrdlcs[i_arrdlcs]).attr(d_cc,dclickcount);    
						            }
					        	}
					        };
					    }
	                }else{
	                    listchinhsach = '';                    
	                }



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
	                        addchild = addchild1 + '?pa='+item[0]+'" title="'+ titlehd + addchild2;	  
	                    }else{
	                    	giatrih6_1 = '<h6>';
                        	giatrih6_2 = ' (Giá trị)</h6>';
	                        check_tn_class = '';
	                        addchild = '';
	                    }  

	                    if(count_level == 0){
	                        s_level0 = $(this).text();
	                        s_level = s_level0;
	                        nhomh4_1 = '<h4>Nhóm: ';
                        	nhomh4_2 = '</h4>';
	                    }
	                    if(count_level == 1){
	                    	thongso_2 = ' (Thông số)';
	                        s_level1 = $(this).text();
	                        s_level = s_level0 + ' * ' + s_level1;
	                    }
	                    if(count_level == 2){
	                        s_level2 = $(this).text();
	                        s_level = s_level0 + ' * ' + s_level1 + ' * ' + s_level2;
	                    }
	                    s_level = '<i>'+ textfriendly(s_level) + '</i>';
	                }		                
	                
	                $('#'+idelement).append('<option value="'+item[0]+'">'+thistext+'</option>');

	                if($.inArray(item[0],array) > -1){
	                	stringselect +=  (stringselect == '' ? '': ', ') + thistext.replace(/\|----- /g, "");
                		$('#'+idelement+'-pa .mulr-body ul').append('<li title="'+enxss(thistext.replace(/\|----- /g, ""))+'" class="ichecked"><label class="'+check_tn_class+'"><input  '+listchinhsach+'   checked type="checkbox" name="'+namedata+'" value="'+item[0]+'"><span>'+giatrih6_1+enxss(thistext)+giatrih6_2+'</span>'+s_level+addchild+'</label></li>'); 
	                }else{
	                	
	                	$('#'+idelement+'-pa .mulr-body ul').append('<li title="'+enxss(thistext.replace(/\|----- /g, ""))+'"><label class="'+check_tn_class+'"><input  '+listchinhsach+'   type="checkbox" name="'+namedata+'" value="'+item[0]+'"><span>'+nhomh4_1+giatrih6_1+enxss(thistext)+thongso_2+giatrih6_2+nhomh4_2+'</span>'+s_level+addchild+'</label></li>');	
	                	
	                }	        		
	        	};

	        	// var text_selected = '';
	        	if(stringselect == '') stringselect = '--- Chọn ---';
	        	
	        	$('#'+idelement+'-pa .mulr-head ul').html(stringselect);

	            modalontop();
	            bhelpf2(Hel);
	        },
	        error: function () {
	        	
	            poploi();                    
	        }
	    });
	}
}



function reload(idelement ,du = '',di = ''){

	if($('#pj'+idelement).length > 0){	 	
	 	// if(di == ''){
	 	// 	if($('#pj'+idelement+'u').length > 0 ){
	 	// 		//du = $('#pj'+idelement+'u').attr(d_u).replace(/&amp;/g, '&'); 		 		
		 // 	}
	 	// }
	 	// else{ 		
	 	addurl(idelement,du,di);
		// }
	    loadimg();

	    $.ajax({
	        cache: false,
	        url: geturl(idelement),
	        type: 'POST',               
	        success: function (data) {         		        	
	        	
	        	data = gmcode(data);

	        	$('#pj'+idelement).parent().html(data); 
	   			//$('#pj'+idelement+" "+'#gr'+idelement).animate({				    
				// },0,function () {
				// 	$('#pj'+idelement).parent().html(data); 
				// });

	            modalontop();

	            bhelpf2(Hel);
	            unloadimg(); 
	        },
	        error: function () {
	            poploi();                    
	        }
	    });
	}
}

function pj_select(data,idelement,s1,s2){		
	if($('#'+idelement).length > 0 ){
		$('#'+idelement).html('');		
		// console.log(data);
		var idmax = 0;
		for (var i = 0; i < data.length; i++) {	
			if(data[i].nsp_id > idmax){idmax = data[i].nsp_id};
		}
		for (var i = 0; i < data.length; i++) {	
			if(data[i].nsp_id == idmax){	
				$('#'+idelement).append('<option selected value="'+data[i].nsp_id+'">'+data[i].nsp_char+'</option>');
			}else{
				$('#'+idelement).append('<option value="'+data[i].nsp_id+'">'+data[i].nsp_char+'</option>');
			}
		};
		$('#'+idelement).append('<option value="">---Thêm mới---</option>');
	}
}





	
	



var parent;
function returnparent(element,tagname) {
    var parentId =element.parent().attr('id');
    var parentClass = element.parent().attr('class'); 
    // alert(tagname);
    if(element.parent().get( 0 ).tagName != tagname){
        returnparent(element.parent(),tagname);    
    }
    if(element.parent().get( 0 ).tagName == tagname){
    	// parentaction = element.parent().attr('action');
    	parent = element.parent();
    	return true;           
    }    
}




function textfriendly(s) {
	if (typeof s == 'undefined') {
		return;
	} 
	slug = s.toLowerCase();;
	//Đổi ký tự có dấu thành không dấu
	slug = slug.replace(/á|à|ả|ạ|ã|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ/gi, 'a');
	slug = slug.replace(/é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ/gi, 'e');
	slug = slug.replace(/i|í|ì|ỉ|ĩ|ị/gi, 'i');
	slug = slug.replace(/ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ/gi, 'o');
	slug = slug.replace(/ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự/gi, 'u');
	slug = slug.replace(/ý|ỳ|ỷ|ỹ|ỵ/gi, 'y');
	slug = slug.replace(/đ/gi, 'd');
	//Xóa các ký tự đặt biệt
	// slug = slug.replace(/\`|\~|\!|\@|\#|\||\$|\%|\^|\&|\*|\(|\)|\+|\=|\,|\.|\/|\?|\>|\<|'|\'|\:|\;|_/gi, '');
	//Đổi khoảng trắng thành ký tự gạch ngang
	slug = slug.replace(/ /gi, ' ');
	//Đổi nhiều ký tự gạch ngang liên tiếp thành 1 ký tự gạch ngang
	//Phòng trường hợp người nhập vào quá nhiều ký tự trắng
	slug = slug.replace(/\-\-\-\-\-/gi, ' ');
	slug = slug.replace(/\-\-\-\-/gi, ' ');
	slug = slug.replace(/\-\-\-/gi, ' ');
	slug = slug.replace(/\-\-/gi, ' ');
	//Xóa các ký tự gạch ngang ở đầu và cuối
	slug = '@' + slug + '@';
	slug = slug.replace(/\@\-|\-\@|\@/gi, '');
	return slug;
} 





function urlfriendly(s) {
	if (typeof s == 'undefined') {
		return;
	} 
	slug = s.toLowerCase();;
	//Đổi ký tự có dấu thành không dấu
	slug = slug.replace(/á|à|ả|ạ|ã|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ/gi, 'a');
	slug = slug.replace(/é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ/gi, 'e');
	slug = slug.replace(/i|í|ì|ỉ|ĩ|ị/gi, 'i');
	slug = slug.replace(/ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ/gi, 'o');
	slug = slug.replace(/ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự/gi, 'u');
	slug = slug.replace(/ý|ỳ|ỷ|ỹ|ỵ/gi, 'y');
	slug = slug.replace(/đ/gi, 'd');
	//Xóa các ký tự đặt biệt
	slug = slug.replace(/\`|\~|\!|\@|\"|\\|\[|\]|\#|\||\$|\%|\^|\&|\*|\(|\)|\+|\=|\,|\.|\/|\?|\>|\<|'|\'|\:|\;|_/gi, '');
	//Đổi khoảng trắng thành ký tự gạch ngang
	slug = slug.replace(/ /gi, '-');
	//Đổi nhiều ký tự gạch ngang liên tiếp thành 1 ký tự gạch ngang
	//Phòng trường hợp người nhập vào quá nhiều ký tự trắng
	slug = slug.replace(/\-\-\-\-\-/gi, '-');
	slug = slug.replace(/\-\-\-\-/gi, '-');
	slug = slug.replace(/\-\-\-/gi, '-');
	slug = slug.replace(/\-\-/gi, '-');
	//Xóa các ký tự gạch ngang ở đầu và cuối
	slug = '@' + slug + '@';
	slug = slug.replace(/\@\-|\-\@|\@/gi, '');
	return slug;
} 

function loadimg() {
	$('#imgloading').show();	

	// $('#loading>div').css('width',0);
	// $('#loading>div').show();
	// $( "#loading>div" ).animate({
	// 	width: '90%',    
	// }, 300);
}

function unloadimg() {
	// $( "#loading>div" ).animate({
	//     width: '100%',    
	// }, 100, function() {
	// 		setTimeout( function(){ 
	// 	        $('#loading>div').hide();				
	// 	    }, 300)
	// });
	// alert('1');

	setTimeout( function(){ 
        $( "#imgloading" ).animate({
		    opacity: 0,    
		  }, 500,function () {
		  		$('#imgloading').hide();	
		  		$('#imgloading').css('opacity',1);
		  });
    }, 500);
		
}


function poprecycle(idmodal = '') {
	var sop = $('.popupalert .thanhcong').length;                         
    $('.popupalert').append('<div class=thanhcong'+sop+'><div class="thanhcong"><i>Thông báo</i><br/>Đã chuyển vào thùng rác!</div></div>');                        
    $('.thanhcong'+sop+'').fadeOut(0, function() {
    	$('.thanhcong'+sop+'').fadeIn( 500, function() {
    		$('.thanhcong'+sop+'').fadeTo(4000, 500).slideUp(500, function(){
			    $('.thanhcong'+sop+'').remove();        
			});
		 });
	});   

    if(idmodal != ''){
	    setTimeout( function(){ 
	        $('#'+idmodal).modal('hide');
	    }, 500);
	    
	    setTimeout( function(){ 
	         $('#'+idmodal+'.modal-body div').html('');
	    }, 1000);
	}

    //$('#imgloading').hide();
}

function popthanhcong(ten,idmodal) {
	var sop = $('.popupalert .thanhcong').length;     
	// prepend                    
    $('.popupalert').append('<div class=thanhcong'+sop+'><div class="thanhcong"><i>Thông báo</i><br/>Thao tác <b>'+ten+'</b> thành công!</div></div>');                           
    $('.thanhcong'+sop+'').fadeOut(0, function() {
    	$('.thanhcong'+sop+'').fadeIn( 500, function() {
    		$('.thanhcong'+sop+'').fadeTo(4000, 500).slideUp(500, function(){
			    $('.thanhcong'+sop+'').remove();        
			});
		 });
	});    
    setTimeout( function(){ 
        $(idmodal).modal('hide');
    }, 500)
    // setTimeout( function(){ 
    //     unloadimg();
    // }, 700) 
    //$('#imgloading').hide();

    setTimeout( function(){    	
    	if(idmodal != '#'){
         	$(idmodal+' .modal-body div').html('');

    	}
    }, 1000) 
}

function popthatbai(ten) {
	var sop = $('.popupalert .thatbai').length;                         
    $('.popupalert').append('<div class=thatbai'+sop+'><div class="thatbai"><i>Thông báo</i><br/>Thao tác <b>'+ten+'</b> thất bại!</div></div>');                        
    $('.thatbai'+sop+'').fadeOut(0, function() {
    	$('.thatbai'+sop+'').fadeIn( 500, function() {
    		$('.thatbai'+sop+'').fadeTo(3000, 500).slideUp(500, function(){
		        $('.thatbai'+sop+'').remove();
		    });
		 });
	});
	unloadimg();
    //$('#imgloading').hide();
}

function poploi() {
	var sop = $('.popupalert .thatbai').length;                         
    $('.popupalert').append('<div class=thatbai'+sop+'><div class="thatbai"><i>Thông báo</i><br/>Thao tác không thành công.</div></div>');                        
           
    $('.thatbai'+sop+'').fadeOut(0, function() {
    	$('.thatbai'+sop+'').fadeIn( 500, function() {
    		$('.thatbai'+sop+'').fadeTo(3000, 500).slideUp(500, function(){
		        $('.thatbai'+sop+'').remove();
		    });
		 });
	});
	unloadimg();
    //$('#imgloading').hide();
}

//Khi form chưa điền đầy
function poploiform(errors = '') {
	var sop = $('.popupalert .thatbai').length;  
	if(sop < 1){
	    $('.popupalert').append('<div class=thatbai'+sop+'><div class="thatbai"><i>Thông báo</i><br/>Có '+errors+' lỗi.</div></div>');                        
	           
	    $('.thatbai'+sop+'').fadeOut(0, function() {
	    	$('.thatbai'+sop+'').fadeIn( 500, function() {
	    		$('.thatbai'+sop+'').fadeTo(3000, 500).slideUp(500, function(){
			        $('.thatbai'+sop+'').remove();
			    });
			 });
		});
		unloadimg();
	}
    //$('#imgloading').hide();
}



function popno() {
	var sop = $('.popupalert .thatbai').length;                         
    $('.popupalert').append('<div class=thatbai'+sop+'><div class="thatbai"><i>Thông báo</i><br/>Bạn không có quyền.</div></div>');                        
           
    $('.thatbai'+sop+'').fadeOut(0, function() {
    	$('.thatbai'+sop+'').fadeIn( 500, function() {
    		$('.thatbai'+sop+'').fadeTo(3000, 500).slideUp(500, function(){
		        $('.thatbai'+sop+'').remove();
		    });
		 });
	});
	unloadimg();
    //$('#imgloading').hide();
}




function getParameterByUrl(name, url) {
    if (!url) {
      url = window.location.href;
    }
    name = name.replace(/[\[\]]/g, '\\$&');
    var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, ' '));
}


 function updateQueryStringParameter(uri, key, value) { 
 	// alert(value);
 	value = value.replace(/'/g, '');
 	value = value.replace(/%22/g, '');
 	value = value.replace(/%25/g, '');
 	// alert(value);

 	uri = decodeURIComponent(uri);
 	if(key.indexOf('[]') > 0){
	    while(uri.indexOf(key) > 0){
	  		uri = xoamangurl(uri,key);   
	    }
	}else{
		value = encodeURIComponent(value);	
	}

  var re = new RegExp('([?&])' + key + '=.*?(&|#|$)', 'i');
  if( value === undefined ) {
    if (uri.match(re)) {
        return uri.replace(re, '$1$2');
    } else {
        return uri;
    }
  } else {
    if (uri.match(re)) {
        return uri.replace(re, '$1' + key + '=' + value + '$2');
    } else {
    var hash =  '';
    if( uri.indexOf('#') !== -1 ){
        hash = uri.replace(/.*#/, '#');
        uri = uri.replace(/#.*/, '');
    }
    var separator = uri.indexOf('?') !== -1 ? '&' : '?';    
    return uri + separator + key + '=' + value + hash;
  }
  }  
}

function xoamangurl(str,key){
	key = key.replace('[]', '\\[\\]'); 
	res = str.match(RegExp('([?&])' + key + '=.*?(&|#|$)', 'g'))[0];    
    if (res.length < 1){    	
    	return str;
    }    
    var kt = '';
    if(res.substring(res.length - 1, res.length) == '&'){
      	kt = '&';
     }
    str = str.replace(res, kt);
     return str;
    
}

