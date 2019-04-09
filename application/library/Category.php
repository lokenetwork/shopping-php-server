<?php
/**
 * @name CategoryModel
 * @desc CategoryModel 商品分類
 * @author root
 */
class Category {
    public function __construct() {
    }   
    
    public function test() {
        echo 'Hello World!';
    }

    function get_sub_category($fields,$parent_id=0,$shop_id){
        global $r_db;
        if(in_array("*", $fields)){
            exit('禁止用星号查询分类表');
        }
        $condition['AND']['shop_id'] = $shop_id;
        $condition['AND']['parent_id'] = $parent_id;

        $data = $r_db->select("shop_category", $fields, $condition);
        return $data;
    }

    function get_shop_category($category_id){
        global $r_db;

        $return_str = '';
        $category_info = [];

        //获取3层的分类
        for( $i=0; $i < 4 ;$i++ ){

            $info = $r_db->get('shop_category','*',['category_id'=>$category_id]);
            $category_info[] = $info['category_name'];
            $category_id = $info['parent_id'];

            if( 0 == $info['parent_id'] ){
                break;
            }

        }

        krsort($category_info);

        foreach($category_info as $item){
            $return_str .= $item.' > ';
        }

        $return_str = trim($return_str,' > ');

        return $return_str;

    }
}
