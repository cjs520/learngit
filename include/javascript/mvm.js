/* $Id : mvm.js www.mvmmall.cn $ */
$(function(){ 
	$("span[@rel=tolink]").siblings("a").hover(
		function(e){
			$(this).siblings("span[@rel=tolink]").show();
		},
		function(e){
			$(this).siblings("span[@rel=tolink]").hide();
		}
	);//end hover
	
	$("a[@rel=apply_page]").click(function(e){
		e.preventDefault();
		var apply_url=$.trim($(this).attr("apply_url"));
		if(apply_url=="")
		{
			alert("URL无效");
			return false;
		}
		
		var p_url=$(".newopen").attr("p_url");
		
		$.ajax({
		    url:apply_url,
			type:"get",
			async:true,
			cache:false,
			data:"p_url="+p_url,
			success:function(html){
				        if(html.indexOf("<!--form content-->")==-1)
						{
							alert(html);
							return false;
						}
				        var o=$(".newopen");
						var o_block=$("#block");
						
						o_block.show();
						$("select").hide();
						o.show();
						o.children("div[@rel=content]").html(html);
						
		                var itop=(document.documentElement.clientHeight-o.height())/2+$(document).scrollTop();
                        var ileft=(document.documentElement.clientWidth-o.width())/2-20+$(document).scrollLeft();
                        o.css({
                            position:"absolute",
                            top:itop+"px",
                            left:ileft+"px"
                        });
						
						o.children("div[@rel=content]").find("form").submit(function(){
						    var b_false=true;
							$(this).find("input[@rel=necessary]").each(function(i){
							    if($.trim($(this).val())=="")
								{
									$(this).siblings("span[@rel=tip]").show();
								    b_false=false;
								}
							});//end each
							return b_false;
						});//end submit
					},
			error:function(o,errStatus,err){
				      alert(errStatus);
					  alert(err);
				  }
		});//end ajax
	});//end click
	
	$("a[@rel=close_apply]").click(function(e){
	    e.preventDefault();
		var o_block=$("#block");
		o_block.fadeOut();
		$(".newopen").hide();
		$("select").show();
	});//end click
	
	$("a[@rel=close_page_error_tip]").click(function(e){
	    e.preventDefault();
		$(this).parent("[@rel=page_error_tip]").hide();
	});//end click
	
	$("a[@rel=ad_apply]").each(function(i){
	    var url="sadmin.php?module=rcm_ad&action=apply";
		if($(this).attr("m")) url+="&m="+$(this).attr("m");
		if($(this).attr("p")) url+="&p="+$(this).attr("p");
		if($(this).attr("op")) url+="&op="+$(this).attr("op");
		if($(this).attr("t")) url+="&t="+$(this).attr("t");
		$(this).attr("href",url);
	});//end click
	
	$("a[@rel=banner]").hover(
	    function(e){
			$(this).siblings("a[@rel=ad_apply]").attr("hide","hide");
		    $(this).siblings("a[@rel=ad_apply]").show();
		},
		function(e){
		    $(this).siblings("a[@rel=ad_apply]").hide();
		}
	);//end hover
	
	$("a[@rel=ad_apply]").hover(
        function(e){
		    $(this).show();
		},
		function(e){
			if($(this).attr("hide")=="hide") $(this).hide();
		}
	);//end hover
	
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
			$(filter).eq(index).css("visibility","visible");
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
	
	$("img[@rel=delay_load]").each(function(i){
	    var src=$(this).attr("path");
		var max_width=$(this).attr("max_width");
		var max_height=$(this).attr("max_height");
		var jq_this=$(this);
		
		max_width=parseInt(max_width);
		if(isNaN(max_width) || max_width<=0) max_width=0;
		max_height=parseInt(max_height);
		if(isNaN(max_height) || max_height<=0) max_height=0;
		
		src=$.trim(src);
		if(src=="") return true;
		var img=new Image();
		img.src=src;
		if(img.complete)
		{
			jq_this.attr("src",img.src);
			if(max_width>0 && img.width>max_width) jq_this.width(max_width);
			if(max_height>0 && img.height>max_height) jq_this.height(max_height);
			return true;
		}
		img.onload=function(){
		    jq_this.attr("src",this.src);
			if(max_width>0 && this.width>max_width) jq_this.width(max_width);
			if(max_height>0 && this.height>max_height) jq_this.height(max_height);
		}//img onload
	});//end each
	
	var friend_lock=false;
	$("a[@rel=friend]").click(function(e){
	    e.preventDefault();
		if(friend_lock) return;
		var m_uid=$(this).attr("m_uid");
		$.ajax({
		    url:"ajax.php?action=make_friend&cmd=confirm&m_uid="+m_uid,
			type:"GET",
			cache:false,
		    success:function(msg){
			    if(msg.indexOf("ERR")!=-1)
				{
					alert(msg);
					friend_lock=false;
					return;
				}
				
				if(!confirm(msg))
				{
					friend_lock=false;
					return;
				}
				
				$.get(
				    "ajax.php?action=make_friend&cmd=make&m_uid="+m_uid+"&rnd="+Math.random(),
					function(msg){
						friend_lock=false;
					    alert(msg);
					}
				);//end get
			}
		});//end ajax
	});//end click
	
	$("img[@rel=code]").trigger("click");
});

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