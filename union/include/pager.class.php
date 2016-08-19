<?php
class Pager
{
    private  $_total;                          //记录总数
    private  $pagesize;                       //每一页显示的记录数
    private  $pages;                         //总页数
    private  $_cur_page;                    //当前页码
    private  $offset;                      //记录偏移量
    private  $pernum = 10;                //页码偏移量，这里可随意更改

    public function __construct($total,$pagesize,$_cur_page)
    {
        $this->_total=$total;
        $this->pagesize=$pagesize;
        $this->_offset();
        $this->_pager();
        $this->cur_page($_cur_page);
    }

    private function _pager()//计算总页数
    {
        return $this->pages = ceil($this->_total/$this->pagesize);
    }
    
    private function cur_page($_cur_page) //设置页数
    {
        if (isset($_cur_page)&&$_cur_page!=0)
        {
            $this->_cur_page=intval($_cur_page);
        }
        else
        {
            $this->_cur_page=1; //设置为第一页
        }
        return  $this->_cur_page;
    }

    //数据库记录偏移量
    public function _offset()
    {
        return $this->offset=$this->pagesize*($this->_cur_page-1);
    }
    //html连接的标签
    public function link($url,$exc='')
    {
        global $lang;
        $text = "$lang[pag_total]<span>$this->pages</span>$lang[pags] | $lang[pag_loction]<span>$this->_cur_page</span>$lang[pags]";
        if ($this->_cur_page == 1 && $this->pages>1)
        {
            //第一页
            $text.= " | $lang[pag_home] | $lang[pag_return] | <a href=".$url.($this->_cur_page+1).$exc.">$lang[pag_next]</a> | <a href=".$url.$this->pages.$exc.">$lang[pag_end]</a>";
        }
        elseif($this->_cur_page == $this->pages && $this->pages>1)
        {
            //最后一页
            $text.= " | <a href=".$url.'1'.$exc.">$lang[pag_home]</a> | <a href=".$url.($this->_cur_page-1).$exc.">$lang[pag_return]</a> | $lang[pag_next] | $lang[pag_end]";
        }
        elseif ($this->_cur_page > 1 && $this->_cur_page <= $this->pages)
        {
            //中间
            $text.= " | <a href=".$url.'1'.$exc.">$lang[pag_home]</a> | <a href=".$url.($this->_cur_page-1).$exc.">$lang[pag_return]</a> | <a href=".$url.($this->_cur_page+1).$exc.">$lang[pag_next]</a> | <a href=".$url.$this->pages.$exc.">$lang[pag_end]</a>";
        }
        $text.=" | $lang[pag_go]<input class=\"inp\" name=\"page_name\" id=\"page_name\" value=\"$GLOBALS[page]\" onkeydown=\"if(event.keyCode==13) {window.location='".$url."'+this.value+'$exc'; return false;}\" />$lang[pags] <input type=\"button\" onclick=\"window.location='".$url."'+$('#page_name').val()+'$exc';return false;\" class=\"page_go\" />";
        return $text;
    }
}