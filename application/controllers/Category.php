<?php

/**
 * @name IndexController
 * @author root
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class CategoryController extends UserController {

  protected $Category;
  public function init(){
    parent::init();
    $this->Category = new Category();
  }

  /*
   * 分类列表
   * */
  public function indexAction(){
    global $r_db;

    $page = get('page', 1);
    $parent_id = get('parent_category_id', 0);
    $this->getView()->assign('parent_category_id', $parent_id);

    $category_name = get('cat_name');
    $this->getView()->assign('category_name', $category_name);

    $condition = [];
    $condition["AND"]['shop_id'] = $this->shop_id;
    if($category_name){
      $condition['AND']['category_name[~]'] = $category_name;
    }

    if( $parent_id ){
      $condition["AND"]['parent_id'] = $parent_id;
    }
    $spec_num = $r_db->count('shop_category', $condition);

    $Pagination = new Pagination($spec_num, $page, 20);
    $this->getView()->assign('pagination', $Pagination->show());

    $condition["LIMIT"] = [$Pagination->firstRow, $Pagination->listRows];
    $fields = "*";
    $c_list = $r_db->select('shop_category', $fields, $condition);

    foreach($c_list as $key=>$item){
      $c_list[$key]['full_name'] =$this->Category->get_shop_category($item['category_id']);
    }


    $this->getView()->assign('category_list',$c_list);


    return TRUE;
  }

  function goodsCategoryAddAction(){
    $this->display('goodsCategoryAdd');
    return false;
  }

  function goodsCategoryAddPostAction(){
    global $w_db;

    $parent_id = intval(post('parent_category_id'));
    $cat_name = post('cat_name');
    $sort_order = intval(post('sort_order'));

    $data =[];
    $data['category_name'] = $cat_name;
    $data['shop_id'] = $this->shop_id;
    $data['parent_id'] = $parent_id;

    if( $parent_id ){
      //查询出父级等信息
      $parent_cate_info = $w_db->get("shop_category","*",['category_id'=>$parent_id]);
      $data['level'] = $parent_cate_info['level']+1;
    }else{
      $data['level'] = 0;
    }
    $data['sort'] = $sort_order;

    $w_db->insert('shop_category',$data);

    $this->getView()->assign("title", '操作提醒');
    $this->getView()->assign("desc", '商品分类添加成功!');
    $this->getView()->assign("url", '/Category/goodsCategoryAdd');
    $this->getView()->assign("type", 'success');
    $this->getView()->display('common/tips.html');
    return false;
  }

  function goodsCategoryEditAction(){

    global $r_db;
    $c_id = $this->_get("id");
    $condition = [];
    $condition['AND']['shop_id'] = $this->shop_id;
    $condition['AND']['category_id'] = $c_id;
    $c_info = $r_db->get('shop_category',"*",$condition);

    $current_level = $c_info['level'];
    $p_id = $c_info['parent_id'];

    while($current_level>0){
      $condition = [];
      $condition['AND']['category_id'] = $p_id;
      $info = $r_db->get('shop_category',"*",$condition);
      $_GET['category_level_'.($info['level'])] = $info['category_id'];
      $current_level = $info['level'];
      $p_id = $info['parent_id'];
    }

    $this->getView()->assign('category_info',$c_info);
    $this->getView()->assign('parent_category_id',$c_info['parent_id']);
    $this->display('goodsCategoryEdit');
    return false;
  }

  function goodsCategoryEditPostAction(){
    global $w_db;

    $parent_id = intval(post('parent_category_id'));
    $cat_name = post('cat_name');
    $sort_order = intval(post('sort_order'));
    $c_id = intval(post('id'));

    $data =[];
    $data['category_name'] = $cat_name;
    $data['shop_id'] = $this->shop_id;
    $data['parent_id'] = $parent_id;

    if( $parent_id ){
      //查询出父级等信息
      $parent_cate_info = $w_db->get("shop_category","*",['category_id'=>$parent_id]);
      if( $parent_cate_info['category_id'] == $c_id ){
        $this->getView()->assign("title", '操作提醒');
        $this->getView()->assign("desc", '父级分类选择错误!');
        //$this->getView()->assign("url", '/Category');
        $this->getView()->assign("type", 'error');
        $this->getView()->display('common/tips.html');
        return false;
      }
      $data['level'] = $parent_cate_info['level']+1;
    }else{
      $data['level'] = 0;
    }
    $data['sort'] = $sort_order;

    $condition = [];
    $condition['AND']['category_id'] = $c_id;
    $condition['AND']['shop_id'] = $this->shop_id;

    $w_db->update('shop_category',$data,$condition);

    $this->getView()->assign("title", '操作提醒');
    $this->getView()->assign("desc", '商品分类修改成功!');
    $this->getView()->assign("url", '/Category');
    $this->getView()->assign("type", 'success');
    $this->getView()->display('common/tips.html');
    return false;
  }


  function echoSubCategoryAction($parent_id){
    $fields = ['category_id','category_name'];
    $result = $this->Category->get_sub_category($fields,$parent_id,$this->shop_id);
    if( !$result ){
      $result = 'no_sub_category';
    }
    ajaxReturn($result);
    return false;
  }




}