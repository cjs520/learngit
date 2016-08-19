document.write('<script type="text/javascript" src="http://api.map.baidu.com/api?v=1.5&ak=Ama5bgpPNWjqWRj8AG2IVzpM"></script>');
//为了hack以前的代码，51ditu里的结构
if(typeof(LTMaps)!="function") LTMaps=function(){}
if(typeof(LTPoint)!="function")
{
	LTPoint=function(lng,lat){
	    lng=parseFloat(lng);
	    lat=parseFloat(lat);
	    this.lng=lng;
	    this.lat=lat;
    }
}

//jquery plug-in start
$(function(){
    
	//user def class MVMMarker
    function MVMMarker(center,length,color,title) {
		this._center=center;
		this._length=length;
		this._color=color;
		this._title=title;
	}
	
	MVMMarker.prototype = new BMap.Overlay();
	
	MVMMarker.prototype.initialize = function(map) {
		this._map=map;
		var div=$('<div class="map_sj" rel="tag"><table border="0" cellspacing="0"><tr><td class="td1"></td><td class="td2">'+this._title+'</td><td class="td3"></td></tr></table><span class="td4"></span></div>').get(0);
		div.style.position="absolute";
		div.style.width=this._length+"px";
		div.style.height=this._length+"px";
		div.style.background=this._color;
		
		map.getPanes().markerPane.appendChild(div);
		this._div=div;
		
		return div;
    };
	
	MVMMarker.prototype.getMarkerDom=function(){
	    return this._div;
	}
	
    MVMMarker.prototype.draw = function() {
		var position=this._map.pointToOverlayPixel(this._center);
		this._div.style.left=position.x-this._length/2+"px";
		this._div.style.top=position.y-this._length/2+"px";
    };
	
	MVMMarker.prototype.hide = function() {
		if(this._div) this._div.style.display="none";
    };
	
	MVMMarker.prototype.show = function() {
		if(this._div) this._div.style.display="";
    };
	
	MVMMarker.prototype.toggle = function() {
		if(this._div)
		{
			if(this._div.style.display=="") this.hide();
			else this.show();
		}
    };
	
    $.fn.extend({
    	MapV2:function(mask){
			var top=this;
			var jq_top=$(this);
			
			this.json_area=[];
			this.h_map=null;
			this.arr_markers=[];
			this.h_info_win=null;
			this.jq_mask=mask;
			this.is_loading=false;
			this.fav_loading=false;
			
			this.Prepare=function(){
				if(jq_top.size()<=0)
				{
					alert("no map layout");
					return false;
				}
				
				return true;
			}//end Prepare
			
			this.DrawMap=function(){
				top.h_map=new BMap.Map(jq_top.get(0));
				top.h_map.centerAndZoom(new BMap.Point(108,38),5);
				top.h_map.addControl(new BMap.ScaleControl({type: BMAP_ANCHOR_TOP_LEFT}));
				top.h_map.addControl(new BMap.NavigationControl());
			}//end DrawMap
			
			this.DrawMarkers=function(){
				var marker;
				for(var key in top.arr_markers)
				{
					top.h_map.removeOverlay(top.arr_markers[key]);
				}
				top.arr_markers=[];
				
				for(var key in top.json_area)
				{
                    marker = new MVMMarker(new BMap.Point(top.NTU2Normal(top.json_area[key].lng),top.NTU2Normal(top.json_area[key].lat)), 0,"",top.json_area[key].shop_name);
					
					top.h_map.addOverlay(marker);
					top.arr_markers.push(marker);
				}
				
			}//end DrawMarkers
			
			this.BuildInfoWindow=function(html_content,marker){
			    if(!top.h_info_win)
				{
					var opts={
			            width:350,
					    height:150,
					    title:"",
					    enableMessage:false
				    };
				    top.h_info_win=new BMap.InfoWindow("",opts);
				}
				top.h_info_win.setContent(html_content);
				
				top.h_map.openInfoWindow(top.h_info_win, marker._center);
				setTimeout(function(){
				    top.SetCollectFavEvents();
				},500);
				
			}//end BuildInfoWindow
			
			this.SetEvents=function(){
			    for(var key in top.arr_markers)
				{
					var dom_div=top.arr_markers[key].getMarkerDom();
					$(dom_div).data("key",key);
					$(dom_div).hover(
						function(e){
							$(this).addClass("map_sj_hover");
						    $("div[@rel=tag]").css("zIndex",0);
							$(this).css("zIndex",1);
						},
						function(e){
							$(this).removeClass("map_sj_hover");
						}
					);//end hover
					
					$(dom_div).click(function(){
					    var k=$(this).data("key");
						var html="";
						html+="<div class='map_xx'><h3><a style='font-weight:bold;font-size:14px' href='"+top.json_area[k].url+"' target='_blank'>"+top.json_area[k].shop_name+"</a></h3>";
						html+=top.json_area[k].map_tip+"<br />";
						html+="电话：<strong>"+top.json_area[k].member_tel1+"</strong> <br />手机：<strong>"+top.json_area[k].member_tel2+"</strong><br />";
						html+="<a class='blue' href='"+top.json_area[k].url+"' target='_new'>[详情]</a> <a class='blue' rel='fav' uid='"+top.json_area[k].m_uid+"'>[收藏]</a>";
						html+="<a class='logo' href='"+top.json_area[k].url+"' target='_blank'><img src='"+top.json_area[k].up_logo+"' /></a>";
						html+="</div>";
						top.BuildInfoWindow(html,top.arr_markers[k]);
					});//end click
				}
				
			}//end SetEvents
			
			this.RefreshMapData=function(keywords,sup_cat){
				if(top.is_loading) return;
				top.jq_mask.show();
				var post_data="keywords="+keywords+"&sup_cat="+sup_cat;
				
				top.is_loading=true;
				$.ajax({
				    url:"map.php?action=get_shop_info",
					type:"POST",
					dataType:"json",
					data:post_data,
					cache:true,
					success:function(json){
					    top.json_area=json;
						$("span[@rel=search_num]").html(top.json_area.length==0?"0":top.json_area.length);
						
						//take search result down in the list
						$("#con_one_1").children("ul").remove();
						for(var key in top.json_area)
						{
							var html="";
							html+="<ul>";
                            html+='<li><a href="'+top.json_area[key]["url"]+'" class="fd" target="_new">'+top.json_area[key]["shop_name"]+'</a> <a href="'+top.json_area[key]["url"]+'" class="blue" target="_new">[详情]</a> <a href="#" rel="fav" uid="'+top.json_area[key]["m_uid"]+'" class="blue">[收藏]</a></li>';
                            html+='<li>电话：'+top.json_area[key]["member_tel2"]+'</li>';
                            html+='<li>地址：'+top.json_area[key]["province"]+' '+top.json_area[key]["city"]+' '+top.json_area[key]["county"]+' '+top.json_area[key]["shop_address"]+'</li>';
                            html+="</ul>";
							$("#con_one_1").append(html);
							
						}
						top.DrawMarkers();
						top.SetEvents();
						top.SetCollectFavEvents();
					},
					complete:function(){
					    top.jq_mask.hide();
						top.is_loading=false;
					}
			    });
			}//end function RefreshMapData
			
			this.LoadFavorite=function(){
				if(top.fav_loading) return;
				
				top.fav_loading=true;
			    $.ajax({
				    url:"map.php?action=load_fav",
					type:"GET",
					dataType:"json",
					cache:false,
					success:function(json){
						var html="";
					    if(json["str_err"])
						{
							html+='<h5>您还未登录</h5><p class="gray">您可以：<br />&nbsp; 登录后再查看收藏商铺</p>';
							$("#con_one_2").html(html);
							return;
						}
						
						$("#con_one_2").html("");
						
						if(json["shop"].length<=0)
						{
							html+='<h5>您没有收藏任何商家</h5><p class="gray">您可以：<br />&nbsp; 1.点击地图中的商家<br />&nbsp; 2.点击"收藏"<br />&nbsp; 3.收藏成功<br />注意：您最多收藏10个商家</p>';
						}
						else
						{
							html+='<h5 class=fn>以下是您收藏过的商家：<a class="blue" rel="clear_fav">[清空收藏]</a></h5>';
							html+='<ul>';
							for(var key in json["shop"])
							{
								html+='<li>';
								html+='<a class=gray href="'+json["shop"][key]["url"]+'" target="_new">'+json["shop"][key]["shop_name"]+'</a>';
								html+='<a class=del href="#" rel="del_fav" uid="'+json["shop"][key]["m_uid"]+'"></a>';
								html+='</li>';
							}
							html+='</ul>';
						}
						
						$("#con_one_2").html(html);
						top.SetFavEvents();
					},
					complete:function(){
					    top.fav_loading=false;
					}
				});
			}//end function LoadFavorite
			
			this.SetFavEvents=function(){
			    $("a[@rel=clear_fav]").click(function(e){
				    e.preventDefault();
					if(top.fav_loading)
					{
						alert("收藏任务执行中，请稍候");
						return false;
					}
					if(!confirm("确定清空你的所有收藏？")) return false;
					
					top.fav_loading=true;
					$.get(
					    "map.php?action=clear_fav&rnd="+Math.random(),
						function(msg){
						    top.fav_loading=false;
							top.LoadFavorite();
						}
					);//end get
				});//end click
				
				$("a[@rel=del_fav]").click(function(e){
				    e.preventDefault();
					if(top.fav_loading)
					{
						alert("收藏任务执行中，请稍候");
						return false;
					}
					if(!confirm("确定删除您所选的收藏？")) return false;
					
					top.fav_loading=true;
					var uid=$(this).attr("uid");
					$.get(
					    "map.php?action=del_fav&sid="+uid+"&rnd="+Math.random(),
						function(msg){
						    top.fav_loading=false;
							top.LoadFavorite();
						}
					);//end get
					
				});//end click
				
			}//end function SetFavEvents
			
			this.SetCollectFavEvents=function(){
			    $("a[@rel=fav]").unbind("click");
				$("a[@rel=fav]").click(function(e){
				    e.preventDefault();
					if(top.fav_loading)
					{
						alert("收藏任务执行中，请稍候");
						return false;
					}
					if(!confirm("确定收藏这间商铺？")) return false;
					
					top.fav_loading=true;
					var uid=$(this).attr("uid");
					$.ajax({
					    url:"map.php?action=fav_shop&sid="+uid,
						type:"GET",
						cache:false,
						success:function(msg){
						    alert(msg);
							if(msg.indexOf("OK")!=-1) top.LoadFavorite();
						},
						complete:function(){
						    top.fav_loading=false;
						}
					});//end ajax
				});//end click
			}//end function SetCollectFavEvents
			
			this.NTU2Normal=function(num){
			    return num/100000;
			}//end NTU2Normal
			
			this.Normal2NTU=function(num){
			    return num*100000;
			}//end Normal2NTU
			
			if(!this.Prepare()) return false;
			this.DrawMap();
			
			return this;
		}
	});
});