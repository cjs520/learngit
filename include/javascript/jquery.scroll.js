//������֧�����¹���
//ʹ�÷���$(ѡ����).scrolling();����
//�����˴��룬��Ӧ�ٶ����Լӿ�
//DXD����20091230


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