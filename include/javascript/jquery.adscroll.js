$(function(){
    $.fn.extend({
	    ad_scroll:function(){
			var li_height=49;
			var pelem=this;
			var level=parseInt($(pelem).children("ul").children("li").size()/3);
			level=$(pelem).children("ul").children("li").size()/3>level?level+1:level;
			
			this.ad_test=function()
			{
				if(level<=1) return false;
				return true;
			}
			this.ad_init=function()
			{
				with($(pelem))
				{
					data("direction","top");
					data("now_level",1);
				}
			}
			this.start_scroll=function()
			{
				setInterval(
				    function(){
				        var now_level=parseInt($(pelem).data("now_level"));
						var now_dir=$(pelem).data("direction");
						if(now_dir=="top")    //现在是向上滚动
						{
							now_level++;
							if(now_level>level)
							{
								now_level>level && $(pelem).data("now_level",level-1);
								$(pelem).children("ul").animate({"top":"+="+li_height+"px"});
								$(pelem).data("direction","down");
							    return;
							}
							$(pelem).children("ul").animate({"top":"-="+li_height+"px"});
							$(pelem).data("now_level",now_level);
						}
						else
						{
							now_level--;
							if(now_level<1)
							{
								now_level<1 && $(pelem).data("now_level",1+1);
								$(pelem).children("ul").animate({"top":"-="+li_height+"px"});
								$(pelem).data("direction","top");
								return;
							}
							$(pelem).children("ul").animate({"top":"+="+li_height+"px"});
							$(pelem).data("now_level",now_level);
						}
				    },
					5000
				);
			}
			this.set_button_func=function()
			{
				$(pelem).children("span").children("a.click_up").click(function(e){
					e.preventDefault();   
			        var now_level=parseInt($(pelem).data("now_level"));
					if(now_level>=level) return;
					now_level++;
					$(pelem).children("ul").animate({"top":"-="+li_height+"px"});
					$(pelem).data("now_level",now_level);
				});
				$(pelem).children("span").children("a.click_down").click(function(e){
					e.preventDefault();   
			        var now_level=parseInt($(pelem).data("now_level"));
					if(now_level<=1) return;
					now_level--;
					$(pelem).children("ul").animate({"top":"+="+li_height+"px"});
					$(pelem).data("now_level",now_level);
				});
			}
			
			
			//$(this).children("ul").animate({"top":"-=46px"});
			if(!this.ad_test()) return;
			this.ad_init();
			this.start_scroll();
			this.set_button_func();
		    
		}    
	});   
});