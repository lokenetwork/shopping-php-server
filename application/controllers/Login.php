<?php
/**
 * @name IndexController
 * @author root
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Keychain; // just to make our life simpler
use Lcobucci\JWT\Signer\Hmac\Sha256; // 如果在使用 ECDSA 可以使用 Lcobucci\JWT\Signer\Ecdsa\Sha256

class LoginController extends BaseController {

	private $key;

	public function init(){
		parent::init();
	}

	public function indexAction(){
		$account = $this->_post('account');
		$password = $this->_post('password');
		$account = "13723772347";
		$password = "88888888";

		$hash = password_hash($password, PASSWORD_DEFAULT);

		global $r_db;
		$user_info  = $r_db->get("client","*",['account'=>$account]);
		if(!$user_info){
			$ajax_data = [];
			$ajax_data['status'] = -1;
			$ajax_data['status_name'] = "账号不存在，请核对账号";
			ajaxReturn($ajax_data);
		}else{
			//token 过期时间
			$token_time = 3600*24*7;
			$password_right = password_verify($password, $user_info['password']);
			if($password_right){
				$builder = new Builder();
				$signer = new Sha256();
				// 设置发行人
				$builder->setIssuer('http://example.com');
				// 设置接收人
				$builder->setAudience('http://example.org');
				// 设置id
				$builder->setId('4f1g23a12aa', true);
				// 设置生成token的时间
				$builder->setIssuedAt(time());
				// 设置在60秒内该token无法使用
				$builder->setNotBefore(time() - 60);
				// 设置过期时间
				$builder->setExpiration(time() + $token_time);
				// 给token设置一个id
				$builder->set('client_id', $user_info['client_id']);
				$builder->set('client_type', 'buyer_auth');
				// 对上面的信息使用sha256算法签名
				$builder->sign($signer, $this->password_key);
				// 获取生成的token
				$token = (string)$builder->getToken();
				$ajax_data = [];
				$ajax_data['status'] = 1;
				$ajax_data['status_name'] = "认证通过";
				$ajax_data['token'] = $token;
				$ajax_data['token_time'] = $token_time-100;
				$ajax_data['nick_name'] = $user_info['nick_name'];
				$ajax_data['account'] = $user_info['account'];
				$ajax_data['distance'] = $user_info['distance'];
				ajaxReturn($ajax_data);
			}else{
				$ajax_data = [];
				$ajax_data['status'] = -2;
				$ajax_data['status_name'] = "密码错误，请重新输入密码";
				ajaxReturn($ajax_data);
			}
		}
		return false;
	}

	/**
	 * just display the login page
	 */
	public function index2Action($name = "Stranger"){

		$builder = new Builder();
		$signer = new Sha256();
		// 设置发行人
		$builder->setIssuer('http://example.com');
		// 设置接收人
		$builder->setAudience('http://example.org');
		// 设置id
		$builder->setId('4f1g23a12aa', true);
		// 设置生成token的时间
		$builder->setIssuedAt(time());
		// 设置在60秒内该token无法使用
		$builder->setNotBefore(time() + 60);
		// 设置过期时间
		$builder->setExpiration(time() + 3600);
		// 给token设置一个id
		$builder->set('uid', 1);
		// 对上面的信息使用sha256算法签名
		$builder->sign($signer, $this->key);
		// 获取生成的token
		$token = (string)$builder->getToken();


		$parse = (new Parser())->parse($token);
		$signer = new Sha256();
		$data = $parse->verify($signer, $this->key);// 验证成功返回true 失败false
		var_dump($data);
		var_dump($parse->getClaims());
		exit;

		return TRUE;
	}

	/*
	 * submit action deal
	 * */
	public function postAction(){
		$account = trim($this->_post('account'));
		$password = trim($this->_post('password'));
		$keep_login = intval($this->_post('keep_login'));

		//get the possible admin list
		$condition = ['OR' => ["admin_name" => $account, "admin_email" => $account, "mobile" => $account,]];
		$field = ['group_id', 'admin_password', 'admin_name', 'admin_id'];
		$admin_list = $GLOBALS['r_db']->select("admin_user", $field, $condition);


		foreach($admin_list as $item){
			if(password_verify($password, $item['admin_password'])){
				$_SESSION['admin_name'] = $item['admin_name'];
				$_SESSION['admin_id'] = $item['admin_id'];
				$group_id = $item['group_id'];
				break;
			}
		}
		if(isset($_SESSION['admin_id']) && $_SESSION['admin_id']){
			$Ctime = new Ctime();
			$time = $Ctime->long_time();
			$last_ip = $_SERVER['REMOTE_ADDR'];
			//Update login info
			$GLOBALS['w_db']->update('admin_user', ['last_login' => $time, 'last_ip' => $last_ip], ['admin_id' => $_SESSION['admin_id']]);

			//管理组权限
			if($_SESSION['admin_id'] == 1){
				$_SESSION['rights'] = "all_privilege";
			}else{
				if($group_id){
					//Get the people right
					$condition = ["id" => $group_id,];
					$field = 'rights';
					$_SESSION['rights'] = $GLOBALS['r_db']->get("admin_group", $field, $condition);
				}else{
					$_SESSION['rights'] = "";
				}
			}
			$respond_data['status'] = 1;
			$respond_data['msg'] = '登陆成功!';
			$session_name = ini_get('session.name');
			if($keep_login && isset($_COOKIE[$session_name])){
				setcookie($session_name, $_COOKIE[$session_name], time() + 3600 * 24 * 7, '/');
			}

		}else{
			if($admin_list){
				$respond_data['status'] = 0;
				$respond_data['msg'] = '账号或密码错误!';
			}else{
				$respond_data['status'] = -1;
				$respond_data['msg'] = '账号不存在!';
			}
		}
		ajaxReturn($respond_data);
		return false;
	}


}
