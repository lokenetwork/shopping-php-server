<?php

/**
 * @name IndexController
 * @author root
 * @desc AreaController
 */
class AreaController extends Yaf_Controller_Abstract {

  public function getChinaProvinceAction(){
    $r_medoo = new medoo();
    $pronvince = $r_medoo->select("Areas", [
      "area_id",
      "area_name"
    ], [
      'AND'=>[
        "parent_id" => 1,
        'area_type'=>2
      ],
      "ORDER" => "area_name_en ASC",
    ]);
    $this->getView()->assign("pronvince", $pronvince);
    return true;
  }



}