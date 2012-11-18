<?php
trait ActionView_Helper_WillPaginate
{
    // protected $_name = 'WillPaginate_Helper';
    
    private $_url;
    
    private $_page;
    
    private $_pages;
    
    public function will_paginate(ActiveRecord_Collection $coll)
    {
        if ($coll->pages() <= 1)
            return;
        $pages = $coll->pages();
        $page = $coll->page();
        $url = Rails::application()->dispatcher()->request()->path();
        $get_params = $this->params()->get();
        
        if (isset($get_params['page']))
            unset($get_params['page']);
        
        $gap = '<span class="gap">...</span>';
        
        $get_params = http_build_query($get_params);
        $get_params && $get_params .= '&';
        $url .= '?'.$get_params.'page=';
        
        $this->_page  = $page;
        $this->_pages = $pages;
        $this->_url   = $url;
        
        echo '<div class="pagination">';
        
        if ($page - 1 > 0) {
            $rel = ($page - 1) == 1 ? ' start' : '';
            echo '<a rel="prev'.$rel.'" href="' . $url . ($page - 1) . '" class="previous_page">&#8592; Previous</a> ';
        } else
            echo '<span class="previous_page disabled">&#8592; Previous</span> ';

        if ($page == 1)
            echo $this->_create_num_current(1);
        else
            echo $this->_create_num_link(1);

        if ($pages < 10){
            for ($i = 2; $i <= $pages; $i++){
                if($i == $page)
                    echo $this->_create_num_current($i);
                else {
                    echo $this->_create_num_link($i);
                }
            }
        } elseif ($page > ($pages - 4)) {
            echo $gap;
            for($i = ($pages - 4); $i < ($pages); $i++) {
                if($i == $page)
                    echo $this->_create_num_current($i);
                else {
                    echo $this->_create_num_link($i);
                }
            }
        } elseif ($page > 4) {
            echo $gap;
            for ($i = ($page - 1); $i <= ($page + 2); $i++) {
                if($i == $page+1)
                    echo $this->_create_num_current($i);
                else {
                    echo $this->_create_num_link($i);
                }
            }
            echo $gap;
        } else {
            if ($page >= 3){
                for ($i = 2; $i <= $page+2; $i++) {
                    if ($i == $page)
                        echo $this->_create_num_current($i);
                    else {
                        echo $this->_create_num_link($i);
                    }
                }
            } else {
                for ($i = 2; $i <= 5; $i++) {
                    if ($i == $page)
                        echo $this->_create_num_current($i);
                    else {
                        echo $this->_create_num_link($i);
                    }
                }
            }
            echo $gap;
        }

        if ($pages >= 10) {
            if ($pages == $page)
                echo $this->_create_num_current($i);
            else
                echo '<a href="' . $url . $pages . '">' . $pages . '</a> ';
        }

        if ($pages != $page) {
            echo '<a rel="next" href="' . $url . ($page + 1) . '" class="next_page">Next &#8594;</a> ';
        } else
            echo ' <span class="next_page disabled">Next &#8594;</span>';
        
        echo '</div>';
    }
    
    private function _create_num_current($page)
    {
        return '<span class="current">' . $page . '</span> ';
    }
    
    private function _create_num_link($to_page)
    {
        $rel = array();
        if ($to_page == $this->_page + 1)
            $rel[] = 'next';
        elseif ($to_page == $this->_page - 1)
            $rel[] = 'prev';
        if ($to_page == 1)
            $rel[] = 'start';
        $rel = $rel ? ' rel="'.implode(' ', $rel).'"' : '';
        return '<a'.$rel.' href="'    . $this->_url . $to_page . '">' . $to_page . '</a> ';
    }
}