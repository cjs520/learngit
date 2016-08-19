//这个JQ扩展函数用来控制投票的拖动
//投票用<a>标签,用group=""来分组，相同的为一组，例<a group="group1"></a>,而且同一组的数量不能超过五个
//标签a里还有一个info属性，用来存储提示信息
//在同一组下必须还有一个hidden来提收投票值
//同一组的提示标签span可有可无，如果有，则会显示相应的提示信息
//鼠标移到a上的css类hover_class和鼠标移出的css类static_class，必须自己在外部实现
//HTML示例
//<input type="hidden" name="comment_star" group="comment_star" value="5" />
//<a href="#" class="ding" group="comment_star" info="$lang[rate_product1]"></a>
//<a href="#" class="ding" group="comment_star" info="$lang[rate_product2]"></a>
//<a href="#" class="ding" group="comment_star" info="$lang[rate_product3]"></a>
//<a href="#" class="ding" group="comment_star" info="$lang[rate_product4]"></a>
//<a href="#" class="ding" group="comment_star" info="$lang[rate_product5]"></a> 
//<span group="comment_star">$lang[rate_product5]</span>
//调用
//$("a[@group=comment_star]").vote("hover","ding");

$.fn.extend({
    vote:function(hover_class,static_class){
		var err_info=new Array("","标签数量错误","标签出错","分组出错","隐藏标签出错");
		var max_vote=5;
		var jq_objs=$(this);
		var group_name=jq_objs.eq(0).attr("group");
		var jq_hidden=$("input[@type=hidden][@group="+group_name+"]");
		var jq_span=$("span[@group="+group_name+"]");
		
		if(jq_objs.size()<=0) return false;
		
		this.Init=function(){
		    var flag=0;
			if(jq_objs.size()<=0 || jq_objs.size()>max_vote) flag=1;
			jq_objs.each(function(i){
			    if(this.tagName.toLowerCase()!="a")
				{
					flag=2;
					return false;
				}
				if(i>0 && jq_objs.eq(i-1).attr("group")!=$(this).attr("group"))
				{
					flag=3;
					return false;
				}
			});
			if(jq_hidden.size()<=0 || jq_hidden.size()>1) flag=4;
			if(flag==0)
			{
				var ori_value=jq_objs.filter("."+static_class).size();
				if(ori_value<=0) ori_value=1;
				if(ori_value>max_vote) ori_value=max_vote;
				jq_hidden.val(ori_value);
			}
			return flag;
		}//end Init
		
		this.DoVote=function(){
		    jq_objs.hover(
			    function(e){
					if($("body").data(group_name+"_enable"))
					{
				        jq_objs.filter("."+static_class).removeClass(static_class).addClass(hover_class);
					    var target_index=jq_objs.index(this);
					    jq_objs.filter(":gt("+target_index+")").removeClass(hover_class);
					    jq_objs.filter(":lt("+(target_index+1)+")").addClass(hover_class);
					    if(jq_span.size()>=1) jq_span.html($(this).attr("info"));
					}
				},
				function(e){
				    jq_objs.filter("."+hover_class).removeClass(hover_class).addClass(static_class);
				}
			);
			jq_objs.parent().hover(
			    function(e){
				    $("body").data(group_name+"_enable",true);
				},
				function(e){
				    if($("body").data(group_name+"_enable"))
					{
						var ori_value=jq_hidden.val();
						jq_objs.filter(":gt("+(ori_value-1)+")").removeClass(static_class);
					    jq_objs.filter(":lt("+ori_value+")").addClass(static_class);
					    if(jq_span.size()>=1) jq_span.html(jq_objs.eq(ori_value-1).attr("info"));
					}
				}
			);
			jq_objs.click(function(e){
			    e.preventDefault();
				if($("body").data(group_name+"_enable"))
				{
				    jq_objs.filter("."+hover_class).removeClass(hover_class).addClass(static_class);
				    jq_hidden.val(jq_objs.index(this)+1);
				}
				$("body").data(group_name+"_enable",false);
			});
		}//end DoVote
		
		//函数调用
		var rtn_code=this.Init();
		if(rtn_code!=0)
		{
			alert(err_info[rtn_code]);
			return false;
		}
		this.DoVote();
	}
});





