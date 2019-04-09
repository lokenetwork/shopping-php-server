<?php
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Keychain; // just to make our life simpler
use Lcobucci\JWT\Signer\Hmac\Sha256; // 如果在使用 ECDSA 可以使用 Lcobucci\JWT\Signer\Ecdsa\Sha256
use Lcobucci\JWT\ValidationData;
/**
 * Created by PhpStorm.
 * User: loken_mac
 * Date: 1/22/16
 * Time: 9:52 PM
 */
class BaseController extends Yaf_Controller_Abstract {

	protected $passport_name;
	protected $root_domain;
	protected $passport_domain;
	protected $img_upload_domain;
	protected $img_diaplay_domain;
	protected $password_key;

	public function init(){
		$this->root_domain = GetUrlToDomain($_SERVER['SERVER_NAME']);
		$this->redis_port = Yaf_Application::app()->getConfig()->redis->port;
		$this->redis_server = Yaf_Application::app()->getConfig()->redis->server;
		$this->img_upload_domain = Yaf_Application::app()->getConfig()->img->upload_domain;
		$this->img_diaplay_domain = Yaf_Application::app()->getConfig()->img->diaplay_domain;
		$this->password_key = Yaf_Application::app()->getConfig()->password_key;

		$this->getView()->assign("css_rel", CSS_REL);
		$this->getView()->assign("css_type", CSS_TYPE);
		if(ini_get("yaf.environ") == 'dev'){
			$this->getView()->assign("client_less", '<script src="http://cdn.bootcss.com/less.js/1.7.0/less.min.js"></script>');
		}else{
			$this->getView()->assign("client_less", '');
		}
		$this->getView()->company_name = $this->get_company_name();

	}

	/**
	 * get the group name quickly
	 */
	public function get_company_name(){
		$condition = ["name" => 'company_name',];
		$field = ['value'];
		$company_info = $GLOBALS['r_db']->get("setting", $field, $condition);
		return $company_info['value'];
	}

	/*
	 * pdo 查看错误信息demo
	 * */
	function pdo_error_demo(){
		global $r_db;
		var_dump($r_db->pdo->errorInfo());
	}

	/*
	 * 再封装下get,post,为以后过滤做准备,直接改yaf我们不熟
	 * */
	protected function _get($name, $default_value = ''){
		$Yaf_Request_Http = new Yaf_Request_Http();
		$value = $Yaf_Request_Http->get($name);
		if($value === null){
			$responed = $default_value;
		}else{
			$responed = $value;
		}
		if(is_string($responed)){
			$responed = trim($responed);
		}
		return $responed;
	}

	protected function _post($name, $default_value = ''){
		$Yaf_Request_Http = new Yaf_Request_Http();
		$value = $Yaf_Request_Http->getPost($name);
		if($value === null){
			$responed = $default_value;
		}else{
			$responed = $value;
		}
		if(is_string($responed)){
			$responed = trim($responed);
		}
		return $responed;
	}

	//Check the user is login or not
	protected	function checkToken(){
		$token = $this->_post('token');
		//$token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImp0aSI6IjRmMWcyM2ExMmFhIn0.eyJpc3MiOiJodHRwOlwvXC9leGFtcGxlLmNvbSIsImF1ZCI6Imh0dHA6XC9cL2V4YW1wbGUub3JnIiwianRpIjoiNGYxZzIzYTEyYWEiLCJpYXQiOjE1NTM5OTkyNjMsIm5iZiI6MTU1NDAwMjg2MywiZXhwIjoxNTU0MDAyODYzLCJjbGllbnRfaWQiOiIxIn0.1s6j1tZtWSKZuzxO6EwbuFmpzpIqRm0xsJ_ld-dlJmE';
		if( !$token ){
			return 0;
		}
		$parse = (new Parser())->parse($token);
		$signer = new Sha256();
		$status = $parse->verify($signer, $this->password_key);// 验证成功返回true 失败false
		if(!$status){
			return 0;
		}else{
			//todo 这里的验证要重新优化一下。 还有 not before 没有验证
			//判断token是否过期
			$exp = $parse->getClaim('exp');
			if( $exp < time() ){
				return 0;
			}else{
				return $parse->getClaim('client_id');
			}
		}
	}


}
