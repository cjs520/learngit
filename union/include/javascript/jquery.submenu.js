//该插件要求HTＭＬ格式
//主菜单基本格式：<dl><dt uid="">一级菜单</dt><dd uid="">二级菜单数据</dd><dd>二级菜单数据</dd></dl>
//一二级菜单数据用uid关联
//主菜单中的二级菜单（dd）只供JS读取，全设置为display:none
//从菜单（二级菜单）基本格式<div class="sub_category"><ul><li>二级菜单</li><li>二级菜单</li></ul></div>

$(function(){
    $.fn.extend({
	    DXDSubMenu:function(){
		    var dxd_jq_main=$(this);
			var dxd_jq_sub=$(".sub_category");
			
			//检查函数
			this.dxd_fun_check=function(){
				if(dxd_jq_main.children("dt").size()<=0) return false;
				if(dxd_jq_sub.size()<=0) return false;
				return true;
			}
			//初始化函数
			this.dxd_fun_init=function(){
				if(dxd_jq_sub.children("div").size()<=0) dxd_jq_sub.append("<div></div>");
				dxd_jq_sub.children("div").empty();
				dxd_jq_sub.css({"position":"absolute","zIndex":"999"}).hide();
				dxd_jq_main.children("dd").hide();
			}
			//设置菜单功能
			this.dxd_fun_menu=function(){
			    dxd_jq_main.hover(
				    function(e){},
					function(e){
					    dxd_jq_sub.hide();
					}
				);    //外框鼠标离去
				dxd_jq_main.children("dt").hover(
				    function(e){
						dxd_jq_sub.hide();
					    var dxd_uid=$(this).attr("uid");
						if(!dxd_uid) return;
						dxd_jq_sub.children("div").empty();
						$(this).siblings("dd[@uid="+dxd_uid+"]").each(function(i){
						    dxd_jq_sub.children("div").append($(this).html());
						});
												
						dxd_jq_main.children("dt").attr("hover","");
						$(this).attr("hover","hover")
						
						$(this).addClass("self");
						var dxd_a_pos=$(this).position();
						var dxd_ul_pos=dxd_jq_main.position();
						dxd_jq_sub.css({"top":dxd_a_pos.top+"px","left":dxd_ul_pos.left+dxd_jq_main.width()+"px"});
						if(dxd_jq_sub.children("div").children().size()>0) dxd_jq_sub.show();
					},
					function(e){
						$(this).removeClass("self");
					}
				);    //一级菜单项鼠标覆盖
				dxd_jq_sub.hover(
				    function(e){
						$("div[@rel=category]").show();
						dxd_jq_main.children("dt[@hover=hover]").addClass("self");
					    $(this).show();
					},
					function(e){
						$("div[@rel=category]").hide();
						dxd_jq_main.children("dt").removeClass("self");
					    dxd_jq_sub.hide();
					}
				);    //二级菜单鼠标移入/移出
			}
			
			if(!this.dxd_fun_check()) return;    //检查过不了
			this.dxd_fun_init();
			this.dxd_fun_menu();
		}
	});  
});