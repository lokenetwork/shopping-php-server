<?php

/**
 * @name GoodsController
 * @author root
 * @desc
 *
 */
class AppController extends AuthController {

  public function init(){
    parent::init();
  }

  function setAppIdAction(){

    global $w_db;

    $Cstring = new Cstring();

    $app_id = $Cstring->randString('30');
    $app_where = ['app_id'=>$app_id];
    $app_id_had_use = $w_db->count('user_location',$app_where);

    $search_count = 0;
    $max_search_count = 50;
    while($app_id_had_use !== 0 && $search_count < $max_search_count ){
      $search_count++;
      $app_id = $Cstring->randString('30');
      $app_id_had_use = $w_db->count('user_location',$app_where);
    };

    if( $search_count >= $max_search_count ){
      $app_respond = [
        'status'=>0,
        'app_id'=>'',
        'message'=>'System busy,please reflesh'
      ];
    }else{
      $blank_location_data = [
        'app_id'=>$app_id
      ];
      $insert_result = $w_db->insert('user_location',$blank_location_data);
      if( $insert_result ){
        $app_respond = [
          'status'=>1,
          'app_id'=>$app_id,
          'message'=>'app_id set successly',
        ];
      }else{
        $app_respond = [
          'status'=>0,
          'app_id'=>'',
          'message'=>'set error,please reflesh'
        ];
      }
    }

    ajaxReturn($app_respond);
    return false;
  }


}