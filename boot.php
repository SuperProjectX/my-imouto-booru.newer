<?php
define('RAILS_ROOT', dirname(__FILE__));
require RAILS_ROOT.'/lib/Rails/Rails.php';
Rails::boot();

function st($end = false) {
  static $starttime;
  
  $mtime = microtime(); 
  $mtime = explode(" ",$mtime); 
  $mtime = $mtime[1] + $mtime[0]; 
  
  if (!$end) {
    $starttime = $mtime;
  } else {
    $endtime = $mtime; 
    $totaltime = ($endtime - $starttime); 
    echo $totaltime;
  }
}
function mu() {
  echo 'Memory usage: '.number_to_human_size(memory_get_usage());
}
function number_to_human_size($bytes){ 
	$size = $bytes / 1024; 
	if($size < 1024){ 
		$size = number_format($size, 1); 
		$size .= ' KB'; 
	} else { 
		if($size / 1024 < 1024){ 
				$size = number_format($size / 1024, 1); 
				$size .= ' MB'; 
		} else if ($size / 1024 / 1024 < 1024) { 
				$size = number_format($size / 1024 / 1024, 1); 
				$size .= ' GB'; 
		}  
	} 
	return $size; 
}

st();