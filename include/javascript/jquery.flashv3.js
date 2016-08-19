$(function(){
    $.fn.extend({
        flashv3:function(img_paths,links,tags,img_width,img_height,tag_height,full_screen){
			var jq_this=$(this);
			var o_this=this;
			var jq_div,jq_p,jq_ul;
			var arr_img=img_paths.split("|");
			var arr_link=links.split("|");
			var arr_tag=tags.split("|");
			this.now_pos=0;
			this.full_screen=full_screen;
			this.h_time=0;
			this.start_x=0;
			this.start_time=0;
			
			this.Init=function(){
				jq_this.empty();
				jq_this.css({
				    "width":"100%",
					"height":(img_height.toString().indexOf("rem")==-1)?img_height+"px":img_height
				});
				
				if($.trim(img_paths)=="")
				{
					return false;
				}
				if(arr_img.length!=arr_link.length || arr_img.length!=arr_tag.length)
				{
					return false;
				}
				return true;
			};
			
			this.CreatStructure=function()
			{
				jq_this.append("<div></div><ul></ul>");
				jq_div=jq_this.children("div");
				jq_ul=jq_this.children("ul");
				
				jq_div.append("<p></p>");
				jq_p=jq_div.children("p");
				
				jq_div.css({
				    "width":"100%",
					"height":(img_height.toString().indexOf("rem")==-1)?img_height+"px":img_height,
					"overflow":"hidden",
					"position":"relative"
				});
				
				jq_p.css("position","relative");
				jq_ul.css({"lineHeight":tag_height+"px"});
				
				var i;
				for(i=0;i<arr_link.length;i++)
				{
					jq_ul.append("<li><a href='"+arr_link[i]+"' target='_blank'>"+arr_tag[i]+"</a></li>");
					var prev=i==0?"prev":"";
					if(o_this.full_screen==1)
					{
						jq_p.append("<p prev='"+prev+"' style='position:absolute;left:0px;top:0px;z-index:"+(arr_link.length-i)+"'><a href='"+arr_link[i]+"' target='_blank'></a></p>");
					}
					else
					{
						jq_p.append("<p prev='"+prev+"' style='position:absolute;left:0px;top:0px;z-index:"+(arr_link.length-i)+"'><a href='"+arr_link[i]+"' target='_blank'><img src='images/noimages/nocycle.jpg' width='"+img_width+"' height='"+img_height+"' border='0' /></a></p>");
					}
				}
				var arr_o_img=[];
				for(i=0;i<arr_img.length;i++)
				{
					arr_o_img.push(new Image());
					img=arr_o_img[i];
					img.idx=i;
					img.src=arr_img[i];
					
					if(img.complete)
					{
						jq_this.parent().css("background","url()");
						if(o_this.full_screen==1)
						{
							jq_p.children("p").eq(img.idx).css("backgroundImage","url("+img.src+")");
						}
						else jq_p.find("img").eq(img.idx).attr("src",img.src);
						continue;
					}
					
					img.onload=function(){
						jq_this.parent().css("background","url()");
						if(o_this.full_screen==1)
						{
							if(!$.browser.msie) jq_p.children("p").eq(this.idx).css("backgroundPosition","center");
							jq_p.children("p").eq(this.idx).css("backgroundRepeat","repeat");
							jq_p.children("p").eq(this.idx).css("backgroundImage","url("+this.src+")");
						}
						else jq_p.find("img").eq(this.idx).attr("src",this.src);
					}
				}
				
				jq_ul.find("a").eq(0).css({
					"background":"",
				    "backgroundColor":"#c80002",
					"color":"#fff"
				});
				jq_ul.css("zIndex",10);
			};
			
			this.SetInterval=function(){
			    o_this.h_time=setInterval(function(){
					var next_pos=o_this.now_pos>=arr_link.length-1?0:o_this.now_pos+1;
					o_this.Focus(next_pos);
				},5000);
			};
			
			this.SetEvent=function(){
				jq_ul.find("a").unbind();
				jq_ul.find("a").hover(
				    function(e){
					    var idx=jq_ul.find("a").index(this);
						o_this.Focus(idx);
					},
					function(e){}
				);
			};
			
			this.Focus=function(i,dir,mouse_offset,time_offset){
				jq_ul.find("a").css({
				    "backgroundColor":"#b5b5b5",
					"color":"#e3e3e3"
				});
				jq_ul.find("a").eq(i).css({
					"background":"",
				    "backgroundColor":"#c80002",
					"color":"#fff"
				});
				
				var jq_p_child=jq_p.children("p");
				if(i==jq_p_child.index(jq_p_child.filter("[@prev=prev]"))) return;
				
				var jq_p_prev=jq_p_child.filter("[@prev=prev]:first");
				if(jq_p_prev.size()<=0) jq_p_prev=jq_p_child.eq(0);
				var jq_now=jq_p_child.eq(i);
				
				if(!isNaN(mouse_offset) && parseInt(mouse_offset)<10)    //if mouseoffset is less than 10, go to the specified url
				{
					if(!isNaN(time_offset) && time_offset>150) window.location=jq_p_prev.find("a").attr("href");
					return;
				}
				//else execute animation affect
				
				jq_p_child.css("zIndex",0).attr("prev","");
				jq_now.attr("prev","prev");
				
				//animation for transition
				if(o_this.full_screen==1)
				{
					jq_p_prev.css("zIndex",1);
				    jq_now.css("zIndex",2);
					jq_now.hide();
					jq_now.fadeIn();
				}
				else
				{
					jq_p_prev.css("zIndex",2);
				    jq_now.css("zIndex",1);
					
					var anim;
					if(o_this.full_screen==2)
					{
						if(dir=="left") anim={left:"-="+img_width+"px"};
						else if(dir=="right") anim={left:"+="+img_width+"px"};
						else if(jq_p_child.index(jq_now)<jq_p_child.index(jq_p_prev)) anim={left:"-="+img_width+"px"};
						else anim={left:"+="+img_width+"px"};
					}
					else anim={top:"-="+img_height+"px"};
					
				    jq_p_prev.animate(anim,500,"linear",function(){
					    jq_p_prev.css({
						    zIndex:0,
							top:"0px",
							left:"0px"
					    });
					    jq_now.css("zIndex",2);
					});
				}
				
				o_this.now_pos=i;
			};
			
			this.TouchEvent=function(){
				jq_div.find("a").unbind();
				jq_div.find("a").click(function(e){
				    e.preventDefault();
				});//end click
				
				jq_div.get(0).addEventListener("touchstart",function(e){
					//e.preventDefault();
					clearInterval(o_this.h_time);
					o_this.h_time=0;
					
					o_this.start_x=e.touches[0].clientX;
					o_this.start_time=new Date();
				},false);//touch start
				
				jq_div.get(0).addEventListener("touchmove",function(e){},false);//touch move
				
				jq_div.get(0).addEventListener("touchend",function(e){
			        var now_x=e.changedTouches[0].clientX;
					if(o_this.start_x>now_x)
					{
						var prev_pos=o_this.now_pos<=0?arr_link.length-1:o_this.now_pos-1;
						o_this.Focus(prev_pos,"left",o_this.start_x-now_x,new Date().getTime()-o_this.start_time.getTime());
					}
					else
					{
						var next_pos=o_this.now_pos>=arr_link.length-1?0:o_this.now_pos+1;
					    o_this.Focus(next_pos,"right",now_x-o_this.start_x,new Date().getTime()-o_this.start_time.getTime());
					}
				    o_this.SetInterval();
				},false);
			};
			
			if(this.Init())
			{
				this.CreatStructure();
				this.SetInterval();
				this.SetEvent();
				if(IsMobile()) this.TouchEvent();
			}
	    }
    });
});