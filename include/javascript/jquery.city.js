$(function(){
    $.extend({
    	init_area:function(arr_sel_id,init_data,callback){
			var top=this;
			var address_union="address_union"+Math.random();
			this.test=function(){
		    	for(var i in arr_sel_id)
				{
			    	if($("select#"+arr_sel_id[i]).size()<=0) return false;
					$("#"+arr_sel_id[i]).attr("rel",address_union);
				}
				return true;
			};
			this.init=function(){
		    	$("select[@rel="+address_union+"]").css("width","80px").empty();
		    	$("select[@rel="+address_union+"]").append("<option value=''>请选择</option>");
				//初始化第一个:省
				$.ajax({
				    type:"GET",
					cache:true,
					dataType:"json",
					url:"city_data_response.php?action=province",
					success:function(json){
						        if(!json) return false;
								var selected="";
						        for(var i in json)
								{
									if(init_data && init_data.length>=1) selected=json[i]==init_data[0]?"selected":"";
				                    $("#"+arr_sel_id[0]).append('<option value="'+json[i]+'" '+selected+'>'+json[i]+'</option>'); 
								}
							},
					error:function(o,e,er){alert(er);}
				});
				//初始化第二个：市
				if(init_data && init_data.length>=1 && init_data[0]!="")
				{
					$.ajax({
				    	type:"GET",
						cache:true,
						dataType:"json",
						url:"city_data_response.php?action=city&province="+encodeURI(init_data[0]),
						success:function(json){
						        	if(!json) return false;
									var selected="";
						        	for(var i in json)
									{
				                    	if(init_data && init_data.length>=2) selected=json[i]==init_data[1]?"selected":"";
										$("#"+arr_sel_id[1]).append('<option value="'+json[i]+'" '+selected+'>'+json[i]+'</option>'); 
									}
								},
						error:function(o,e,er){alert(er);}
					});
				}
				//初始化第三个：县
				if(init_data && init_data.length>=2 && init_data[0]!="" && init_data[1]!="")
				{
					$.ajax({
				    	type:"GET",
						cache:true,
						dataType:"json",
						url:"city_data_response.php?action=county&province="+encodeURI(init_data[0])+"&city="+encodeURI(init_data[1]),
						success:function(json){
						        	if(!json)  return false;
									var selected="";
									if(json.length<=0)
									{
										$("#"+arr_sel_id[2]).hide();
										return false;
									}
									$("#"+arr_sel_id[2]).show();
						        	for(var i in json)
									{
										if(init_data && init_data.length>=3) selected=json[i]==init_data[2]?"selected":"";
				                    	$("#"+arr_sel_id[2]).append('<option value="'+json[i]+'" '+selected+'>'+json[i]+'</option>'); 
									}
								},
						error:function(o,e,er){alert(er);}
					});
				}
			}
			
			this.set_event=function(){    //更改后，这个函数暂不支持无限级
		    	$("select[@rel="+address_union+"]").bind("change",function(e){
					var index=$("select[@rel="+address_union+"]").index(this);
					var is_get_data=false;
					var action="";
					var data="",province="",city="";
					switch(index)
					{
					    case 0:
						    is_get_data=true;
							action="city";
							province=$(this).val();
							action="city";
						    break;
						case 1:
						    is_get_data=true;
							action="county";
							province=$("select[@rel="+address_union+"]:eq(0)").val();
							city=$(this).val();
							action="county";
						    break;
						default:
						    break;
					}
					$("select[@rel="+address_union+"]:gt("+index+")").empty().append("<option value=''>请选择</option>");
					if(typeof callback=="function")
					{
						callback($("select[@rel="+address_union+"]").eq(0).val(),$("select[@rel="+address_union+"]").eq(1).val(),$("select[@rel="+address_union+"]").eq(2).val());
					}
					
					if($(this).val()=="") return true;
					if(is_get_data)
					{
						$.ajax({
				    		type:"GET",
							cache:false,
							dataType:"json",
							url:"city_data_response.php?action="+action+"&province="+encodeURI(province)+"&city="+encodeURI(city),
							success:function(json){
						        if(!json)
								{
								    alert("没有地区数据");
									return false;
								}
								if(json.length<=0)
								{
									$("#"+arr_sel_id[index+1]).hide();
									return false;
								}
								$("#"+arr_sel_id[index+1]).show();
						        for(var i in json)
				                    $("#"+arr_sel_id[index+1]).append('<option value="'+json[i]+'">'+json[i]+'</option>'); 
							},
							error:function(o,e,er){alert(er);}
						});
					}
				});
				return true;
			}
			
			if(!this.test())
			{
				alert("ID不匹配");
				return false;
			}
			this.init();
			if(!this.set_event())
			{
				alert("事件设置发生错误");
				return false;
			}
			
		}
	});
});