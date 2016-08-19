//本插件结构<tag><subtag rel="hour"></subtag><subtag rel="minute"></subtag><subtag rel="second"></subtag></tag>

$(function(){
    $.fn.extend({
	    count_down:function(mill_second){
		    mill_second=parseInt(mill_second);
			var pelem=$(this);
			var top=this;
			pelem.data("tid",0);
			
			//函数定义
			this.format=function(num){    //格式化函数
			    if(num<=0) return "00";
				if(num>0 && num<10) return "0"+num;
				else return num;
			}
			this.DoCount=function(){    //开始计时
			    //计算小时
				if(mill_second<=0)
				{
					var tid=pelem.data("tid");
					clearInterval(tid);
				}
				mill_second--;
				
				var tmp_second=mill_second;
				var day=parseInt(tmp_second/86400);
				tmp_second%=86400;
				var hour=parseInt(tmp_second/3600);
				tmp_second%=3600;
				var minute=parseInt(tmp_second/60);    //分
				tmp_second%=60;
				var second=tmp_second;    //秒
				
				day=top.format(day);
				hour=top.format(hour);
				minute=top.format(minute);
				second=top.format(second);
				
				pelem.children("span[@rel=day]").html(day);
				pelem.children("span[@rel=hour]").html(hour);
				pelem.children("span[@rel=minute]").html(minute);
				pelem.children("span[@rel=second]").html(second);
				
			};
			
			//功能实现
			if($(this).children("span[@rel=hour]").size()<=0) return;
			var tid=setInterval(this.DoCount,1000);
			pelem.data("tid",tid);
		}
	});
});