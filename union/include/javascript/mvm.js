$(function(){
    $("a[@rel=tab_head]").hover(
	    function(e){
			var n=$(this).attr("n");
		    var index=$("a[@rel=tab_head][n="+n+"]").index(this);
			$("a[@rel=tab_head][n="+n+"]").removeClass("hover");
			$(this).addClass("hover");
			
			var t=$(this).attr("t");
			filter="[@rel=tab_body][@n="+n+"]";
			if(t) filter=t+filter;
			
			$(filter).hide();
			$(filter).eq(index).show();
		},
		function(e){}
	);//end hover  
	
	$("a[@rel=fav]").click(function(e){
	    e.preventDefault();
		var t=$(this).attr("t");
		var uid=$(this).attr("uid");
		var module=$(this).attr("module");
		var gt=$(this).attr("gt");
		$.post(
	        "ajax.php?action=fav&rnd="+Math.random(),
			{
			    t:t,
				uid:uid,
				module:module,
				gt:gt
			},
			function(msg){
			    alert(msg);
			}
		);//end get
	});//end click
	
	$.extend({
	    SetFriendEvent:function(){
		    $("a[@rel=friend]").unbind("click");
			$("a[@rel=friend]").click(function(e){
		        e.preventDefault();
				var m_id=$(this).attr("m_id");
				$.get(
				    "ajax.php?action=add_friend&m_id="+encodeURI(m_id),
					function(msg){
					    alert(msg);
					}
				);//end get
			});//end click
		}
	});//end extend
});

function currency(price_sign,price)
{
	price=parseFloat(price);
	if(isNaN(price)) price=0.00;
	price=price.toFixed(2);
	return price_sign+price;
}

function IsMobile()
{
    var userAgentInfo = navigator.userAgent;
    var Agents = ["Android", "iPhone","SymbianOS", "Windows Phone","iPad", "iPod"];
    var flag = false;
    for (var v = 0; v < Agents.length; v++)
	{
        if (userAgentInfo.indexOf(Agents[v]) > 0)
		{
            flag = true;
            break;
        }
    }
    return flag;
}

var kindecitor_items=['source', '|', 'undo', 'redo', '|', 'preview', 'print', 'template', 'cut', 'copy', 'paste',
                      'plainpaste', 'wordpaste', '|', 'justifyleft', 'justifycenter', 'justifyright',
                      'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript',
                      'superscript', 'clearhtml', 'quickformat', 'selectall', '|', 'fullscreen', '/',
                      'formatblock', 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold',
                      'italic', 'underline', 'strikethrough', 'lineheight', 'removeformat', '|', 'image',
                      'flash', 'table', 'hr', 'emoticons', 'baidumap', 'pagebreak',
                      'anchor', 'link', 'unlink'];

var MVMUrl = {
    encode : function (string) {  
        return escape(this._utf8_encode(string));  
    },
    decode : function (string) {  
        return this._utf8_decode(unescape(string));  
    }, 
    _utf8_encode : function (string) {  
        string = string.replace(/\r\n/g,"\n");  
        var utftext = "";  
        for (var n = 0; n < string.length; n++) {  
            var c = string.charCodeAt(n);  
            if (c < 128) {  
                utftext += String.fromCharCode(c);  
            }  
            else if((c > 127) && (c < 2048)) {  
                utftext += String.fromCharCode((c >> 6) | 192);  
                utftext += String.fromCharCode((c & 63) | 128);  
            }  
            else {  
                utftext += String.fromCharCode((c >> 12) | 224);  
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);  
                utftext += String.fromCharCode((c & 63) | 128);  
            }  
   
        }
        return utftext;  
    },
    _utf8_decode : function (utftext) {  
        var string = "";  
        var i = 0;  
        var c = c1 = c2 = 0;  
        while ( i < utftext.length ) {  
            c = utftext.charCodeAt(i);  
            if (c < 128) {  
                string += String.fromCharCode(c);  
                i++;  
            }  
            else if((c > 191) && (c < 224)) {  
                c2 = utftext.charCodeAt(i+1);  
                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));  
                i += 2;  
            }  
            else {  
                c2 = utftext.charCodeAt(i+1);  
                c3 = utftext.charCodeAt(i+2);  
                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));  
                i += 3;  
            }  
        }  
        return string;  
    }  
}//end MVMUrl

function MVMBase64() {  
    var _keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";  
    this.encode = function (input) {  
        var output = "";  
        var chr1, chr2, chr3, enc1, enc2, enc3, enc4;  
        var i = 0;  
        input = _utf8_encode(input);  
        while (i < input.length) {  
            chr1 = input.charCodeAt(i++);  
            chr2 = input.charCodeAt(i++);  
            chr3 = input.charCodeAt(i++);  
            enc1 = chr1 >> 2;  
            enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);  
            enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);  
            enc4 = chr3 & 63;  
            if (isNaN(chr2)) {  
                enc3 = enc4 = 64;  
            } else if (isNaN(chr3)) {  
                enc4 = 64;  
            }  
            output = output +  
            _keyStr.charAt(enc1) + _keyStr.charAt(enc2) +  
            _keyStr.charAt(enc3) + _keyStr.charAt(enc4);  
        }  
        return output;  
    }
    this.decode = function (input) {  
        var output = "";  
        var chr1, chr2, chr3;  
        var enc1, enc2, enc3, enc4;  
        var i = 0;  
        input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");  
        while (i < input.length) {  
            enc1 = _keyStr.indexOf(input.charAt(i++));  
            enc2 = _keyStr.indexOf(input.charAt(i++));  
            enc3 = _keyStr.indexOf(input.charAt(i++));  
            enc4 = _keyStr.indexOf(input.charAt(i++));  
            chr1 = (enc1 << 2) | (enc2 >> 4);  
            chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);  
            chr3 = ((enc3 & 3) << 6) | enc4;  
            output = output + String.fromCharCode(chr1);  
            if (enc3 != 64) {  
                output = output + String.fromCharCode(chr2);  
            }  
            if (enc4 != 64) {  
                output = output + String.fromCharCode(chr3);  
            }  
        }  
        output = _utf8_decode(output);  
        return output;  
    }
    _utf8_encode = function (string) {  
        string = string.replace(/\r\n/g,"\n");  
        var utftext = "";  
        for (var n = 0; n < string.length; n++) {  
            var c = string.charCodeAt(n);  
            if (c < 128) {  
                utftext += String.fromCharCode(c);  
            } else if((c > 127) && (c < 2048)) {  
                utftext += String.fromCharCode((c >> 6) | 192);  
                utftext += String.fromCharCode((c & 63) | 128);  
            } else {  
                utftext += String.fromCharCode((c >> 12) | 224);  
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);  
                utftext += String.fromCharCode((c & 63) | 128);  
            }  
   
        }  
        return utftext;  
    }
    _utf8_decode = function (utftext) {  
        var string = "";  
        var i = 0;  
        var c = c1 = c2 = 0;  
        while ( i < utftext.length ) {  
            c = utftext.charCodeAt(i);  
            if (c < 128) {  
                string += String.fromCharCode(c);  
                i++;  
            } else if((c > 191) && (c < 224)) {  
                c2 = utftext.charCodeAt(i+1);  
                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));  
                i += 2;  
            } else {  
                c2 = utftext.charCodeAt(i+1);  
                c3 = utftext.charCodeAt(i+2);  
                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));  
                i += 3;  
            }  
        }  
        return string;  
    }  
}//MVMBase64