<?php
/**
 * @name GoodsController
 * @author root
 * @desc 商品控制器
 *
 */
class ShopController extends BaseController {

  public function init(){
    parent::init();
  }
	public function viewAction(){
		global  $w_db;
		$view_data = [];
		$Ctime = new Ctime();

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

		$view_data['shop_id'] = $shop_id;
		$view_data['view_time'] = $Ctime->long_time();;

		$w_db->insert("shop_view",$view_data);
		$ajax_data = [];
		$ajax_data['status'] = 1;
		ajaxReturn($ajax_data);
		exit;

	}

}
