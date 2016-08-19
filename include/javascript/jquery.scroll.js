//这个插件支持上下滚动
//使用方法$(选择器).scrolling();即可
//精简了代码，响应速度明显加快
//DXD记于20091230


/*
<script type="text/javascript">
$(function(){
    $("div.kf").show().scrolling({"right":"0px","top":"143px"});
	$.ScrollImg();
});
</script>
*/


$(function(){
    $.fn.extend({
        scrolling:function(jsonCSS){
			var jq_this=$(this);
	        jq_this.css({"position":"absolute","z-index":"999","top":"0px"});
			if(jsonCSS) jq_this.css(jsonCSS);
		    var pos=jq_this.position();
			var top=pos.top;
		    
			setInterval(
			    function(){
		            var topDistance=document.documentElement.scrollTop+top+"px";
		            jq_this.animate(
			            {"top":topDistance}
			        );
				},
				500
			)
	    }
	    
    });
});