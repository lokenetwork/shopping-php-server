<?php

/**
 * @name OrderController
 * @author root
 * @desc OrderController
 *
 */
class OrderController extends UserController {

  public function init(){
    parent::init();
  }

  private function get_origin_sku_info($sku_ids){
    $origin_sku_info_fields = ['sku_value(origin_sku_value)', 'goods_price', 'sku_id', 'sku_picture'];
    $origin_sku_info_where = ['sku_id' => $sku_ids];
    return w_db()->select("goods_sku", $origin_sku_info_fields, $origin_sku_info_where);
  }

  private function get_goods_base_info($goods_ids){
    $goods_base_info_fields = ['goods_name', 'goods_id', 'first_picture'];
    $goods_base_info_where = ['goods_id' => $goods_ids];
    return w_db()->select("goods", $goods_base_info_fields, $goods_base_info_where);
  }

  private function get_selected_cart_goods_info($shop_id){
    $cart_goods_list_fields = ['goods_id', 'goods_sku', 'sku_id', 'goods_number'];
    $cart_goods_list_where = ['AND' => ['shop_id' => $shop_id, 'is_selected' => 1,]];
    return w_db()->select("cart", $cart_goods_list_fields, $cart_goods_list_where);

  }

  private function get_card_goods_list($shop_id){


    $cart_goods_list = $this->get_selected_cart_goods_info($shop_id);

    $all_sku_ids = [];
    $all_goods_ids = [];
    foreach($cart_goods_list as $cart_goods_list_item){
      array_push($all_sku_ids, $cart_goods_list_item['sku_id']);
      array_push($all_goods_ids, $cart_goods_list_item['goods_id']);
    }

    $origin_sku_info = $this->get_origin_sku_info($all_sku_ids);
    $goods_base_info = $this->get_goods_base_info($all_goods_ids);

    foreach($cart_goods_list as &$cart_goods_list_item){

      $cart_goods_list_item['goods_sku_array'] = explode(" ", $cart_goods_list_item['goods_sku']);

      //add sku info
      foreach($origin_sku_info as $origin_sku_item){
        $cart_goods_list_item['sku_data_changed'] = 'yes';
        if($origin_sku_item['sku_id'] == $cart_goods_list_item['sku_id']){
          if($origin_sku_item['origin_sku_value'] == $cart_goods_list_item['goods_sku']){
            $cart_goods_list_item['sku_data_changed'] = 'no';
            $cart_goods_list_item['goods_price'] = $origin_sku_item['goods_price'];

            $cart_goods_list_item['goods_picture'] = $origin_sku_item['sku_picture'];
          }
        }
      }

      //add basic info of goods
      foreach($goods_base_info as $goods_base_item){
        if($goods_base_item['goods_id'] == $cart_goods_list_item['goods_id']){
          $cart_goods_list_item['goods_name'] = $goods_base_item['goods_name'];
          if(!$cart_goods_list_item['goods_picture']){
            $cart_goods_list_item['goods_picture'] = $goods_base_item['first_picture'];
          }
        }
      }
    }
    unset($cart_goods_list_item);
    return $cart_goods_list;
  }

  private function getCartOrderTransportPrice($shop_id){
    return 0;
  }

  function cartOrderInfoAction(){
    $shop_id = get('shop_id');

    $respond['cart_goods_list'] = $this->get_card_goods_list($shop_id);
    $respond['transport_price'] = $this->getCartOrderTransportPrice($shop_id);

    $respond['order_amount'] = $respond['transport_price'];
    $respond['goods_number_amount'] = 0;
    foreach($respond['cart_goods_list'] as &$cart_goods_item){
      $respond['goods_number_amount'] += $cart_goods_item['goods_number'];
      $respond['order_amount'] += ($cart_goods_item['goods_price'] * $cart_goods_item['goods_number']);
      $cart_goods_item['goods_price'] = sprintf("%.2f", $cart_goods_item['goods_price'] / 100);
    }
    unset($cart_goods_item);

    $respond['order_amount'] = sprintf("%.2f", $respond['order_amount'] / 100);

    ajaxReturn($respond);
  }

  private function get_pay_type_number($pay_type_string){
    switch($pay_type_string){
      case 'wechat_pay':
        return 1;
        break;
      case 'alipay_pay':
        return 2;
        break;
      case 'arrivel_pay':
        return 3;
        break;
    }
  }

  private function create_order_number(){
    //Todo,It is not right
    return time();
  }

  function createCartOrderAction(){
    $shop_id = post('shop_id');
    $address_id = post('address_id');
    $pay_type = $this->get_pay_type_number(post('pay_type'));
    $message_to_shopkeeper = post('message_to_shopkeeper');

    $order_number = $this->create_order_number();

    //false data
    //$shop_id = 4;
    //$pay_type = 1;

    //check data
    if(!$shop_id || !$pay_type){
      ajaxReturn(post('pay_type'));
      exit;
    }

    $cart_goods_list = $this->get_card_goods_list($shop_id);

    $transport_price = $this->getCartOrderTransportPrice($shop_id);

    $order_amount = $transport_price;

    foreach($cart_goods_list as &$cart_goods_item){
      $order_amount += ($cart_goods_item['goods_price'] * $cart_goods_item['goods_number']);
    }
    unset($cart_goods_item);


    $insert_order_info_data = ['order_number' => $order_number, 'shop_id' => $shop_id, 'user_id' => $_SESSION['user_id'], 'pay_type' => $pay_type, 'transport_price' => $transport_price, 'order_amount' => $order_amount, 'message_to_shopkeeper' => $message_to_shopkeeper, 'create_time' => getTimeStamp(),];

    $order_id = w_db()->insert("order_info", $insert_order_info_data);

    $insert_order_goods_data = [];
    foreach($cart_goods_list as $cart_goods_item_index => $cart_goods_item){
      $insert_order_goods_data[$cart_goods_item_index]['order_id'] = $order_id;
      $insert_order_goods_data[$cart_goods_item_index]['goods_id'] = $cart_goods_item['goods_id'];
      $insert_order_goods_data[$cart_goods_item_index]['goods_name'] = $cart_goods_item['goods_name'];
      $insert_order_goods_data[$cart_goods_item_index]['goods_sku_id'] = $cart_goods_item['sku_id'];
      $insert_order_goods_data[$cart_goods_item_index]['goods_sku_info'] = $cart_goods_item['goods_sku'];
      $insert_order_goods_data[$cart_goods_item_index]['goods_price'] = $cart_goods_item['goods_price'];
      $insert_order_goods_data[$cart_goods_item_index]['shop_id'] = $shop_id;
      $insert_order_goods_data[$cart_goods_item_index]['goods_number_amount'] = $cart_goods_item['goods_number'];
    }
    w_db()->insert("order_goods", $insert_order_goods_data);


    $repond['order_id'] = $order_id;
    $repond['status'] = 'no_problem';


    ajaxReturn($repond);


  }

  function listAction(){

    $page = post('page', 1);


    $order_list_fields = ['order_id', 'shop_id', 'order_amount','pay_type', 'order_status', 'pay_status', 'transport_status'];
    $order_list_where = ['AND' => ['user_id' => $_SESSION['user_id']]];

    $order_list_num = w_db()->count('order_info', $order_list_where);


    $order_list = [];

    $Pagination = new Pagination($order_list_num, $page);

    $order_list_where["LIMIT"] = [$Pagination->firstRow, $Pagination->listRows];


    $order_list = w_db()->select("order_info", $order_list_fields, $order_list_where);
    if($order_list){
      $all_order_shop_ids = [];
      $all_order_ids = [];
      foreach($order_list as $order_item){
        if(!in_array($order_item['shop_id'], $all_order_shop_ids)){
          array_push($all_order_shop_ids, $order_item['shop_id']);
        }
        if(!in_array($order_item['order_id'], $all_order_ids)){
          array_push($all_order_ids, $order_item['order_id']);
        }
      }

      $all_shop_info_fields = ['name(shop_name)', 'shop_id'];
      $all_shop_info_where = ['shop_id' => $all_order_shop_ids];
      $all_shop_info = r_db()->select("shop_info", $all_shop_info_fields, $all_shop_info_where);


      $order_goods_list_field = ['goods_name', 'goods_picture', 'order_id'];
      $order_goods_list_where = ['order_id' => $all_order_ids];
      $order_goods_list = w_db()->select("order_goods", $order_goods_list_field, $order_goods_list_where);

      foreach($order_list as &$order_item){
        $order_item['order_amount'] = sprintf("%.2f", $order_item['order_amount'] / 100);

        $order_item['order_goods_list'] = [];

        foreach($all_shop_info as $shop_info_item){
          if($order_item['shop_id'] == $shop_info_item['shop_id']){
            $order_item['shop_name'] = $shop_info_item['shop_name'];
          }
        }

        foreach($order_goods_list as $order_goods_item){
          if($order_goods_item['order_id'] == $order_item['order_id']){
            array_push($order_item['order_goods_list'], $order_goods_item);
          }
        }

      }
      unset($order_item);

    }
    ajaxReturn($order_list);
  }

}