
<meta name="viewport" content="width=device-width, initial-scale=1,minimum-scale=1, maximum-scale=1"/>
<!-- 
<meta content="https://i-kinhdoanh.vnecdn.net/2017/09/02/a-tb-can-ho-mot-ty-dong-TP-9191-1504339398_500x300.jpg" itemprop="thumbnailUrl" property="og:image" />
 -->
<?php

//https://developers.facebook.com/tools/explorer/145634995501895/?method=GET&path=me&version=v2.10

// https://developers.facebook.com/tools/debug/accesstoken/?q=EAAAAUaZA8jlABAHdGNYBuIZBCoP4gKDQ29DSAFdO45PCyKX2ZAqnxIVh4EqGaVb2RKUwmc7UFbSSQGXX3dOAOwNtTTRqOxMHZByaR5Ge4I69H0egZBKTupfpJG0NIVEsZBcrOMySt0eGAKJKy1aX8oz3vT3l31QFv6ZBhPado4NUK6qjuBmsijFvsyf4ga8CjsZD&version=v2.10
$id = isset($_GET["_"]) ? htmlspecialchars($_GET["_"]) : '0' ;

$id = '100002868737003_1193740377398242';

$json =  file_get_contents("https://graph.facebook.com/v2.10/".$id."/comments?access_token=EAAAAUaZA8jlABAHdGNYBuIZBCoP4gKDQ29DSAFdO45PCyKX2ZAqnxIVh4EqGaVb2RKUwmc7UFbSSQGXX3dOAOwNtTTRqOxMHZByaR5Ge4I69H0egZBKTupfpJG0NIVEsZBcrOMySt0eGAKJKy1aX8oz3vT3l31QFv6ZBhPado4NUK6qjuBmsijFvsyf4ga8CjsZD&debug=all&format=json&method=get&pretty=0&suppress_http_code=1");

$jsonlike =  file_get_contents("https://graph.facebook.com/v2.10/".$id."/reactions?access_token=EAAAAUaZA8jlABAHdGNYBuIZBCoP4gKDQ29DSAFdO45PCyKX2ZAqnxIVh4EqGaVb2RKUwmc7UFbSSQGXX3dOAOwNtTTRqOxMHZByaR5Ge4I69H0egZBKTupfpJG0NIVEsZBcrOMySt0eGAKJKy1aX8oz3vT3l31QFv6ZBhPado4NUK6qjuBmsijFvsyf4ga8CjsZD&debug=all&format=json&method=get&pretty=0&suppress_http_code=1");

// reactions

// $maxacnhan = 'Hay qua :)';
// $maxacnhan = 'Hay quA :)';
// $maxacnhan = 'Hay qUa :)';
// $maxacnhan = 'Hay Qua :)';
// $maxacnhan = 'HaY qua :)';

$maxacnhan = 'Thanks:-(:(:[=(';

$arr = json_decode($json);
$arrlike = json_decode($jsonlike);

// print_r($arr);
// die;

$timcomment = $maxacnhan;

// echo (string)$arr;
$GLOBALS['showdebug'] = '';
// $GLOBALS['showdebug'] = 'ok';


$tenid = searchcomment($arr,$timcomment);
if($tenid == null){
	// echo 'Bạn là người dùng hay là robots?<br/>';
	// echo 'Vui lòng quay lại bài trước Comment mã xác nhận.<br/>';
	echo '<h2>Bạn chưa tương tác.<br/>';
	// echo 'Gợi ý: <h2 onClick="cptclip(this.innerHTML)">' . $maxacnhan. '</h2>';
	// echo 'Gợi ý: <input onClick="cptclip(this.value) type="text" value="' . $maxacnhan. '" />';
	echo 'Xuống dưới copy chữ bôi vàng, rồi dán vào bài trước cho nhanh</h2>';
	
}else{
	$fbid = $tenid['id'];
	$fbname = $tenid['name'];

	// echo $fbid.'<br/>';
	// echo $fbname;

	echo '<br/><br/>';
	if(searchlike($arrlike,$fbid) == 1){
		echo 'Đã like';
	}else{
		echo 'Bạn chưa like, vui lòng quay lại like bài.';
	}
}


?>
<div id="ndung" style="background: #FF0;height: 50px;" class="break_word">
	<h1>Thanks:-(:(:[=(</h1>
</div>
<h2><button onClick="cli()">Ấn vào đây để copy cho nhanh</button></h2>

<script src="http://localhost/20170715/ad/assets/833a3110/jquery.js"></script>

<!-- <script src="http://www.seabreezecomputers.com/tips/copy2clipboard.js"></script> -->


<script type="text/javascript">



	function cli(){
		select_all_and_copy(document.getElementById('ndung'));
	}


	function cptclip(datacopy) {    
		alert(datacopy);
	    var a = document.createElement("input");
	    a.setAttribute("value", datacopy);
	    document.body.appendChild(a);
	    a.select();
	    document.execCommand("copy");
	    document.body.removeChild(a);
	}
</script>



<?php

function searchcomment($arr,$timcomment)
{	
	$dung = '';
	foreach ($arr as $key => $value) {	
		// echo '<pre>';
		// 		print_r($value);
		if($key == 'data'){
		foreach ($value as $key2 => $value2) {		
			foreach ($value2 as $key3 => $value3) {	
			    if($key3 == 'from'){
					foreach ($value3 as $key4 => $value4) {				
						if($key4 == 'name'){
							$ten = (string)($value4);
						}
						if($key4 == 'id'){				
							$idfb = (string)$value4;				
						}
					}
				}
				if($key3 == 'message'){				
					$code = (string)$value3;					
					if (strpos($code, $timcomment) !== false) {
						$return = [
							'id' => $idfb,
							'name' => $ten
						];
						return $return;
					}
				}					
			}		
			if($GLOBALS['showdebug']  == 'ok'){
				echo $idfb .' - '.$ten .' - '. $code;
		  		echo '<br/>';
		  	}
		}
		}else{	
			foreach ($value as $key => $value2) {
				// echo '<pre>';
				// print_r($value2);
				if($key == 'next'){
					$next = $value2;
					$jsonnext =  file_get_contents($next);
					$arrnext = json_decode($jsonnext);
					$return = searchcomment($arrnext,$timcomment);
					return $return;
				}
			}
		}
	}
}





function searchlike($arrlike,$fbid)
{
	foreach ($arrlike as $key => $value) {	
		// echo '<pre>';
		// 		print_r($value);
		if($key == 'data'){
			foreach ($value as $key2 => $value2) {		
				foreach ($value2 as $key3 => $value3) {				    
					if($key3 == 'name'){
						$ten = (string)($value3);
					}
					if($key3 == 'id'){				
						$idfb = (string)$value3;	
						if ($idfb == $fbid) return 1;			
					}			
				}		
				if($GLOBALS['showdebug']  == 'ok'){
					echo $idfb .' - '.$ten;
		    		echo '<br/>';
		    	}
			}
		}else{	
			foreach ($value as $key => $value2) {				
				if($key == 'next'){
					$next = $value2;
					$jsonnext =  file_get_contents($next);
					$arrnext = json_decode($jsonnext);
					$return = searchlike($arrnext,$fbid);
					return $return;
				}
			}
		}
		// die;
	}
	return 0;
}


?> 


<script type="text/javascript">
	
function tooltip(el, message) {
    var scrollLeft = document.body.scrollLeft || document.documentElement.scrollLeft;
    var scrollTop = document.body.scrollTop || document.documentElement.scrollTop;
    var x = parseInt(el.getBoundingClientRect().left) + scrollLeft + 10;
    var y = parseInt(el.getBoundingClientRect().top) + scrollTop + 10;
    if (!document.getElementById("copy_tooltip")) {
        var tooltip = document.createElement('div');
        tooltip.id = "copy_tooltip";
        tooltip.style.position = "absolute";
        tooltip.style.border = "1px solid black";
        tooltip.style.background = "#dbdb00";
        tooltip.style.opacity = 1;
        tooltip.style.transition = "opacity 0.3s";
        document.body.appendChild(tooltip);
    } else {
        var tooltip = document.getElementById("copy_tooltip")
    }
    tooltip.style.opacity = 1;
    tooltip.style.left = x + "px";
    tooltip.style.top = y + "px";
    tooltip.innerHTML = message;
    setTimeout(function() {
        tooltip.style.opacity = 0;
    }, 2000);
}

function paste(el) {
    if (window.clipboardData) {
        // IE
        el.value = window.clipboardData.getData('Text');
        el.innerHTML = window.clipboardData.getData('Text');
    } else if (window.getSelection && document.createRange) {
        // non-IE
        if (el.tagName.match(/textarea|input/i) && el.value.length < 1)
            el.value = " ";
            // iOS needs element not to be empty to select it and pop up 'paste' button
        else if (el.innerHTML.length < 1)
            el.innerHTML = "&nbsp;";
        // iOS needs element not to be empty to select it and pop up 'paste' button
        var editable = el.contentEditable;
        // Record contentEditable status of element
        var readOnly = el.readOnly;
        // Record readOnly status of element
        el.contentEditable = true;
        // iOS will only select text on non-form elements if contentEditable = true;
        el.readOnly = false;
        // iOS will not select in a read only form element
        var range = document.createRange();
        range.selectNodeContents(el);
        var sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
        if (el.nodeName == "TEXTAREA" || el.nodeName == "INPUT")
            el.select();
        // Firefox will only select a form element with select()
        if (el.setSelectionRange && navigator.userAgent.match(/ipad|ipod|iphone/i))
            el.setSelectionRange(0, 999999);
        // iOS only selects "form" elements with SelectionRange
        if (document.queryCommandSupported("paste")) {
            var successful = document.execCommand('Paste');
            if (successful)
                tooltip(el, "Pasted.");
            else {
                if (navigator.userAgent.match(/android/i) && navigator.userAgent.match(/chrome/i)) {
                    tooltip(el, "Click blue tab then click Paste");

                    if (el.tagName.match(/textarea|input/i)) {
                        el.value = " ";
                        el.focus();
                        el.setSelectionRange(0, 0);
                    } else
                        el.innerHTML = "";

                } else
                    tooltip(el, "Press CTRL-V to paste");
            }
        } else {
            if (!navigator.userAgent.match(/ipad|ipod|iphone|android|silk/i))
                tooltip(el, "Press CTRL-V to paste");
        }
        el.contentEditable = editable;
        // Restore previous contentEditable status
        el.readOnly = readOnly;
        // Restore previous readOnly status
    }
}

function select_all_and_copy(el) {
    // Copy textarea, pre, div, etc.
    if (document.body.createTextRange) {
        // IE 
        var textRange = document.body.createTextRange();
        textRange.moveToElementText(el);
        textRange.select();
        textRange.execCommand("Copy");
        tooltip(el, "Copied!");
    } else if (window.getSelection && document.createRange) {
        // non-IE
        var editable = el.contentEditable;
        // Record contentEditable status of element
        var readOnly = el.readOnly;
        // Record readOnly status of element
        el.contentEditable = true;
        // iOS will only select text on non-form elements if contentEditable = true;
        el.readOnly = true;
        // iOS will not select in a read only form element
        var range = document.createRange();
        range.selectNodeContents(el);
        var sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);

		// alert(el.nodeName);

        // Does not work for Firefox if a textarea or input
        // if (el.nodeName == "TEXTAREA" || el.nodeName == "INPUT")
        //     el.select();
        // Firefox will only select a form element with select()
        if (el.setSelectionRange && navigator.userAgent.match(/ipad|ipod|iphone/i))
            el.setSelectionRange(0, 999999);
        // iOS only selects "form" elements with SelectionRange
        el.contentEditable = editable;
        // Restore previous contentEditable status
        el.readOnly = true;
        // Restore previous readOnly status 
        if (document.queryCommandSupported("copy")) {
            var successful = document.execCommand('copy');
            if (successful)
                tooltip(el, "Đã copy");
            else            	
                tooltip(el, "Press CTRL+C to copy");
        } else {
        	tooltip(el, "chọn Sao chép");
            if (!navigator.userAgent.match(/ipad|ipod|iphone|android|silk/i)){            	
                tooltip(el, "Press CTRL+C to copy");
            }
        }

        sel = null;
        el = null;
        range = null;
    }
}
// end function select_all_and_copy(el) 

function make_copy_button(el) {
    //var copy_btn = document.createElement('button');
    //copy_btn.type = "button";
    var copy_btn = document.createElement('span');
    copy_btn.style.border = "1px solid black";
    copy_btn.style.padding = "5px";
    copy_btn.style.cursor = "pointer";
    copy_btn.style.display = "inline-block";
    copy_btn.style.background = "lightgrey";

    el.parentNode.insertBefore(copy_btn, el.nextSibling);
    copy_btn.onclick = function() {
        select_all_and_copy(el);
    }
    ;

    //if (document.queryCommandSupported("copy") || parseInt(navigator.userAgent.match(/Chrom(e|ium)\/([0-9]+)\./)[2]) >= 42)
    // Above caused: TypeError: 'null' is not an object (evaluating 'navigator.userAgent.match(/Chrom(e|ium)\/([0-9]+)\./)[2]') in Safari
    if (document.queryCommandSupported("copy")) {
        // Desktop: Copy works with IE 4+, Chrome 42+, Firefox 41+, Opera 29+
        // Mobile: Copy works with Chrome for Android 42+, Firefox Mobile 41+	
        //copy_btn.value = "Copy to Clipboard";
        copy_btn.innerHTML = "Copy to Clipboard";
    } else {
        // Select only for Safari and older Chrome, Firefox and Opera
        /* Mobile:
				Android Browser: Selects all and pops up "Copy" button
				iOS Safari: Selects all and pops up "Copy" button
				iOS Chrome: Form elements: Selects all and pops up "Copy" button 
		*/
        //copy_btn.value = "Select All";
        copy_btn.innerHTML = "Select All";

    }
}
/* Note: document.queryCommandSupported("copy") should return "true" on browsers that support copy
	but there was a bug in Chrome versions 42 to 47 that makes it return "false".  So in those
	versions of Chrome feature detection does not work!
	See https://code.google.com/p/chromium/issues/detail?id=476508
*/


</script>