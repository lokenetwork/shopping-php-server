<?php
/**
 * @name UserController
 * @author root
 * @desc use this control must be login
 * @see
 */
class UserController extends BaseController {

  public function init(){
    parent::init();
    $this->checkLoginAction();
  }
  /**
   * get the shop info from database
   */
  public function useInfoAction($field){
    if( $field == '*' ){
      //limit select with *
      return false;
    }
    $condition = [
      "admin_id" => $_SESSION['admin_id'],
    ];
    $c_info = $GLOBALS['r_db']->get("admin_user", $field, $condition);
    return $c_info;
  }

  function logout(){
    unset($_SESSION['admin_id']);
    return false;
  }


  //Check the user is login or not
  function checkLoginAction(){
    if( !isset($_SESSION['user_id']) ){
      $responed = [
        'login_status'=>0,
        'message'=>'UserNotlogin',
      ];
      ajaxReturn($responed);
    }
  }


}