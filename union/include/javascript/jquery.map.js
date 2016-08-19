//为了hack以前的代码，51ditu里的结构
LTMaps=function(){}
LTPoint=function(lng,lat){
	lng=parseFloat(lng);
	lat=parseFloat(lat);
	this.lng=lng;
	this.lat=lat;
}


$(function(){
    $.fn.extend({
    	Map:function(init_area,zoom){
			var top=this;
			var jq_top=$(this);
			
			this.id="";
			this.lng=0;
			this.lat=0;
			this.marked=!(typeof(init_area)=="string");
			
			this.CreateMap=function(){
			    if(!jq_top.attr("id"))
				{
					jq_top.attr("id","my_map");
					top.id="my_map";
				}
				else top.id=jq_top.attr("id");
				
				
				if(typeof(init_area)=="string") top.AreaShowMap(init_area);
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
				var geoc=new google.maps.Geocoder();
				geoc.geocode(
				    {address:area_name},
					function(rtl,status){
					    if(status == google.maps.GeocoderStatus.OK)
						{
							top.lng=top.Normal2NTU(rtl[0].geometry.location.lng());
				            top.lat=top.Normal2NTU(rtl[0].geometry.location.lat());
						}
						top.ShowMap();
					}
				);
			}//end AreaShowMap
			
			this.ShowMap=function(){
				if(jq_top.size()<=0) return false;
				var lat=top.NTU2Normal(top.lat);
				var lng=top.NTU2Normal(top.lng);
				var url="http://api.map.baidu.com/staticimage?center="+lng+","+lat+"&width=230&height=230&zoom=15";
				var w=jq_top.find("div[@rel=map_cnt]").size()>0?jq_top.find("div[@rel=map_cnt]").get(0).style.width:0;
				var h=jq_top.find("div[@rel=map_cnt]").size()>0?jq_top.find("div[@rel=map_cnt]").get(0).style.height:0;
				w=w.replace("px","");
				h=h.replace("px","");
				
				url+="&markers=非常商城|"+lng+","+lat+"&markerStyles=l,M,0x4d98dd";
				jq_top.find("div[@rel=map_cnt]").append("<img src='"+url+"' />");
			}//end ShowMap
			
			this.NTU2Normal=function(num){
			    return num/100000;
			}//end NTU2Normal
			
			this.Normal2NTU=function(num){
			    return num*100000;
			}//end Normal2NTU
			
			
			this.CreateMap();
		}
	});
});