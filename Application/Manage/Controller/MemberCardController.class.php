<?php

namespace Manage\Controller;

use Manage\Model\BannerModel;
use Think\Controller;

class MemberCardController extends CommonController
{

    public function membercardList()
    {
        $this->getDlist('membercard', $_GET['keyWord']);
    }


}

?>