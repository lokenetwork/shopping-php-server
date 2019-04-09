<?php

/**
 * @name IndexController
 * @author root
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class IndexController extends BaseController {

	/*
	 * 首页默认商品
	 * */
	public function indexAction(){
		global $r_db;

		$longitude = $this->_post('longitude');
		$latitude = $this->_post('latitude');
		$distance = $this->_post('distance',"10");
		if( !$distance ){
			$distance = '10';
		}
		$page = $this->_post('p',1);
		$min_price = $this->_post('min_price');
		$min_price = 1;
		$max_price = $this->_post('max_price');
		$max_price = 5000;
		$query = $this->_post('query');
		//$query = '连衣裙 新品';
		$search_cache_id = $this->_post('search_cache_id');
		$longitude = '113.953195';
		$latitude = '22.562101';
		$redis = new Redis();
		$redis->pconnect($this->redis_server, $this->redis_port, 1);
		//查询出附近店铺.
		$time = explode(' ', microtime());

		$shop_list = $redis->rawCommand('georadius', 'shop_location', $longitude, $latitude, $distance, 'km', 'COUNT', '500');
		$time = explode(' ', microtime());

		//如果附件没有店铺，返回提示，让用户扩大半径范围。
	    if( !$shop_list ){
		    $ajax_data = [];
		    $ajax_data['search_cache_id'] = 0;
		    $ajax_data['goods_data'] = [];
		    ajaxReturn($ajax_data);
	    }
		$where = [];
		$where['shop_id'] = $shop_list;
		$where['is_check'] = 1;
		$where['lock'] = 0;
		$where['is_delete'] = 0;
		//要根据店铺的 状态 再过滤一次$shop_list
		$shop_list = $r_db->select('shop','shop_id',$where);

		$page_num = 20;
		$page_start = ($page-1) * $page_num;

		//第一个分支，用户没有提交关键字。展示附件商品
		if( !$query ){
			$goods_list = $r_db->select('goods', ['goods_id', 'goods_name', 'first_picture', 'goods_price'], ['shop_id' => $shop_list, "LIMIT" => [$page_start, $page_num]]);
			$search_cache_id = 0;
		}else{
			//搜索处理
			if( !$redis->keys($search_cache_id) ){
				//key 过期了
				$search_cache_id = 0;
			}
			//用户提交了关键词 但是，没有缓存。
			if( $query && "" == $search_cache_id ){
				$time = explode(' ', microtime());
				$search_cache_id = $this->get_search_cache_id($query,$redis,$shop_list);
				$time = explode(' ', microtime());
				if(  0 === $search_cache_id){
					//搜索不到商品
					$ajax_data = [];
					$ajax_data['search_cache_id'] = "";
					$ajax_data['goods_data'] = [];
					ajaxReturn($ajax_data);
				}
			}

			//程序跑到这里，已经获取到搜索的id，把所有ID都拿出来根据价格过滤。
			$goods_id_page = $redis->lRange($search_cache_id,0,-1) ;
			if(!$goods_id_page){
				$ajax_data = [];
				$ajax_data['search_cache_id'] = 0;
				$ajax_data['goods_data'] = [];
				ajaxReturn($ajax_data);
			}
			//把所有goods_id 拿出来，交给mysql去。
			$search_condition = [];
			$search_condition['goods_id'] = $goods_id_page;
			$search_condition['goods_price[<>]'] = [$min_price, $max_price];
			$search_condition['LIMIT'] = [$page_start, $page_num];
			$goods_list = $r_db->select('goods', ['goods_id', 'goods_name', 'first_picture', 'goods_price'],$search_condition);
		}
		$size = "_300x300";
		//循环处理图片数据
		foreach($goods_list as $k => $item){
			//获取图片后缀
			$arr = explode('.', $item['first_picture']);
			$pic_suffix = array_pop($arr);
			$first_picture = str_replace(".".$pic_suffix,$size.".".$pic_suffix,$item['first_picture']);
			$goods_list[$k]['first_picture'] = $this->img_diaplay_domain . $first_picture;
			//$goods_list[$k]['first_picture'] = 'http://192.168.0.108:8083/tmp/5c94b153cfca6_300x300.jpg';
			$goods_list[$k]['goods_name'] = mb_substr( $item['goods_name'],0,23,"utf8");
		}
		//循环，把数组一分为二
		$ajax_data = [];
		$ajax_data['search_cache_id'] = $search_cache_id;
		$goods_data = [];
		$position = 0;
		for( $i =0 ; $i < count($goods_list); $i+=2 ){
			$goods_data[$position][0] = $goods_list[$i];
			if(isset($goods_list[$i+1])){
				$goods_data[$position][1] = $goods_list[$i+1];
			}
			$position++;
		}
		$ajax_data['goods_data'] = $goods_data;
		ajaxReturn($ajax_data);
		return false;
	}

	public function shopAction(){

		$shop_id = $this->_post('shop_id');
		$page = $this->_post('p',1);
		$min_price = $this->_post('min_price');
		$max_price = $this->_post('max_price');
		$search_cache_id = $this->_post('search_cache_id');
		$goods_type = $this->_post('goods_type');
		$query = $this->_post('query');
		//$query = '半身裙';
		$redis = new Redis();
		$redis->pconnect($this->redis_server, $this->redis_port, 1);

		global $r_db;
		$page_num = 20;
		$page_start = ($page-1) * $page_num;

		$where = [];
		$where['shop_id']=$shop_id;
		$where['is_on_sale']=1;
		$where['is_delete']=0;
		$where['LIMIT']= [$page_start, $page_num];
		$where['ORDER']=['goods_id'=>'DESC'];
		if($max_price){
			$where['goods_price[<>]'] = [$min_price, $max_price];
		}
		if($goods_type){
			switch($goods_type){
				case "new":
					$where['is_new'] = 1;
					break;
				case "hot":
					$where['is_hot'] = 1;
					break;
				case "cheap":
					$where['is_cheap'] = 1;
					break;
			};
		}

		if( $query ){
			//搜索处理
			if( !$redis->keys($search_cache_id) ){
				//key 过期了
				$search_cache_id = "";
			}
			//用户提交了关键词 但是，没有缓存。
			if( $query && "" == $search_cache_id ){
				$search_cache_id = $this->get_search_cache_id($query,$redis,[$shop_id]);
				if(  0 === $search_cache_id){
					//搜索不到商品
					$ajax_data = [];
					$ajax_data['search_cache_id'] = "";
					$ajax_data['goods_data'] = [];
					ajaxReturn($ajax_data);
				}
			}

			//程序跑到这里，已经获取到搜索的id，把所有ID都拿出来根据价格过滤。
			$goods_id_page = $redis->lRange($search_cache_id,0,-1) ;
			if(!$goods_id_page){
				$ajax_data = [];
				$ajax_data['search_cache_id'] = 0;
				$ajax_data['goods_data'] = [];
				ajaxReturn($ajax_data);
			}
			//把所有goods_id 拿出来，交给mysql去。
			$where['goods_id'] = $goods_id_page;
		}
		$goods_list = $r_db->select('goods', ['goods_id', 'goods_name', 'first_picture', 'goods_price'],$where);

		$size = "_300x300";
		//循环处理图片数据
		foreach($goods_list as $k => $item){
			//获取图片后缀
			$arr = explode('.', $item['first_picture']);
			$pic_suffix = array_pop($arr);
			$first_picture = str_replace(".".$pic_suffix,$size.".".$pic_suffix,$item['first_picture']);
			$goods_list[$k]['first_picture'] = $this->img_diaplay_domain . $first_picture;
			//$goods_list[$k]['first_picture'] = 'http://192.168.0.108:8083/tmp/5c94b153cfca6_300x300.jpg';

			$goods_list[$k]['goods_name'] = mb_substr( $item['goods_name'],0,23,"utf8");
		}
		//循环，把数组一分为二
		$ajax_data = [];
		$ajax_data['search_cache_id'] = $search_cache_id;
		$goods_data = [];
		$position = 0;
		for( $i =0 ; $i < count($goods_list); $i+=2 ){
			$goods_data[$position][0] = $goods_list[$i];
			if(isset($goods_list[$i+1])){
				$goods_data[$position][1] = $goods_list[$i+1];
			}
			$position++;
		}
		$ajax_data['goods_data'] = $goods_data;
		ajaxReturn($ajax_data);
		return false;
	}

	public function shopinfoAction(){
		$shop_id = $this->_post('shop_id');
		//$shop_id = 1;
		$field = ['shop_id','shop_name','longitude','latitude','address_display'];
		global $r_db;
		$shop_info = $r_db->get('shop',$field,['shop_id'=>$shop_id]);
		ajaxReturn($shop_info);
		return false;
	}

	public function dianpuAction(){

		$longitude = $this->_post('longitude');
		$latitude = $this->_post('latitude');
		$distance = $this->_post('distance');
		if( !$distance ){
			$distance = 10;
		}
		$page = $this->_post('p',1);
		$query = $this->_post('query');
		//$query = '吕布';
		$search_cache_id = $this->_post('search_cache_id');
		$longitude = '113.953195';
		$latitude = '22.562101';
		$redis = new Redis();
		$redis->pconnect($this->redis_server, $this->redis_port, 1);
		//查询出附近店铺.
		$shop_list = $redis->rawCommand('georadius', 'shop_location', $longitude, $latitude, $distance, 'km');

		//如果附件没有店铺，返回提示，让用户扩大半径范围。
		if( !$shop_list ){
			$ajax_data = [];
			$ajax_data['shop_list'] = [];
			ajaxReturn($ajax_data);
		}

		global $r_db;
		$page_num = 20;
		$page_start = ($page-1) * $page_num;

		$where = [];
		$where['shop_id'] = $shop_list;
		$where['is_check'] = 1;
		$where['lock'] = 0;
		$where['is_delete'] = 0;
		$where['LIMIT'] = [$page_start, $page_num];
		$fields = ['shop_id','shop_name','shop_profile','pic_url','address_display'];

		if( $query ){
			$where['shop_name[~]'] = $query;
		}

		$shop_list = $r_db->select('shop',$fields,$where);
		foreach($shop_list as $k=>$item){
			$shop_list[$k]['pic_url'] = $this->img_diaplay_domain . $item['pic_url'];
		}

		$ajax_data = [];
		$ajax_data['shop_list'] = $shop_list;
		ajaxReturn($ajax_data);
		return false;
	}

	public function create_guid($namespace = '') {
		static $guid = '';
		$uid = uniqid("", true);
		$data = $namespace;
		$data .= $_SERVER['REQUEST_TIME'];
		$data .= $_SERVER['HTTP_USER_AGENT'];
		$data .= $_SERVER['LOCAL_ADDR'];
		$data .= $_SERVER['LOCAL_PORT'];
		$data .= $_SERVER['REMOTE_ADDR'];
		$data .= $_SERVER['REMOTE_PORT'];
		$hash = strtoupper(hash('ripemd128', $uid . $guid . md5($data)));
		$guid = '{' .
			substr($hash, 0, 8) .
			'-' .
			substr($hash, 8, 4) .
			'-' .
			substr($hash, 12, 4) .
			'-' .
			substr($hash, 16, 4) .
			'-' .
			substr($hash, 20, 12) .
			'}';
		return $guid;
	}

	private function get_search_cache_id($query,$redis,$shop_list){
		global $r_db;
		//分词搜索
		//根据空格切分 输入
		$arr = explode(" ",$query);
		$all_words = [];
		foreach($arr as $item){
			$all_words = array_merge($this->pullword($item,$redis),$all_words);
		}
		//生成唯一的查询ID,返回给客户端,保存在cookie,分页可以使用
		$search_cache_id = uniqid($this->create_guid());
		$words_unm = count($all_words);
		//如果拆分不到词,退出
		if( 0 == $words_unm ){
			return 0;
		}
		$new_set = [];
		//只要有一个集合为空,就是搜不到商品
		foreach($all_words as $item){
			$word_godds_set = "word_".$item."_goods";
			$key_exist = $redis->keys($word_godds_set);
			if(!$key_exist){
				return 0;
			}
		}
		$search_set = "search_set_".$search_cache_id;
		//同义词搜索集合，临时数据
		$synonym_search_set = "synonym_search_set_".$search_cache_id;
		$synonym_search_set2 = "synonym_search_set_".$search_cache_id."_2";

		//如果只有一个词
		if( 1 == $words_unm ){
			$redis->sUnionStore($search_set,"word_".$all_words[0]."_goods",$search_set);
			//todo，如果这个词有同义词，求这两个词条的合集。
			$synonym_word_list = $r_db->select('search_synonym_word','second_word',['primary_word'=>$all_words[0]]);
			if( $synonym_word_list ){
				foreach($synonym_word_list as $item){
					$redis->sUnionStore($search_set,"word_".$item."_goods",$search_set);
				}
			}
		}else{
			//循环求出每个词关联的商品ID 的交集
			for( $i=0; $i < $words_unm ;$i+=2 ){
				//如果下一个元素存在
				if( empty($all_words[$i+1]) ){
					//先清空缓存键
					$redis->delete($synonym_search_set);
					$redis->sUnionStore($synonym_search_set,"word_".$all_words[$i]."_goods",$synonym_search_set);
					$synonym_word_list = $r_db->select('search_synonym_word','second_word',['primary_word'=>$all_words[$i]]);
					if( $synonym_word_list ){
						foreach($synonym_word_list as $item){
							$redis->sUnionStore($synonym_search_set,"word_".$item."_goods",$synonym_search_set);
						}
					}
					//$word_godds_set_1 = "word_".$all_words[$i]."_goods";
					$redis->sInterStore($search_set,$synonym_search_set,$search_set);
				}else{
					$redis->delete($synonym_search_set);
					$redis->delete($synonym_search_set2);
					$redis->sUnionStore($synonym_search_set,"word_".$all_words[$i]."_goods",$synonym_search_set);
					$redis->sUnionStore($synonym_search_set2,"word_".$all_words[$i+1]."_goods",$synonym_search_set2);

					$synonym_word_list = $r_db->select('search_synonym_word','second_word',['primary_word'=>$all_words[$i]]);
					if( $synonym_word_list ){
						foreach($synonym_word_list as $item){
							$redis->sUnionStore($synonym_search_set,"word_".$item."_goods",$synonym_search_set);
						}
					}
					$synonym_word_list2 = $r_db->select('search_synonym_word','second_word',['primary_word'=>$all_words[$i+1]]);
					if( $synonym_word_list2 ){
						foreach($synonym_word_list2 as $item){
							$redis->sUnionStore($synonym_search_set2,"word_".$item."_goods",$synonym_search_set2);
						}
					}

					//$word_godds_set_1 = "word_".$all_words[$i]."_goods";
					//$word_godds_set_2 = "word_".$all_words[$i+1]."_goods";

					if(  !$redis->keys($search_set) ){
						$redis->sInterStore($search_set,$synonym_search_set,$synonym_search_set2);
					}else{
						$redis->sInterStore($search_set,$synonym_search_set,$search_set);
						$redis->sInterStore($search_set,$synonym_search_set2,$search_set);
					}
				}
			}
		}
		//求相关店铺商品的并集
		$all_shop_goods_set = "goods_shop_all_".$search_cache_id;
		$time = explode(' ', microtime());
		foreach($shop_list as $shop_id){
				$shop_set = "goods_shop_".$shop_id;
				$redis->sUnionStore($all_shop_goods_set,$shop_set,$all_shop_goods_set);
		}
		$time = explode(' ', microtime());
		//根据 shop_id 再次过滤集合
		$redis->sInterStore($search_set,$all_shop_goods_set,$search_set);

		//排序结果集，
		$search_result_cache = "search_cache_".$search_cache_id;
		$redis->sort($search_set,array('store'=>$search_result_cache));
		$redis->delete($search_set);
		$redis->delete($synonym_search_set);
		$redis->delete($synonym_search_set2);
		$redis->delete($all_shop_goods_set);
		//设置键的过期时间
		$redis->expire($search_result_cache, 3600*24);
		//如果集合不存在，就是没有数据
		if( $redis->keys($search_result_cache) ){
			return $search_result_cache;
		}else{
			return 0;
		}
	}

	private function pullword($str, $redis){

		$charset = "UTF-8";
		//词条 redis集合
		$set_name = "word_set";

		//最大的词有10个字符,这里考虑了英文单词.
		$max_word_len = 10;

		$finish_word = [];

		$search_str = $str;

		$remain_str = $search_str;

		/* 正向最大词匹配 */
		//无限循环,符合特定条件才退出
		for(; ; ){

			//如果待切分的短语 少于最大词长度
			if(mb_strlen($remain_str,$charset) < $max_word_len){
				$word_len = mb_strlen($remain_str,$charset);
			}else{
				$word_len = $max_word_len;
			}

			$maybe_word = mb_substr($remain_str, 0, $word_len, $charset);

			//判断分词是否完成
			$pullword_finish = false;

			//这个标示如果是true,则 maybe_word 里肯定有一个词.否者没有词则退出分词,分词结束
			$is_mark = false;

			for($i = 0; $i < $word_len; $i++){
				$tmp_word = mb_substr($maybe_word, 0, $word_len - $i, $charset);
				$word_exist = $redis->sIsMember($set_name, $tmp_word);

				//找到词,退出循环
				if($word_exist){
					$is_mark = true;
					array_push($finish_word, $tmp_word);

					//去除已经匹配到的短语
					$remain_str = substr($remain_str, strlen($tmp_word));
					if(mb_strlen($remain_str,$charset) <= 0){
						//分词已经完成
						$pullword_finish = true;
					}

					break;
				}else{

				}

			}

			//$maybe_word 里没有一个词,分词完成
			if(false == $is_mark){
				break;
			}

			if($pullword_finish){
				break;
			}

		}

		/* 逆向最大词匹配 */
		for(; ; ){

			//如果待切分的短语 少于最大词长度,那就从待切分短语的开头读取字符串

			//如果待切分的短语 大于最大词长度,那就从 (待切分短语长度-最大词长度[10]) 位置读取 最大词长度[10] 个字符出来匹配

			//如果待切分的短语 少于最大词长度
			if(mb_strlen($remain_str, $charset) <= $max_word_len){
				$maybe_word = mb_substr($remain_str, 0, $max_word_len, $charset);
			}else{
				$maybe_word = mb_substr($remain_str, mb_strlen($remain_str, $charset) - $max_word_len, $max_word_len, $charset);
			}

			//判断分词是否完成
			$pullword_finish = false;

			//这个标示如果是true,则 maybe_word 里肯定有一个词.否者没有词则退出分词,分词结束
			$is_mark = false;

			$word_len = mb_strlen($maybe_word, $charset);

			for($i = 0; $i < $word_len; $i++){
				$tmp_word = mb_substr($maybe_word, $i, $word_len - $i, $charset);
				$word_exist = $redis->sIsMember($set_name, $tmp_word);

				//找到词,退出循环
				if($word_exist){
					$is_mark = true;
					array_push($finish_word, $tmp_word);

					//词在待切分短语的偏移位置
					$tmp_word_position = mb_strlen($remain_str, $charset) - mb_strlen($tmp_word, $charset);

					//去除已经匹配到的短语
					$remain_str = mb_substr($remain_str, 0, $tmp_word_position, $charset);
					if(mb_strlen($remain_str, $charset) <= 0){
						//分词已经完成
						$pullword_finish = true;
					}

					break;
				}else{

				}

			}

			//$maybe_word 里没有一个词,分词完成
			if(false == $is_mark){
				break;
			}

			if($pullword_finish){
				break;
			}
		}



		return $finish_word;

	}


}
