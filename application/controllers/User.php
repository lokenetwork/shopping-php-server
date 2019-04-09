<?php
/**
 * @name UserController
 * @author root
 * @desc use this control must be login
 * @see
 */

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Keychain; // just to make our life simpler
use Lcobucci\JWT\Signer\Hmac\Sha256; // 如果在使用 ECDSA 可以使用 Lcobucci\JWT\Signer\Ecdsa\Sha256

class UserController extends BaseController {
	public $shop_id;
	public $user_info;

	public function init(){
		parent::init();
		$status = $user_id = $this->checkToken();
		if(!$status){
			//返回信息错误。
			$ajax_data = [];
			$ajax_data['status'] = -1;
			$ajax_data['status_name'] = "登录错误";
			ajaxReturn($ajax_data);
		}else{
			global $r_db;
			$this->user_info = $r_db->get("client", ['client_id', 'account', 'distance'], ['client_id' => $user_id]);
		}
	}

	public function editnameAction(){
		$name = $this->_post('name');
		global $w_db;
		$w_db->update("client",['nick_name'=>$name],['client_id'=>$this->user_info['client_id']]);
		$ajax_data = [];
		$ajax_data['status'] = 1;
		$ajax_data['status_name'] = "修改成功";
		ajaxReturn($ajax_data);
		return false;
	}

	public function editdistanceAction(){
		$distance = $this->_post('distance');
		global $w_db;
		$w_db->update("client",['distance'=>$distance],['client_id'=>$this->user_info['client_id']]);
		$ajax_data = [];
		$ajax_data['status'] = 1;
		$ajax_data['status_name'] = "修改成功";
		ajaxReturn($ajax_data);
		return false;
	}

	public function editpasswordAction(){
		global $w_db;
		$old_password = $this->_post('old_password');
		$new_password = $this->_post('new_password');
		//$old_password = "88888888";
		//$new_password = "77777777";
		$this->user_info['password'] = $w_db->get("client", 'password', ['client_id' => $this->user_info['client_id']]);
		$password_right = password_verify($old_password, $this->user_info['password'] );
		if($password_right){
			$hash = password_hash($new_password, PASSWORD_DEFAULT);
			$w_db->update("client",['password'=>$hash],['client_id'=>$this->user_info['client_id']]);
			$ajax_data = [];
			$ajax_data['status'] = 1;
			$ajax_data['status_name'] = "修改成功";
			ajaxReturn($ajax_data);
		}else{
			$ajax_data = [];
			$ajax_data['status'] = -2;
			$ajax_data['status_name'] = "修改密码失败，旧密码错误。";
			ajaxReturn($ajax_data);
		}

		return false;
	}

	public function collectAction(){
		global $r_db;

		$page = $this->_post('p',1);
		$page_num = 20;
		$page_start = ($page-1) * $page_num;

		$fields = ["goods.goods_id","goods.goods_name","goods.first_picture","goods.goods_price"];
		$goods_list = $r_db->select("user_collect",["[>]goods" => ["goods_id" => "goods_id"]],$fields,['client_id'=>$this->user_info['client_id'], "LIMIT" => [$page_start, $page_num]]);
		$size = "_300x300";
		//循环处理图片数据
		foreach($goods_list as $k => $item){
			//获取图片后缀
			$arr = explode('.', $item['first_picture']);
			$pic_suffix = array_pop($arr);
			$first_picture = str_replace(".".$pic_suffix,$size.".".$pic_suffix,$item['first_picture']);
			$goods_list[$k]['first_picture'] = $this->img_diaplay_domain . $first_picture;
			$goods_list[$k]['first_picture'] = 'http://192.168.0.108:8083/tmp/5c94b153cfca6_300x300.jpg';
			$goods_list[$k]['goods_name'] = mb_substr( $item['goods_name'],0,23,"utf8");
		}

		//循环，把数组一分为二
		$goods_data = [];
		$position = 0;
		for( $i =0 ; $i < count($goods_list); $i+=2 ){
			$goods_data[$position][0] = $goods_list[$i];
			if(isset($goods_list[$i+1])){
				$goods_data[$position][1] = $goods_list[$i+1];
			}
			$position++;
		}
		$ajax_data = [];
		$ajax_data['status'] = 1;
		$ajax_data['status_name'] = "获取成功";
		$ajax_data['goods_data'] = $goods_data;
		ajaxReturn($ajax_data);
		return false;
	}

	public function likeAction(){
		$like = $this->_post('like');
		//$like = true;
		$goods_id = $this->_post('goods_id');

		//$goods_id = 2;
		global $w_db;

		if( $like ){
			$w_db->update('goods',['collect[+]'=>1],['goods_id'=>$goods_id]);
			if(!$w_db->select("user_collect",'*',['goods_id'=>$goods_id,"client_id"=>$this->user_info['client_id']])){
				$w_db->insert("user_collect",['goods_id'=>$goods_id,"client_id"=>$this->user_info['client_id']]);
			};
		}else{
			$w_db->update('goods',['collect[-]'=>1],['goods_id'=>$goods_id]);
			$w_db->delete("user_collect",['goods_id'=>$goods_id,"client_id"=>$this->user_info['client_id']]);
		}
		$ajax_data = [];
		$ajax_data['status'] = 1;
		$ajax_data['status_name'] = "修改成功";
		ajaxReturn($ajax_data);
		return false;
	}

	function logout(){
		unset($_SESSION['admin_id']);
		return false;
	}



}
