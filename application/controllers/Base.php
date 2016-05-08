<?php

/**
 * Created by PhpStorm.
 * User: loken_mac
 * Date: 1/22/16
 * Time: 9:52 PM
 */
class BaseController extends Yaf_Controller_Abstract {

  protected $passport_name;
  protected $root_domain;
  protected $passport_domain;
  public $passport_info;

  public function init(){

    $_SESSION['user_id'] = 5;

    $this->passport_name = Yaf_Application::app()->getConfig()->passport->name;

    $this->passport_domain = Yaf_Application::app()->getConfig()->passport->domain;

    //$this->passport_info = $this->getPassportInfo();
   
    $this->root_domain = GetUrlToDomain($_SERVER['SERVER_NAME']);


    if( ini_get("yaf.environ") == 'dev' ){
      $this->getView()->assign("client_less", '<script src="/static/common_js/less.min.js"></script>');
    }else{
      $this->getView()->assign("client_less", '');
    }
    $this->getView()->company_name = $this->get_company_name();

    $this->passport_deal();
  }

  /*
   * synchronous login or synchronous logout depend on passport.
   * */
  private function passport_deal(){
    global $w_db;

    if( $this->passport_info->passport_login_status > 0){

      //If not login,auto login
      if(!isset($_SESSION['user_id'])){


        $condition = ["passport_user_id" => $this->passport_info->passport_user_info->user_id];
        $field = ['user_id'];
        $sh_user_info = $w_db->get("user", $field, $condition);

        $shop_info_field = ['shop_id'];
        $shop_info_where = ['user_id'=>$sh_user_info['user_id']];
        $shop_info = $w_db->get("shop_info", $shop_info_field, $shop_info_where);


        if( !$shop_info ){
          $this->setViewPath(VIEW_PATH);
          $login_url = 'http://'.$this->passport_domain.'/Index/shop';
          $this->getView()->assign("title", 'Notice');
          $this->getView()->assign("desc", '!');
          $this->getView()->assign("url", $login_url);
          $this->getView()->assign("type", 'warning');
          $this->getView()->display('common/tips.html');
          exit;
        }else{
          $sh_user_id = $sh_user_info['user_id'];
          $_SESSION['user_id'] = $sh_user_id;
          $_SESSION['shop_id'] = $shop_info['shop_id'];
          $sh_update_data['last_login_time'] = $GLOBALS['TimeStamp'];
          $sh_update_data['last_login_ip'] = $_SERVER['REMOTE_ADDR'];
          $w_db->update('user', $sh_update_data,['user_id'=>$sh_user_id]);
        };

      }

    }else if( $this->passport_info->passport_login_status === -1 ) {
      //Delete session
      unset($_SESSION['user_id']);
    }
  }

  /**
   * get the group name quickly
   */
  public function get_company_name(){
    if( !Yaf_Registry::get('company_name') ){
      $condition = ["name" => 'company_name',];
      $field = ['value'];
      $company_info = r_db()->get("setting", $field, $condition);
      Yaf_Registry::set('company_name',$company_info['value']);
    }
    return Yaf_Registry::get('company_name');
  }

  /*
   * pdo view the error
   * */
  function pdo_error_demo(){
    global $r_db;
    var_dump($r_db->pdo->errorInfo());
  }

  function getPassportInfo(){
    $Curl = new Curl();
    $data['passport_login_key'] = '-';
    if( isset($_COOKIE[$this->passport_name]) ){
      $data['passport_login_key'] = $_COOKIE[$this->passport_name];
    }
    $passport_info = ($Curl->http($this->passport_domain.'/User/echoJsonInfo', $data, 'GET', array("Content-type: text/html; charset=utf-8")));
    return json_decode($passport_info);
  }



}