<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-9
 * Time: 上午9:50
 */

namespace Api\Model;


use Api\Service\UserToken;
use Think\Model\RelationModel;

class FeedModel extends RelationModel
{
    protected $_link = array(

    );

    public function addFeed($uid){
        $Feed = D("feed");
        $data['content'] = $_POST['content'];
        $data['telphone'] = $_POST['tel'];
        $data['addTime'] = date("Y-m-d H:i:s",time());
        $data['userID'] = $uid;
        $result = $Feed->add($data);
        return $result;
    }



}
