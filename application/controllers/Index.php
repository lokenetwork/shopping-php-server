<?php
/**
 * @name IndexController
 * @author root
 * @desc
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class IndexController extends BaseController {

  /*
   * dash board
   * */
  public function indexAction($name = "Stranger"){
    return false;
  }

  function autoUpgradeAction(){
    //echo 'console.log("Auto upgrade")';
    return false;
  }

  public function indexBackAction($name = "Stranger"){
    //1. fetch query
    $get = $this->getRequest()->getQuery("get", "default value");
    //2. fetch model
    $model = new SampleModel();
    //3. assign
    $this->getView()->assign("content", $model->selectSample());
    $this->getView()->assign("name", $name);
    //phpinfo();
    //4. render by Yaf, If return fale,Yaf will not Render template
    return TRUE;
  }

  public function testAction(){
    var_dump(111);
    exit;
    $database = new medoo();

    dump($database);
    $database->insert("test", [
      "name" => "foo",
      "age" => "1"
    ]);
    dump($database->last_query());
    return false;
  }
}