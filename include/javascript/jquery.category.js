//本插件用于分层显示商品分类
//例：<span id="id"></span>
//调用$("#id").GoodsCategory(3);
//要配合ajax.php?action=get_goods_category和ajax.php?action=get_sub_category

$(function(){
    $.fn.extend({
	    GoodsCategory:function(cat_id,callback){
			var jq_top=$(this).eq(0);
			var top=this;
			this.cat_id=parseInt(cat_id);
			this.cb=callback;
			if(isNaN(this.cat_id) || this.cat_id<=0) this.cat_id=0;
			
			this.Init=function(){
				if(jq_top.size()<=0) return false;
				$.get(
					"ajax.php?action=get_goods_category&cat_id="+top.cat_id+"&rnd="+Math.random(),
					function(sel_cat){
						jq_top.html(sel_cat);
						top.SetEvent();
					}
				);//end get
				return true;
			}//end Init
			
			this.SetEvent=function(){
			    var jq_select=jq_top.find("select");
				
				jq_select.unbind();
				jq_select.bind("change",function(e){
				    $(this).nextAll().remove();
					var cur_id=$(this).val();
					if(cur_id=="0") return false;
					
					$.get(
					    "ajax.php?action=get_sub_category&cat_id="+cur_id+"&rnd="+Math.random(),
						function(sel_cat){
							top.CallCB(cur_id);
							
						    if($.trim(sel_cat)=="") return false;
							jq_top.append(sel_cat);
							top.SetEvent();
						}
					);//end get
				});//end bind
			}//end SetEvent
			
			this.CallCB=function(cat_id){
				if((typeof top.cb)!="function") return;
				top.cb(cat_id)
			}//end CallCB
			
			var init_rtl=this.Init();
			if(!init_rtl)
			{
				alert("error");
				return false;
			}
		}//end GoodsCategory
	});  
});
