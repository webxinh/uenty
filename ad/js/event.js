
//Xóa nhóm huyện/quận (Tiền ship)
$(document).on('click','.tt-remove',function(){                            
    if(confirm('Bạn có muốn xóa nhóm giá ship?')){
        $(this).parents('.tttg').remove()
    }
})
//Xóa tỉnh/thành (Tiền ship)
$(document).on('click','.t-remove',function(){                            
    if(confirm('Bạn có muốn xóa tỉnh/thành phố này?')){
        $(this).parents('.pa-i').remove()
    }
})

//Add quan/huyen
$(document).on('click','.tt-add',function(e){
    tt_add(this,e)
})
//Add Tinh/thanh pho
$(document).on('click','.t-add',function(e){
    t_add(this, e)
})




//Next step ở trong form sản phẩm
$(document).on('click','.next-step',function(){
    next_step(this)
})



//Check thông số ở trong sp, mở thêm more info

$(document).on('click','.c_ts',function(){
    more_info(this)
})

//Xóa tiền tệ
$(document).on('click','.tttg-remove',function(){
    $(this).parents('.tttg-one').remove()
})




//Thêm option
$(document).on('click','.add-pbsp-option',function(){
    addpbsp_option(event.target,this);
})
//Xóa thông số phiên bản sp
$(document).on('click','.del-pbsp-option',function(){
    if(confirm('Bạn muốn xóa option này?')){
        $(this).parents('.l-option').remove()
    }
})

//Thêm thông số pbsp
$(document).on('click','.add-pbsp',function(){
    addpbsp(event.target,this);
})

//Xóa thông số phiên bản sp
$(document).on('click','.del-pbsp',function(){
    if(confirm('Bạn muốn xóa thông số này?')){
        $(this).parents('.l-pbsp').remove()
    }
})






//Thêm album ảnh vào sản phẩm
$(document).on('click','.add-album',function(){
    addimg(event.target,this);
})

//Xóa album ảnh sản pphaarm
$(document).on('click','.del-album',function(){
    if(confirm('Bạn muốn xóa album ảnh này?')){
        $(this).parents('.l-album').remove()
    }
})


//Chọn màu icon
$(document).on('click', '.cmau', function() {     
    micon(this);
}); 
// $(document).on('mousemove', function (e) { 
//     console.log('Mouse move');
// });

// $(document).on('click', 'form button[type=submit]', function() {     
    // $(this).attr('type','button'); 
// }); 


//Thay đổi ảnh cove sản phẩm
$(document).on('DOMSubtreeModified', "#editable", function(){
    var imgfirst = $('#editable li').first().find('img');
    var link = $(imgfirst).attr('src');
    if(typeof link !== 'undefined'){
        link = link.replace('thumb/75/75/','uploads/');
        $('#imgcove>.image').html('<img src="'+link+'">');
    }else{
        $('#imgcove>.image').html('');
    }
});

//Thay đổi ảnh cove thông số sản phẩm
$(document).on('DOMSubtreeModified', "#editable_ts", function(){
    var imgfirst = $('#editable_ts li').first().find('img');
    var link = $(imgfirst).attr('src');
    if(typeof link !== 'undefined'){
        link = link.replace('thumb/75/75/','uploads/');
        $('#imgcove_ts>.image').html('<img src="'+link+'">');
    }else{
        $('#imgcove_ts>.image').html('');
    }
});



$(document).on('click', '.opennew', function() {     
    window.open('http://' + $(this).attr('d-url'));
}); 



//Xóa trong Detail View
$(document).on('click', '.dtview .br', function() {  
    buttonrecycle(event.target,this);  
});






runhelp = 0;

function addhelf(current) {    
    var newparent = $(current).parent().find('div.le');    
    $(current).appendTo(newparent);
    // $(newparent).append($(current).context.outerHTML);
}


$(document).on("DOMNodeInserted" ,  function(e){

    target = $(e.target);
    if($(target).length > 0){
    	// console.log('123');
        $('input[type=text].nbr').number(true);

        if($(target).find('.pj').length > 0){    
        	// console.log('1');        
            $(target).find('.pj').each(function () {                
                if($(this).hasClass('tuypjed') === false && $(this).hasClass('pjr') === false){ 
                    var idpj = $(this).attr(d_i);                     
                    changeipse(idpj);  
                    changea(idpj);                    
                    footertable(idpj);                    
                    headertable(idpj);  
                    cleartimkiem(idpj);  
                    $(this).addClass('tuypjed');    

                    
                }
            })
        }

        // console.log($(target));

        if($(target).find(".mulr").length > 0){           
        	// console.log('2');
            $(target).find(".mulr").each(function () {                
                if($(this).hasClass('tuymulred') === false){                    
                    selectmur($(this).attr('id'));  
                    $(this).addClass('tuymulred');
                }
            })            
        }

        

        if($(target).find(".bohelp").length > 0){      
        	// console.log('3');      
            $(target).find(".bohelp").each(function () {                 
                if(runhelp == 0){                    
                    interval = setInterval(function() {                                                                          
                        runhelp = 1;                               
                        elem = $('.bohelp');                            
                        if (elem.css('opacity') == 1) {
                            $( ".bohelp" ).animate({
                                opacity: 0.4
                            });
                        } else {
                            $( ".bohelp" ).animate({
                                opacity: 1
                            });
                        }                         
                    }, 500);                        
                }
                
            })
        }

    }

});





//Đồng ý Chọn ảnh đại diện
$(document).on('click','.chona',function () {
    ca(this);
});
//Đồng ý Chọn icon
$(document).on('click','.choni',function () {
    cicon(this);
});


//Chọn ảnh
$(document).on('click','.chimg',function () {
    chimg(this);
});
//Chọn icon
$(document).on('click','.chicon',function () {
    chicon(this);
});



$(document).on('click','button.ehelp',function () {
    Hel = '';    
    $('.khelp').remove();    
    $('.phelp').removeClass('phelp');
    ehelpf();

    clearInterval(interval);
    runhelp = 0;
})

$(document).on('click','button.bhelp',function () {
    bhelpf(this);
})

//Recycle
$(document).on('click', '.bd', function() { 
    recyclechung(event.target,this,'XÓA','co');
});

$(document).on('click', '.be', function() { 
    recyclechung(event.target,this,'KHÔI PHỤC','co');
}); 

$(document).on('click', '.bda', function() { 
    recyclechung(event.target,this,'XÓA TẤT CẢ','co');
}); 







//Lưu và thêm
$(document).on('click', '.lvt', function() {     
    lvt(this);
});

//Copy link ảnh
$(document).on('click', '.clk', function() { 
    clk(this);
});



//Mở modal
$(document).on('click', '.mb', function() { 
    buttonmodal(this);    
});


//Thay đổi số item trên page
$(document).on('change', '.ipage', function() {
    changeipage(this,event.target);
});


//Lựa chọn nhiều bản ghi để thao tác
$(document).on('change', '.gr input[type=checkbox]', function() { 
    changeinput(event.target,this);
});

//Thao tác với các bản ghi đã chọn
$(document).on('click', '.bra', function() { 
    buttonrecycleall(event.target,this);
});


//Change page
$(document).on('change', 'ul.pagination li.active input', function() {
    if(typeof event !== 'undefined'){         
        changep(event.target,this);
    }
});


//Click vào các link a
$(document).on('click', '.pj a:not(.not-href)', function() {
    // if(!$(this).hasClass('not-href')){
        if(typeof event !== 'undefined'){     
            clicka(event.target,this);
        }
    // }
});



//Sự kiện khi next/back page thì kéo lên trên
$(document).on('click', '.pj ul.pagination', function() {        
    totop();
});

   
//Sự kiện trong menu ở gridview
$(document).on('click', '.gr .ml', function() {  
    showhidden(event.target,this); 
});

$(document).on('click', '.gr .br', function() {  
    buttonrecycle(event.target,this);  
});





//select open body
$(document).on('click', '.mulr-body input[type=radio]', function() { 
   // alert('1')
    changeinputmulr(event.target,this);
});


$(document).on('click', '.mulr-body input[type=checkbox]', function() {     
   // alert('2')
    changeinputmulr(event.target,this);
    ////alert($(this).attr('class'));
});

$(document).on('click', '.brese', function() {
   // alert('3')
    removeselect(event.target,this);
});




$(document).on('input', '.mulr-body input[type=text]', function() {        
   //alert('4')
  changeinputsearch(event.target,this);
}); 



$(document).on('change', '.inpsea', function() {
   //alert('5')
    changeinpsea(event.target,this);
});










//Click vào select multi, mở ra body bên dưới
$(document).on('click', '.mulr-head', function() {
   opbody(this);
})

//Khi click ra ngoài thì đóng body của select
$(document).on('click', 'body', function () {      
    if(typeof event !== 'undefined'){        
        clbody(event.target);
    }
});


//Click btb reload ở trong index
$(document).on('click','.btre', function () {       
    btnrl(this);
});



//Sự kiện sau khi đóng modal
$(document).on('hidden.bs.modal','.modal', function () {            
    afmohi();    
});
//Sự kiện sau khi modal hiện
$(document).on('shown.bs.modal','.modal', function () {     
    modalontop();    
});





//Sự kiện khi click chuột mở menu tại grid
//Chuột trái
$(document).on('click',function(event){ 
    if(typeof event !== 'undefined'){        
        openmenu(event);
    }    
    
});
//Chuột phải
$(document).bind('contextmenu', function(event){  
    if(typeof event !== 'undefined'){       
        openmenu(event);
        event.preventDefault();
    }
});





//Sự kiện khi click nút mở modal trên menu top
$(document).on('click','.pjbm', function () {       
    loadimg();
    addurl($(this).attr(d_i),$(this).attr(d_u));
    buttonmodal($(this),'');
});




//Sự kiện mini menu top
$(document).on('click','#minimenu', function () { 
    mnmenu();
})

//Sự kiện khi di chuột xuống content thì nếu menu đang hide thì đóng lên
$(document).on('mouseenter','#content', function (e) { 
    himn();
});


//Khi menu top đang hide, click vào sẽ sổ xuống, xong lại thu lên
$(document).on('click','.menu-parent>li', function (e) { 
   autohimn(this);
});




//Khi chọn mở 1 tab nội dung
$(document).on('click','.menu-item.pjb', function (e) { 
   clmn(this);
});

//Khi chọn mở 1 tab cấu hình
$(document).on('click','.menu-item.pjb_ch', function (e) { 
   clmn_ch(this);
});





//Sự kiện tự focus đến lỗi trong form
$(document).on('click', '.haserror', function() {    
    aufoerr(this);
});



