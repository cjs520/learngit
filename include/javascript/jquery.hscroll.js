//<tag id="frame_id"><table></table></tag>
//<tag id="lbutton" /><tag id="rbutton" />

$(function(){
    $.extend({
	    h_scroll:function(frame_id,rbutton,lbutton,line,line_num,td_width){
	    //frame_id为外框id,
		//lbutton为右按钮id（向左滚动）,rbutton为左按钮id,
		//line为几行,line_num为一行个数
		//td_width为一个td的宽度
		    var jq_frame=$("#"+frame_id);
			var jq_lbutton=$("#"+lbutton);
			var jq_rbutton=$("#"+rbutton);
			
			if(jq_frame.size()<=0 || jq_frame.children("table").size()<=0) return;
			var jq_table=jq_frame.children("table");
			var jq_tds=jq_frame.children("table").find("td");
			
			if(!td_width)
			{
			    
			    jq_tds.width(jq_tds.eq(0).width());    //统一各个td宽度
			    var total_width=jq_frame.width();
			    td_width=jq_tds.eq(0).width();
			}
			else
			{
				jq_tds.width(td_width);
			}
			
			var now_pos=$("body").data(frame_id)?$("body").data(frame_id):0;
			now_pos=parseInt(now_pos);
			
			if(jq_lbutton.size()>0)    //设置右按钮（向左滚动）
			{
				jq_lbutton.bind("click",function(e){
				    e.preventDefault();
					if((now_pos+1)*line_num>=jq_tds.size()/line) return;
					now_pos++;
					jq_table.css({
					    "left":-(now_pos*line_num*td_width)+"px"
					});
					$("body").data(frame_id,now_pos);
				});
			}
			
			if(jq_rbutton.size()>0)    //设置左按钮（向右滚动）
			{
				jq_rbutton.bind("click",function(e){
				    e.preventDefault();
					if(now_pos<=0) return;
					now_pos--;
					jq_table.css({
					    "left":-(now_pos*line_num*td_width)+"px"
					});
					$("body").data(frame_id,now_pos);
				});
			}
		} 
	});//end extend
});