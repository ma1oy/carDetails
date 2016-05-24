<?php

class Curl {
//  private $_connections;
//  private $_success;
  private $_ch;

  public function __construct() {
//    $this->_connections = [];
//    $this->_success = [];
  }

  public function set($url, $referrer = 'http://www.google.com') {
//    $this->_connections[] = $ch = curl_init();
    $this->_ch = $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; ry:38.0) Gecko/20100101 Firefox/38.0');
    curl_setopt($ch, CURLOPT_REFERER, $referrer);
    // Чтобы данные, полученные в результате запроса, сохранялись в перменную
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//    $this->_success[] = !!$data;
//    curl_close($ch);
    return $ch;
//    return curl_exec($ch);
  }
  
  public function getContent($ch) {
    return curl_exec($ch);
  }

  public function close($ch) {
//    if ($ch) curl_close($ch);
    curl_close($ch);
  }

//  public function closeFast($ch) {
//    var_dump($ch);
//    curl_close($ch);
//  }

//  public function closeLast() {
//    $lastIndex = count($this->_connections) - 1;
//    if ($this->_success[$lastIndex]) {
//      curl_close($this->_connections[$lastIndex]);
//      array_pop($this->_connections);
//      array_pop($this->_success);
//    }
//  }
}