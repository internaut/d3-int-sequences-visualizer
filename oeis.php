<?php
define('OEIS_BASE_URL', 'https://oeis.org/');
define('THIS_BASE_URL', 'http://proj.mkonrad.net/d3-int-seq/');
define('CACHE_DIR', 'cache/');

function fetchOEIS($n) {
  $url = OEIS_BASE_URL . 'A' . $n . '/' . txtName($n);
  return file_get_contents($url);
}

function txtName($n) {
  return 'b' . $n . '.txt';
}

function cacheName($n) {
  return CACHE_DIR . txtName($n);
}

function inCache($n) {
  return is_readable(cacheName($n));
}

function toCache($n, $txt) {
  $f = cacheName($n);
  
  if (!is_writable(CACHE_DIR)) {
    return FALSE;
  }
  
  $fp = fopen($f, 'w');
  fwrite($fp, $txt);
  fclose($fp);
  
  return TRUE;
}

function forwardToCache($n) {
  $f = cacheName($n);
  $url = THIS_BASE_URL . $f;
  
  header('Location: ' . $url, TRUE, 303);
  die();
}

function main($n) {
  if (!inCache($n)) {
    $txt = fetchOEIS($n);
    if ($txt === FALSE) {
      header("HTTP/1.0 403 Forbidden");
      return;
    } else {
      if (!toCache($n, $txt)) {
        header("HTTP/1.0 500 Internal Server Error");        
        return;
      }
    }
  }
  
  forwardToCache($n);
}


$q = $_SERVER['QUERY_STRING'];


if (preg_match('/^A(\d{6,8})$/', $q, $matches) === 1) {
  main($matches[1]);
} else {
  header("HTTP/1.0 400 Bad Request");
  die();
}
