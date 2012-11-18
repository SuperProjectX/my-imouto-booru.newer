<?php
trait ActionView_Helper_Date
{
    // protected $_name = 'DateHelper';
    
    public function time_ago_in_words($from_time, $include_seconds = false)
    {
        return $this->distance_of_time_in_words($from_time, time(), $include_seconds);
    }
    
    public function distance_of_time_in_words($from_time, $to_time = 0, $include_seconds = false)
    {
        $from_time = strtotime($from_time);
        
        $to_time = strtotime(gmdate('Y-m-d H:i:s'));
        $distance_in_seconds = round($to_time - $from_time);
        
        if ($distance_in_seconds < 0) {
            $distance_in_seconds = round($from_time - $to_time);
        }
        
        $distance_in_minutes = ceil($distance_in_seconds/60);
        
        if ($distance_in_seconds < 30)
            return 'Less than a minute';
        elseif ($distance_in_seconds < 90)
            return '1 minute';
        elseif ($distance_in_seconds < 2670)
            return $distance_in_minutes . ' minutes';
        elseif ($distance_in_seconds < 5370)
            return 'about 1 hour';
        elseif ($distance_in_seconds < 86370)
            return 'about ' . ceil($distance_in_minutes/60) . ' hours';
        elseif ($distance_in_seconds < 151170)
            return '1 day';
        elseif ($distance_in_seconds < 2591970)
            return ceil(($distance_in_minutes/60)/24) . ' days';
        elseif ($distance_in_seconds < 5183970)
            return 'about 1 month';
        elseif ($distance_in_seconds < 31536059)
            return ceil((($distance_in_minutes/60)/24)/31) . ' months';
        elseif ($distance_in_seconds < 39312001)
            return 'about 1 year';
        elseif ($distance_in_seconds < 54864001)
            return 'over a year';
        elseif ($distance_in_seconds < 31536001)
            return 'almost 2 years';
        else
            return 'about ' . ceil($distance_in_minutes/60/24/365) . ' years';
    }
}