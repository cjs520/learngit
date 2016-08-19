//本插件只对img标签有效
//<img src="" path="real pic path" />
//img中必须用css指定高度

$(function(){
    $.fn.extend({
	    lazyload:function(){
			var P=this;
		    var JQ_P=$(this);
			this.clientHeight=0;
			
			this.Init=function(){
			    JQ_P.not("img[@open=open]").each(function(){
				    var offset=$(this).offset();
					$(this).attr("l",offset.left);
					$(this).attr("t",offset.top);
				});//end each
				
				P.clientHeight=document.documentElement.clientHeight;
				P.TestLoad();    //Load the pictures shown in the screen
			};//end Init
			
			this.SetEvents=function(){
			    $(window).scroll(function(){
				    P.TestLoad();
				});//end window scroll
				
				$(window).resize(function(){
				    P.Init();
				});//end window resize
				
			};//end SetEvents
			
			this.TestLoad=function(){
			    JQ_P.not("img[@open=open]").each(function(i){
					var t=parseInt($(this).attr("t"));
						
				    if(isNaN(t)) return true;
				    if(t>P.clientHeight+$(window).scrollTop()) return true;    //this pic has not been visible in the screen yet
					if(t+$(this).height()<$(window).scrollTop()) return true;
						
					$(this).attr("open","open");
					$(this).attr("src",$(this).attr("path"));
				});//end each
			};//end TestLoad
			
			this.Init();
			this.SetEvents();
			
		}//end function lazyload
	});   
});