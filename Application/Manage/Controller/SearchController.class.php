<?php
namespace Manage\Controller;
use Think\Controller;
class SearchController extends CommonController {

    public function searchList(){

        $this->getDlist('search',$_GET['keyWord']);
    }
}
?>