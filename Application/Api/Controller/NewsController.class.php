<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-8
 * Time: 上午11:58
 */

namespace Api\Controller;


use Api\Exception\NewsException;
use Api\Exception\UserException;
use Api\Model\GroupsModel;
use Api\Validate\IDMustBePostiveInt;
use Think\Controller;

class NewsController extends CommonController
{


    /**
     * @apiDefine UserNotFoundError
     *
     * @apiError UserNotFound The id of the User was not found.
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */

    /**
     * @api {get} /user/:id Request User information
     * @apiName GetUser
     * @apiGroup User
     *
     * @apiParam {Number} id Users unique ID.
     *
     * @apiSuccess {String} firstname Firstname of the User.
     * @apiSuccess {String} lastname  Lastname of the User.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "firstname": "John",
     *       "lastname": "Doe"
     *     }
     *
     * @apiUse UserNotFoundError
     */

    /**
     * @api {put} /user/ Modify User information
     * @apiName PutUser
     * @apiGroup User
     *
     * @apiParam {Number} id          Users unique ID.
     * @apiParam {String} [firstname] Firstname of the User.
     * @apiParam {String} [lastname]  Lastname of the User.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *
     * @apiUse UserNotFoundError
     */

    //获取全部新闻
    public function getAllNews()
    {
        $result = D('news')->relation(true)->field('newstypeID,status', true)->order('id desc')->select();
        for ($i = 0; $i < count($result); $i++) {
            $result[$i]['title'] = $this->subtextTrim($result[$i]['title'], 15);
            $result[$i]['content'] = $this->subtext(strip_tags($result[$i]['content']), 20);
            $result[$i]['image'] = C('img_prefix') . $result[$i]['image'];
        }
        if (!$result) {
            $result = (new NewsException())->getException();
            $this->ajaxReturn($result);
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $result
        ]);
    }


    //获取首页推荐新闻
    public function getHomeNews()
    {
        $result = D('news')->relation(true)->field('newstypeID,status', true)->where("status=1")->select();
        for ($i = 0; $i < count($result); $i++) {
            $result[$i]['title'] = $this->subtextTrim($result[$i]['title'], 15);
            $result[$i]['content'] = $this->subtext(strip_tags($result[$i]['content']), 20);
            $result[$i]['image'] = C('img_prefix') . $result[$i]['image'];
        }
        if (!$result) {
            $result = (new NewsException())->getException();
            $this->ajaxReturn($result);
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $result
        ]);
    }


    /*字符串截断函数+省略号*/
    public function subtextTrim($text, $length)
    {
        if (mb_strlen($text, 'utf8') > $length)
            return mb_substr($text, 0, $length, 'utf8') . ' ';
        return $text;
    }


    //获取指定新闻
    public function getNews()
    {
        (new IDMustBePostiveInt())->goCheck();
        $news = D('news');
        $result = $news->relation('newsType')->where("id=" . $_POST['id'])->field('status', true)->find();
        $result['image'] = C('img_prefix') . $result['image'];
        if (!preg_match('/(http:\/\/)|(https:\/\/)/i', $result['content'])) {
            $result['content'] = replacePicUrl($result['content'], "http://admin.yjsina.com");
        }
        $click = $news->where("id=" . $_POST['id'])->setInc('click');
        $sql = "select title,image,click,id,content from icpnt_news q where q.`status`=1 order by rand() limit 4";
        $status = $news->query($sql);
        for ($i = 0; $i < count($status); $i++) {
            $status[$i]['title'] = $this->subtext($status[$i]['title'], 15);
            $status[$i]['image'] = C('img_prefix') . $status[$i]['image'];
            $status[$i]['content'] = $this->subtext(strip_tags($status[$i]['content']), 20);
        }
        $result['Recommend'] = $status;
        if (!$result) {
            $result = (new NewsException())->getException();
            $this->ajaxReturn($result);
        }
        $this->ajaxReturn([
            'code' => 200,
            'msg' => 'success',
            'data' => $result
        ]);
    }


}