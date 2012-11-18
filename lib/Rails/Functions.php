<?php
function vd()
{
    $vars = func_get_args();
    call_user_func_array('var_dump', $vars);
}

function vde() 
{
    $vars = func_get_args();
    call_user_func_array('var_dump', $vars);
    exit;
}

# Just a quick way to return gmdate().
function gmd($format = null, $timestamp = null)
{
    !$format && $format = 'Y-m-d H:i:s';
    if ($timestamp)
        return gmdate($format, $timestamp);
    else
        return gmdate($format);
}

function gmd_math($str, $format = null)
{
    return gmd($format, strtotime($str));
}

function is_indexed(array $array)
{
    $i = 0;
    foreach(array_keys($array) as $k) {
        if($k !== $i)
            return false;
        $i++;
    }
    return true;
}

function array_flat($arr)
{
    $flat = array();
    foreach ($arr as $v) {
        if (is_array($v))
            $flat = array_merge($flat, array_flat($v));
        else
            $flat[] = $v;
    }
    return $flat;
}