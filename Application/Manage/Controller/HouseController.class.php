<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-9
 * Time: 下午1:03
 */

namespace Manage\Controller;


class HouseController extends CommonController
{
    public function houseList(){
        $this->getDlist('home', $_GET['keyWord']);
    }

    public function addHouseData(){
        $backUrl    = $_GET['backUrl'];
        $table      = $_GET['table'];
        $controller = $_GET['controller'];
        $id         = $_POST['id'];
        $sql        = D($table);
        if($sql->create()){
            if(empty($id)){
                $sql->id = NULL;
                $sql->addTime = date('Y:m:d H-i-s',time());
                $result = $sql->add();
                $this->setAuth($table,$result);
            }else{
                $sql->addTime = date('Y:m:d H-i-s',time());
                $result = $sql->save();
            }
            if($result){
                $this->success('编辑成功！',U($controller.'/'.$backUrl));
            }
        }else{
            $this->error($sql->getError(),$jumpUrl='',$ajax=true);
        }
    }
}