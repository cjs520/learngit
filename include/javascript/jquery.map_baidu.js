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


$(function(){
    $.fn.extend({
    	Map:function(init_area,zoom,map_title,map_tip,is_rect_zoom,is_show){
			var top=this;
			var jq_top=$(this);
			
			this.id="";
			this.h_map=null;
			this.h_marker=null;
			this.h_infownd=null;
			this.map_tip=map_tip;
			this.map_title=map_title;
			this.lng=0;
			this.lat=0;
			this.marker_icon=new BMap.Icon("http://api.map.baidu.com/images/marker_red_sprite.png",new BMap.Size(23,25),{offset:new BMap.Size(10,25),imageOffset:new BMap.Size(0,0)});
			this.marked=!(typeof(init_area)=="string");
			
			this.CreateMap=function(){
				
			    if(!jq_top.attr("id"))
				{
					jq_top.attr("id","my_map");
					top.id="my_map";
				}
				else top.id=jq_top.attr("id");
				
				if(typeof(BMap)!="object")
				{
					alert("baidu fail loaded");
					return false;
				}
				
				if(typeof(init_area)=="string")
				{
					top.AreaShowMap(init_area);
				}
				else
				{
					top.lng=parseFloat(init_area.lng);
					top.lat=parseFloat(init_area.lat);
					if(isNaN(top.lng)) top.lng=0;
					if(isNaN(top.lat)) top.lat=0;
					top.ShowMap();
				}
			}//end CreateMap
			
			this.AreaShowMap=function(area_name){
				if(!area_name) area_name="";
				
				var geoc=new BMap.Geocoder();
				geoc.getPoint(area_name,function(rtl){
				    top.lng=top.Normal2NTU(rtl.lng);
				    top.lat=top.Normal2NTU(rtl.lat);
					top.ShowMap();
				},"");
			}//end AreaShowMap
			
			this.ShowMap=function(){
				if(jq_top.size()<=0) return false;
				
				if(!top.h_map)
				{
					top.h_map=new BMap.Map(jq_top.get(0));
					top.h_map.centerAndZoom(new BMap.Point(top.NTU2Normal(top.lng),top.NTU2Normal(top.lat)),15);
					top.h_map.addControl(new BMap.ScaleControl({type: BMAP_ANCHOR_TOP_LEFT}));
					top.h_map.addControl(new BMap.NavigationControl());
					top.h_map.addEventListener("click",function(e){
					    top.lat=top.Normal2NTU(e.point.lat);
						top.lng=top.Normal2NTU(e.point.lng);
						top.SetMarker();
					});
				    
					if(top.marked) top.SetMarker();
				}
			    else
				{
					top.h_map.panTo(new BMap.Point(top.NTU2Normal(top.lng),top.NTU2Normal(top.lat)));
				}
			}//end ShowMap
			
			this.SetMarker=function(){
			    if(!top.h_map) return false;
				if(!top.h_marker)
				{
					top.h_marker=new BMap.Marker(new BMap.Point(top.NTU2Normal(top.lng),top.NTU2Normal(top.lat)),top.marker_icon);
					top.h_map.addOverlay(top.h_marker);
				}
				else
				{
					top.h_marker.setPosition(new BMap.Point(top.NTU2Normal(top.lng),top.NTU2Normal(top.lat)));
				}
				
				$("input[@name=lat]").val(top.lat);
				$("input[@name=lng]").val(top.lng);
				top.SetInfoWnd();
			}//end SetMarker
			
			this.SetInfoWnd=function(){
				if(!top.h_map)
				{
				    alert("ERROR：还没有创建地图");
					return;
				}
				if(!top.h_marker)
				{
				    alert("ERROR:还没有创建标记");
					return;
				}
				var opts={
				    width:250,
					height:100,
					title:top.map_title,
					enableMessage:false
				};
				
			    if(!top.h_infownd) top.h_infownd=new BMap.InfoWindow(top.map_tip,opts);
				else
				{
					top.h_infownd.setTitle(top.map_title);
					top.h_infownd.setContent(top.map_tip);
				}
				
				top.h_map.openInfoWindow(top.h_infownd,top.h_marker.getPosition());
			}//end SetInfoWnd
			
			this.NTU2Normal=function(num){
			    return num/100000;
			}//end NTU2Normal
			
			this.Normal2NTU=function(num){
			    return num*100000;
			}//end Normal2NTU
			
			
			this.CreateMap();
			$("input[@rel=view]").click(function(){
			    var map_title=$("input[@name=map_title]").val();
				var map_tip=$("input[@name=map_tip]").val();
				top.map_title=map_title;
				top.map_tip=map_tip;
				top.SetInfoWnd();
			});//end click
			$("select#province,select#city,select#county,select#county").change(function(){
			    var add_val=$(this).val();
				if(add_val.length<=0) return false;
				top.AreaShowMap(add_val);
			});//end change
		}
	});
});