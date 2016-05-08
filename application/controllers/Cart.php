<?php

/**
 * @name CartController
 * @author root
 * @desc CartController
 *
 */
class CartController extends UserController {

  public function init(){
    parent::init();
  }

  function addAction(){
    $goods_id = post('goods_id');
    //$goods_number = post('goods_number');
    $goods_sku = post('goods_sku');

    $goods_number = 1;


    //check the card data is exit or not
    $cart_id_where = [
      "AND" => [
        "goods_id" => $goods_id,
        "user_id" => $_SESSION['user_id'],
      ]
    ];
    if($goods_sku){
      $cart_id_where['AND']['goods_sku'] = $goods_sku;
    }
    $cart_id = w_db()->get("cart", 'cart_id', $cart_id_where);

    if($cart_id){
      w_db()->update("cart", ["goods_number[+]" => $goods_number,], ["cart_id" => $cart_id]);
    }else{
      //get the shop id
      $shop_id = w_db()->get("goods", "shop_id", ["goods_id" => $goods_id]);

      $inser_card_data = ["goods_id" => $goods_id, "goods_number" => $goods_number, 'shop_id' => $shop_id, 'user_id' => $_SESSION['user_id'], 'add_time' => getTimeStamp(),];
      if($goods_sku){
        $inser_card_data['goods_sku'] = $goods_sku;
      }
      w_db()->insert("cart", $inser_card_data);
    }
    $respond['message'] = 'Add success!';
    $respond['status'] = 1;
    ajaxReturn($respond);
  }

}