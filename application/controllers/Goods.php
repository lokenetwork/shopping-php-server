<?php

/**
 * @name IndexController
 * @author root
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class GoodsController extends BaseController {


	public function detailAction(){
		global  $r_db;
		$goods_id = $this->_post('goods_id');
		//$goods_id = 2;
		$field = ['goods_id','shop_id','goods_name','goods_price','first_picture'];
		$goods_info  = $r_db->get('goods',$field,['goods_id'=>$goods_id]);
		$goods_sku  = $r_db->select('goods_sku','*',['goods_id'=>$goods_id]);

		$goods_img = $r_db->select('goods_img', '*', ['goods_id' => $goods_id]);
		$goods_intro = $r_db->get('goods_intro', 'intro', ['goods_id' => $goods_id]);

		//把所有图片合并到一个数组里面
		$all_img = [];
		array_push($all_img, $this->img_diaplay_domain . $goods_info['first_picture']);
		//array_push($all_img, "http://192.168.0.108:8083/tmp/5c94b153cfca6.jpg");
		$sku_info = [];
		foreach($goods_sku as $key=>$item){
			$sku_info[$key]['sku_name'] = $item['color'] . "+" . $item['size'];
			$sku_info[$key]['sku_id'] = $item['sku_id'];
			$sku_info[$key]['sku_price'] = $item['sku_price'];
			$sku_info[$key]['sku_color'] = 'dark';
			array_push($all_img, $this->img_diaplay_domain . $item['pic_url']);
			//array_push($all_img, "http://192.168.0.108:8083/tmp/5c94b153cfca6.jpg");
		}

		foreach($goods_img as $item){
			//array_push($all_img,$this->img_diaplay_domain.$item['pic_url']);
			//array_push($all_img, "http://192.168.0.108:8083/tmp/5c94b153cfca6.jpg");
		}

		$status = $user_id = $this->checkToken();

		if($status){
			$this->user_info = $r_db->get("client", ['client_id', 'account', 'distance'], ['client_id' => $user_id]);
			$collect_id = $r_db->get("user_collect",'collect_id',['goods_id'=>$goods_id,'client_id'=>$user_id]);
			if( $collect_id ){
				$is_like = true;
			}else{
				$is_like = false;
			}
		}else{
			$is_like = false;
		}
		//查询用户是否收藏此商品

		$ajax_data = [];
		$ajax_data['info']['goods_id'] = $goods_info['goods_id'];
		$ajax_data['info']['shop_id'] = $goods_info['shop_id'];
		$ajax_data['info']['goods_name'] = $goods_info['goods_name'];
		$ajax_data['info']['goods_price'] = $goods_info['goods_price'];

		$ajax_data['img'] = $all_img;
		$ajax_data['like'] = $is_like;
		$ajax_data['sku'] = $sku_info;
		$ajax_data['intro'] = $goods_intro;

		ajaxReturn($ajax_data);
		exit;

	}


	public function viewAction(){
		global  $w_db;
		$view_data = [];
		$Ctime = new Ctime();


		$goods_id = $this->_post('goods_id');
		$shop_id = $this->_post('shop_id');
		$token = $this->_post('token');
		if($token){
			$status = $user_id = $this->checkToken();
			if(!$status){
				$view_data['user_id'] = 0;

			}else{
				$user_info = $w_db->get("client", ['client_id', 'account', 'distance'], ['client_id' => $user_id]);
				$view_data['user_id'] = $user_info['client_id'];
			}
		}else{
			$view_data['user_id'] = 0;
		}

		$view_data['goods_id'] = $goods_id;
		$view_data['shop_id'] = $shop_id;
		$view_data['view_time'] = $Ctime->long_time();;

		$w_db->insert("goods_view",$view_data);
		$ajax_data = [];
		$ajax_data['status'] = 1;
		ajaxReturn($ajax_data);
		exit;

	}

}
