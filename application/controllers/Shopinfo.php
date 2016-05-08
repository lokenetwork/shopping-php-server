<?php

/**
 * @name OrderController
 * @author root
 * @desc ShopInfoController
 *
 */
class ShopInfoController extends BaseController {

  public function init(){
    parent::init();
  }



  function shopNameAction(){

    $shop_id = get('shop_id');

    $shop_id = 4;

    $fields = ['name'];
    $where = ['AND' => ['shop_id' => $shop_id]];
    $shop_info  = r_db()->get("shop_info", $fields, $where);
    $respond['shop_name'] = $shop_info['name'];
    ajaxReturn($respond);
  }

}