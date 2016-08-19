$(function(){
    $.fn.extend({
	    Hint:function(jq_span){
		    var jq_input=$(this);
			var top=this;
			this.rel_jq_span=jq_span;
			this.search_txt="";
			this.last_time=new Date().getTime();
			this.is_searching=false;
			this.stop_search=false;
			this.sel_index=-1;
			
			this.SetEvents=function(){
				top.ClearHintSpan();
				$(document).bind("click",function(){
	                top.rel_jq_span.empty();
					top.rel_jq_span.hide();
	            });//end click
				
				jq_input.unbind("keydown");
				jq_input.bind("keydown",function(e){
				    top.last_time=new Date().getTime();
					
					var a_cnt=top.rel_jq_span.children("a").size();
					if(e.keyCode==38 && a_cnt>0)    //up arrow
					{
						top.sel_index--;
						if(top.sel_index<0) top.sel_index=0;
						
						top.rel_jq_span.children("a").removeClass("hover");
						top.rel_jq_span.children("a").eq(top.sel_index).addClass("hover");
						jq_input.val(top.rel_jq_span.children("a").eq(top.sel_index).attr("title"));
						
						top.stop_search=true;
					}
					else if(e.keyCode==40  && a_cnt>0)    //down arrow
					{
						top.sel_index++;
						if(top.sel_index>=a_cnt) top.sel_index=a_cnt-1;
						
						top.rel_jq_span.children("a").removeClass("hover");
						top.rel_jq_span.children("a").eq(top.sel_index).addClass("hover");
						jq_input.val(top.rel_jq_span.children("a").eq(top.sel_index).attr("title"));
						
						top.stop_search=true;
					}
					else
					{
						top.stop_search=false;
					}
				});//end keydown
				
				setInterval(function(){
					if(top.is_searching) return;    //searching, don't interrupt
					if(top.stop_search) return;
				    if(new Date().getTime()-top.last_time<200) return;    //time span is not enough
					if(jq_input.val().indexOf("请输入")!=-1) return;    //hint keywords
					if($.trim(jq_input.val())==top.search_txt) return;    //search value is the same
					
					top.search_txt=$.trim(jq_input.val());
					if(top.search_txt=="")
					{
						top.ClearHintSpan();
						return;
					}
					
					//start searching
					top.is_searching=true;
					$.ajax({
					    url:top.rel_jq_span.attr("url"),
						type:"POST",
						dataType:"json",
						data:"ps_search="+top.search_txt,
						cache:true,
						success:function(json){
						    top.ClearHintSpan();
							top.rel_jq_span.show();
							var html="";
							$.each(json,function(i){
							    html+="<a href='"+this[1]+"' title='"+this[0]+"' target='_new'>"+this[0].replace(top.search_txt,"<b style='color:black;'>"+top.search_txt+"</b>")+"</a>";
							});//end each
							if(html!="")  top.rel_jq_span.html(html);
							else top.rel_jq_span.hide();
							
							top.sel_index=-1;
						},
						error:function(){
						    top.ClearHintSpan();
							top.rel_jq_span.hide();
						},
						complete:function(){
						    top.is_searching=false;
						}
					});//end ajax
					
					
				},300);
			};//end function SetEvents
			
			this.ClearHintSpan=function(){
			    top.rel_jq_span.empty();
			}//end function ClearHintSpan
			
			this.SetEvents();
		}
	});//end extend
});