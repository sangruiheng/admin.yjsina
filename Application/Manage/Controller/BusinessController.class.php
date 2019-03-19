<?php

namespace Manage\Controller;

use Manage\Model\BannerModel;
use Think\Controller;

class BusinessController extends CommonController
{

    public function businessList()
    {
        $this->getDlist('business', $_GET['keyWord']);
    }



}

?>