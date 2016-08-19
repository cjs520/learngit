(function($) {
    $.fn.printer = function() {
		var T=this;
	    var jq_node=$(this);
		var jq_iframe=null;
		var is_print=false;
		
		//function definition
		this.SetEvents=function(){
			jq_node.each(function(i){
			    $(this).bind("click",function(e){
					if(jQuery.browser.safari)    //if we meet chrome, open a new window for this hyperlink
					{
						window.open($(this).attr("url"));
						return;
					}
					
				    if(is_print)
				    {
					    alert("打印任务进行中，请稍等...");
					    return;
				    }
				
			        if(!confirm("确认打印？")) return;
				    is_print=true;
				    T.CreatePrintContainer($(this).attr("url"));
			    });
			});
		    
		}//end functin SetEvents
		
		this.CreatePrintContainer=function(url){
		    if(jq_iframe==null)
			{
				jq_iframe=$("<iframe id='printPage' name='printPage' style='position:absolute;top:0px; left:0px;width:0px; height:0px;border:0px;overfow:none; z-index:-1'></iframe>");
				$("body").append(jq_iframe);
				T.LoadPrintMsg();
				
			}
			jq_iframe.attr("src",url);
			
		}//end function CreatePrintContainer
		
		this.LoadPrintMsg=function(){
			if(jq_iframe==null) return;
			if(jq_iframe.size()<=0) return;
			
			jq_iframe.unbind("load");
			jq_iframe.bind("load",function(){
				T.Print();
			});
		}//end function LoadPriintMsg
		
		this.Print=function(){
			if(frames.length<=0) return;
			
			frames["printPage"].focus();
			frames["printPage"].print();
			
			is_print=false;
		}//end function Print
		
		//***************************************************//
		//function call
		this.SetEvents();
	};
	
})(jQuery);
