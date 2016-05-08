<?php

/**
 * @name GoodsController
 * @author root
 * @desc LocationController
 *
 */
class LocationController extends BaseController {

  public function init(){
    parent::init();
  }

  function updateAppLocationInfoAction(){

    global $w_db;

    $app_uuid = post('app_uuid');
    //$app_uuid = '123456';
    $location_info = [
      'app_uuid' => $app_uuid,
      'latitude' => post('latitude'),
      'longitude' => post('latitude'),
    ];

   $w_db->insert_update("user_location",$location_info, [
      "app_uuid" => $app_uuid
    ]);

    $respond = [
      'status' => 1,
      'message' => 'Location success'
    ];

    ajaxReturn($respond);

    return false;
  }


}