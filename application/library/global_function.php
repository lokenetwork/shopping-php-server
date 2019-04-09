<?php
/**
 * Created by PhpStorm.
 * User: loken_mac
 * Date: 1/29/16
 * Time: 11:01 PM
 */
function ajaxReturn($data, $type = '', $json_option = 0){
  if(empty($type))
    $type = 'JSON';
  switch(strtoupper($type)){
    case 'JSON' :
      // 返回JSON数据格式到客户端 包含状态信息
      header('Content-Type:application/json; charset=utf-8');
      exit(json_encode($data, $json_option));
    case 'XML'  :
      // 返回xml格式数据
      header('Content-Type:text/xml; charset=utf-8');
      exit(xml_encode($data));
    case 'JSONP':
      // 返回JSON数据格式到客户端 包含状态信息
      header('Content-Type:application/json; charset=utf-8');
      $handler = $_GET['callback'];
      exit($handler . '(' . json_encode($data, $json_option) . ');');
    case 'EVAL' :
      // 返回可执行的js脚本
      header('Content-Type:text/html; charset=utf-8');
      exit($data);
    default     :
  }
}

function GetUrlToDomain($domain) {
  $re_domain = '';
  $domain_postfix_cn_array = array("com", "net", "org", "gov", "edu", "com.cn", "cn");
  $array_domain = explode(".", $domain);
  $array_num = count($array_domain) - 1;
  if ($array_domain[$array_num] == 'cn') {
    if (in_array($array_domain[$array_num - 1], $domain_postfix_cn_array)) {
      $re_domain = $array_domain[$array_num - 2] . "." . $array_domain[$array_num - 1] . "." . $array_domain[$array_num];
    } else {
      $re_domain = $array_domain[$array_num - 1] . "." . $array_domain[$array_num];
    }
  } else {
    $re_domain = $array_domain[$array_num - 1] . "." . $array_domain[$array_num];
  }
  return $re_domain;
}

/*
 * 再封装下get,post,为以后过滤做准备,直接改yaf我们不熟
 * */
 function get($name, $default_value = ''){
  $Yaf_Request_Http = new Yaf_Request_Http();
  $value = $Yaf_Request_Http->get($name);
  if( $value === null ){
    $responed = $default_value;
  }else{
    $responed = $value;
  }
  if( is_string($responed) ){
    $responed = trim($responed);
  }
  return $responed;
}

 function post($name, $default_value = ''){
  $Yaf_Request_Http = new Yaf_Request_Http();
  $value = $Yaf_Request_Http->getPost($name);
  if( $value === null  ){
    $responed = $default_value;
  }else{
    $responed = $value;
  }
  if( is_string($responed) ){
    $responed = trim($responed);
  }
  return $responed;
}

//安全函数,过滤get跟post数据
function filer_get_post($ignore_key){
  foreach($_GET as $key=>$item){
    if( is_array($item) ){
      foreach($item as $i_key => $i_item){
        if( is_array($i_item) ){
          foreach($i_item as $j_key => $j_item){
            if( is_array($j_item) ){
              exit("get数组太长");
            }else{
              $_GET[$key][$i_key][$j_key] = htmlspecialchars($j_item);
            }
          }
        }else{
          if( !in_array($i_key,$ignore_key) ){
            $_GET[$key][$i_key] = htmlspecialchars($i_item);
          }
        }
      }
    }else{
      if( !in_array($key,$ignore_key) ){
        $_GET[$key] = htmlspecialchars($item);
      }
    }
  }


  foreach($_POST as $key=>$item){
    if( is_array($item) ){
      foreach($item as $i_key => $i_item){
        if( is_array($i_item) ){
          foreach($i_item as $j_key => $j_item){
            if( is_array($j_item) ){
              exit("post数组太长");
            }else{
              $_POST[$key][$i_key][$j_key] = htmlspecialchars($j_item);
            }
          }
        }else{
          if( !in_array($i_key,$ignore_key) ){
            $_POST[$key][$i_key] = htmlspecialchars($i_item);
          }
        }
      }
    }else{
      if( !in_array($key,$ignore_key) ){
        $_POST[$key] = htmlspecialchars($item);
      }
    }
  }

}