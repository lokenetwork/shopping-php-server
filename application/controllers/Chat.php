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

class ChatController extends UserController {

	public function init(){
		parent::init();
	}

	public function sessionreadAction(){
		global $w_db;
		$shop_id = $this->_post('shop_id');

		$where = [
			'client_id'=>$this->user_info['client_id'],
			'shop_id'=>$shop_id,
		];
		$w_db->update('chat_session',['client_read'=>1],$where);
		ajaxReturn(['status'=>1]);
		return false;
	}

	function getchathistoryAction(){
		global $r_db;
		$shop_id = $this->_post('shop_id');
		$page = $this->_post('p',1);
		//$page = 1;
		//$client_id = 1;
		$page_num = 10;
		$page_start = ($page-1) * $page_num;

		$where = [
			'shoper_id'=>$shop_id,
			'client_id'=>$this->user_info['client_id'],
			"ORDER" => ["message_id"=>"DESC"],
			'LIMIT' =>  [$page_start, $page_num]
		];

		$field = ["message_id","message_type(messageType)","sender_type(from)","shoper_id(shoperId)","client_id(clientId)","content"];
		//倒序取出最后20条聊天记录。
		$message_list = $r_db->select('chat_history',$field,$where);
		ajaxReturn($message_list);
		return false;
	}

	function sessionlistAction(){
		global $r_db;

		//查询出所有会话列表
		$where = [
			'chat_session.client_id'=>$this->user_info['client_id'],
			"ORDER" => ["chat_session.client_read"=>"ASC"]
		];
		$field = ['chat_session.client_id','chat_session.shop_id','chat_session.client_read','shop.shop_name','shop.logo'];
		$session_list = $r_db->select("chat_session",["[>]shop" => ["shop_id" => "shop_id"]],$field,$where);
		foreach($session_list as $key=>$item){
			$session_list[$key]['logo'] = $this->img_diaplay_domain.$item['logo'];
		}
		ajaxReturn($session_list);
		return false;



	}


}
