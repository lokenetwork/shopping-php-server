<?php

/**
 * @name GoodsController
 * @author root
 * @desc GoodsController
 *
 */
class GoodsController extends BaseController {

  public function init(){
    parent::init();
  }


  function hotAction(){

    $app_latitude = post('latitude');
    $only_get_hot_goods_number = post('only_get_hot_goods_number');
    $app_longitude = post('longitude');
    $goods_name = post('goods_name');
    $page = post('page', 1);


    $app_longitude = 114.1211000000;
    $app_latitude = 22.6032330000;


    $Map = new Map();

    //Get near the shop
    $shop_info = $Map->get_nearby_shop($app_longitude, $app_latitude);
    $hot_goods_condition = [];
    $hot_goods_condition['ORDER'] = ['shop_id', $shop_info['all_shop_ids']];
    $hot_goods_condition['AND'] = ['is_delete' => 0, 'is_admin_promote' => 1, 'is_on_sale' => 1, 'shop_id' => $shop_info['all_shop_ids']];


    if($goods_name){
      $hot_goods_condition['AND']['goods_name[~]'] = $goods_name;
    }
    $hot_goods_num = r_db()->count('goods', $hot_goods_condition);

    if($hot_goods_num > 0){
      $Pagination = new Pagination($hot_goods_num, $page);


      $hot_goods_condition["LIMIT"] = [$Pagination->firstRow, $Pagination->listRows];
      $fields = ['goods_id', 'goods_name', 'first_picture', 'goods_price', 'shop_id'];
      $goods_list = r_db()->select('goods', $fields, $hot_goods_condition);

      //shop_id map
      $shop_location_info = [];
      foreach($goods_list as &$single_goods){
        $single_goods['goods_price'] = sprintf("%.2f", ($single_goods['goods_price'] / 100));
        $single_goods['first_picture'] = Yaf_Application::app()->getConfig()->image_server . $single_goods['first_picture'];
        $single_goods['first_picture'] = 'http://test.vengood.com/resource/attachment/images/2016/01/dZc7H7Vd7FSfeQrKQff777ahSQCA8a.png';
        if(!isset($shop_location_info[$single_goods['shop_id']])){
          foreach($shop_info['list'] as $single_shop){
            if($single_shop['shop_id'] == $single_goods['shop_id']){
              $shop_location_info[$single_goods['shop_id']] = $Map->get_distance($app_longitude, $app_latitude, $single_shop['longitude'], $single_shop['latitude']);
            }
          }
        }
        $single_goods['distance'] = $shop_location_info[$single_goods['shop_id']];
      }

      $respond['goods_list'] = $goods_list;

      if(empty($goods_list)){
        $respond['have_more_hot_goods'] = false;
      }else{
        $respond['have_more_hot_goods'] = true;
      }

      //var_dump($respond);
      //exit;
    }else{
      $respond['goods_list'] = [];
      $respond['have_more_hot_goods'] = false;
    }
    ajaxReturn($respond);

  }


  function get_category_model_attribute_info($category_id){
    //SELECT the category model id
    $category_model_id = r_db()->get('category', 'model_id', ['cat_id' => $category_id]);

    //IF the category had bind the goods model, go on
    if($category_model_id){
      $goods_model_info_where = ['id' => $category_model_id];
      $goods_model_spec_ids = r_db()->get('goods_model', 'spec_ids', $goods_model_info_where);
    }


    if($goods_model_spec_ids){
      $goods_model_spec_ids = $goods_model_spec_ids ? unserialize($goods_model_spec_ids) : array();

      $goods_spec_ids = [];
      foreach($goods_model_spec_ids as $key => $item){
        $goods_model_spec_info[$item['id']]['is_attr'] = $item['is_attr'];
        $goods_model_spec_info[$item['id']]['is_required'] = $item['is_required'];
        $goods_spec_ids[] = $item['id'];
      }

      if(!empty($goods_spec_ids)){
        //$sql = "select * from `$spec` where id in ($id) order by find_in_set(id,'$id')";

        $goods_spec_info_filed = ['id', 'name', 'input_type', 'show_type', 'value'];
        $goods_spec_info_where = ['AND' => ['id' => $goods_spec_ids], "ORDER" => ["id", $goods_spec_ids]];
        $goods_spec_info = r_db()->select('goods_spec', $goods_spec_info_filed, $goods_spec_info_where);
        $category_model_attribute_info['special_attribute_info']['special_attribute_num'] = 0;
        $category_model_attribute_info['list'] = [];
        if($goods_spec_info){
          $i = 0;
          foreach($goods_spec_info as $k => $v){
            $category_model_attribute_info['list'][$i]['id'] = $v['id'];
            $category_model_attribute_info['list'][$i]['name'] = $v['name'];
            /*
            if( $v['name'] == 'color' ){
              $goods_model_spec_info[$v['id']]['is_attr'] = 1;
            }
            */
            $category_model_attribute_info['list'][$i]['input_type'] = $v['input_type'];
            $category_model_attribute_info['list'][$i]['show_type'] = $v['show_type'];
            $category_model_attribute_info['list'][$i]['attr_type'] = $goods_model_spec_info[$v['id']]['is_attr'];

            if($goods_model_spec_info[$v['id']]['is_attr'] == 0){
              $category_model_attribute_info['special_attribute_info']['special_attribute_num']++;
              $category_model_attribute_info['list'][$i]['special_attribute_index'] = $category_model_attribute_info['special_attribute_info']['special_attribute_num'];
              $category_model_attribute_info['special_attribute_info']['title'][$category_model_attribute_info['list'][$i]['special_attribute_index']] = $v['name'];
            }
            $category_model_attribute_info['list'][$i]['is_required'] = $goods_model_spec_info[$v['id']]['is_required'];
            $category_model_attribute_info['list'][$i]['spec_value'] = unserialize($v['value']);
            $i++;
          }
        }
        return $category_model_attribute_info;
      }
    }
  }


  function baseInfoAction(){
    $goods_id = get('goods_id');

    $goods_info_fields = ['goods_id', 'goods_name', 'goods_brief', 'goods_price', 'first_picture'];
    $goods_info_where = ['goods_id' => $goods_id];
    $goods_info = r_db()->get("goods", $goods_info_fields, $goods_info_where);
    $goods_info['goods_price'] = sprintf("%.2f", $goods_info['goods_price'] / 100);
    ajaxReturn($goods_info);
  }

  function goodsPicturesAction(){
    $goods_id = get('goods_id');

    $goods_pictures_fields = ['goods_picture'];
    $goods_pictures_where = ['goods_id' => $goods_id];
    $goods_pictures_list = r_db()->select("goods_image", $goods_pictures_fields, $goods_pictures_where);
    //var_dump($goods_pictures_list);
    ajaxReturn($goods_pictures_list);
  }

  private function get_goods_sku_attr($goods_id){
    //get the cheapest price in the sku
    $most_cheap_sku_price_fields = ['sku_value'];
    $most_cheap_sku_price_where['AND'] = ['goods_id' => $goods_id];
    $most_cheap_sku_price_where['ORDER'] = ['goods_price ASC'];

    $most_cheap_sku_info = r_db()->get("goods_sku", $most_cheap_sku_price_fields, $most_cheap_sku_price_where);
    $most_cheap_sku = [];
    foreach(explode(" ", $most_cheap_sku_info['sku_value']) as $key => $value){
      $most_cheap_sku_name_value = explode(":", $value);
      $most_cheap_sku[$most_cheap_sku_name_value[0]] = $most_cheap_sku_name_value[1];
    }

    $table_goods_attr = Yaf_Application::app()->getConfig()->mysql->table_prefix . 'goods_attr';
    $table_goods_spec = Yaf_Application::app()->getConfig()->mysql->table_prefix . 'goods_spec';
    $goods_sku_info_fields = 'ga.attr_id,ga.attr_values,gs.name';

    $table_sql = "{$table_goods_attr} ga,{$table_goods_spec} gs";
    $table_where = "ga.attr_id = gs.id AND ga.goods_id = '{$goods_id}' AND ga.is_sku_attr = 1 ORDER BY gs.id ";

    $sql = "SELECT {$goods_sku_info_fields} FROM {$table_sql} WHERE {$table_where} ";


    $goods_sku_attr_info = r_db()->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    //loop,add attribute
    foreach($goods_sku_attr_info as &$sku_item){
      $sku_attr_values_draf = trim($sku_item['attr_values'], " ");
      $sku_attr_values_draf = explode(" ", $sku_attr_values_draf);
      $sku_item['attr_values'] = [];

      foreach($sku_attr_values_draf as $sku_attr_values_draf_item_index => $sku_attr_values_draf_item){
        $sku_item['attr_values'][$sku_attr_values_draf_item_index]['value'] = $sku_attr_values_draf_item;
        //loop, add is_active
        foreach($most_cheap_sku as $most_cheap_sku_attr_name => $most_cheap_sku_attr_value){
          if($sku_item['name'] == $most_cheap_sku_attr_name){
            if($sku_attr_values_draf_item == $most_cheap_sku_attr_value){
              $sku_item['attr_values'][$sku_attr_values_draf_item_index]['is_active'] = 'active';
            }else{
              $sku_item['attr_values'][$sku_attr_values_draf_item_index]['is_active'] = '';
            }
          }
        }
      }
    }
    unset($sku_item);

    return $goods_sku_attr_info;
  }

  private function get_goods_sku_price($goods_id){
    $sku_price_fields = ['sku_value','goods_price'];
    $sku_price_where['AND'] = ['goods_id' => $goods_id];

    $sku_price_info = r_db()->select("goods_sku", $sku_price_fields, $sku_price_where);
    foreach( $sku_price_info as &$item ){
      $item['goods_price'] = sprintf("%.2f", ($item['goods_price'] / 100));
    }
    unset($item);
    //var_dump($sku_price_info);
    return $sku_price_info;
  }

  function goodsSkuInfoAction(){
    $goods_id = get('goods_id');
    $goods_sku_info['attr'] = $this->get_goods_sku_attr($goods_id);
    $goods_sku_info['price'] = $this->get_goods_sku_price($goods_id);
    ajaxReturn($goods_sku_info);
    exit;
  }


}