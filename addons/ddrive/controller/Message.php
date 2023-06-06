<?php

namespace addons\ddrive\controller;

use addons\ddrive\library\Wechat;
use addons\ddrive\model\MessageComment;
use app\common\controller\Api;
use think\Db;

/**
 * 话题接口
 */
class Message extends Api
{

    protected $noNeedLogin = ['index', 'info', 'comments','notice','notice_info'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \addons\ddrive\model\Message;
    }

    /**
     * 话题接口
     *
     * @return void
     */
    public function index()
    {
        $model    = $this->model;
        $pageSize = $this->request->param('pageSize', 10);
        $map      = [];

        $query = $model->order('weigh desc,id desc');
        // 查找文章标题或用户昵称
        if ($this->request->param('keywords')) {
            $query->where(function ($querys) {
                $querys->where('title', 'LIKE', '%' . $this->request->param('keywords') . '%');
                $userIds = Db::name('user')->where('nickname', 'LIKE', '%' . $this->request->param('keywords') . '%')->column('id');
                if ($userIds) {
                    $querys->whereOr('user_id', 'in', $userIds);
                }
            });
        }
        $list = $query->paginate($pageSize)->each(function ($item) {
            $item['images'] = $item['images'] ? explode(',', $item['images']) : [];
            $item['user']   = $item['user'];
            return $item;
        });
        $this->success("", $list);
    }

    /**
     * 我创建的话题
     *
     * @return void
     */
    public function my()
    {
        $model          = $this->model;
        $pageSize       = $this->request->param('pageSize', 10);
        $map            = [];
        $map['user_id'] = $this->auth->id;
        if ($this->request->param('keywords')) {
            $map['title'] = ['LIKE', '%' . $this->request->param('keywords') . '%'];
        }
        $list = $model->where($map)->order('weigh desc,id desc')->paginate($pageSize)->each(function ($item) {
            $item['user'] = $item['user'];
            return $item;
        });
        $this->success("", $list);
    }

    /**
     * 创建话题
     *
     * @return void
     */
    public function add()
    {
        $data = [
            'title'   => $this->request->post('title'),
            'content' => $this->request->post('content'),
            'images'  => $this->request->post('images'),
            'user_id' => $this->auth->id,
        ];
        if (!$data['title']) {
            $this->error('请填写标题');
        }
        // 内容安全检测
        if (!Wechat::msgSecCheck($data['title'])) {
            $this->error('标题含有敏感内容，请修改后重新提交');
        }
        if (!Wechat::msgSecCheck($data['content'])) {
            $this->error('内容含有敏感内容，请修改后重新提交');
        }
        $model = $this->model;
        $res   = $model->data($data)->save();
        if ($res) {
            // 增加会员积分
            $pointLib = new \addons\ddrive\library\Point;
            $pointLib->messageAdd($data);
            $this->success('发布成功');
        } else {
            $this->error('发布失败');
        }
    }

    /**
     * 获取详情
     *
     * @return void
     */
    public function info()
    {
        $messageId    = $this->request->param('message_id');
        $info         = $this->model->get($messageId);
        $info['user'] = $info['user'];
        $this->success('', $info);
    }

    /**
     * 删除
     *
     * @return void
     */
    public function delete()
    {
        $messageId = $this->request->param('message_id');
        $info      = $this->model->where('id', $messageId)->where('user_id', $this->auth->id)->find();
        if (!$info) {
            $this->error('话题不存在');
        }
        $res = $this->model->where('id', $messageId)->where('user_id', $this->auth->id)->delete();
        if ($res) {
            // 删除评论
            MessageComment::where('message_id', $messageId)->delete();
            $this->success("删除成功");
        } else {
            $this->error('删除失败');
        }
    }

    /**
     * 评论列表
     *
     * @return void
     */
    public function comments()
    {
        $model             = new MessageComment();
        $messageId         = $this->request->param('message_id');
        $pageSize          = $this->request->param('pageSize', 10);
        $map               = [];
        $map['message_id'] = $messageId;

        $list = $model->where($map)->order('id desc')->paginate($pageSize)->each(function ($item) {
            $item['user'] = $item['user'];
            return $item;
        });
        $this->success("", $list);
    }

    /**
     * 评论
     *
     * @return void
     */
    public function addComment()
    {
        $messageId = $this->request->param('message_id');
        $info      = $this->model->where('id', $messageId)->find();
        if (!$info) {
            $this->error('话题不存在');
        }
        $comment        = $this->request->param('comment');
        $MessageComment = new MessageComment;
        if (!Wechat::msgSecCheck($comment)) {
            $this->error('评论内容含有敏感内容，请修改后重新提交');
        }
        $res = $MessageComment->data([
            'user_id'    => $this->auth->id,
            'message_id' => $messageId,
            'comment'    => $comment,
        ])->save();
        if ($res) {
            // 增加会员积分
            $pointLib = new \addons\ddrive\library\Point;
            $pointLib->commentAdd($info);
            $this->success("评论成功");
        } else {
            $this->error('评论失败');
        }
    }

    /**
     * 文章
     * @ApiMethod (POST)
     * @param File $file 文件流
     */
    public function notice()
    {
        $category_id = $this->request->param('category_id');
        $list = Db::name('new_info')
            ->where('category_id', $category_id)
            ->field('title,id,createtime,new_info_img')
            ->order('id desc')
            ->limit(10)
            ->select();
        if ($list) {
            foreach ($list as &$v) {
                $v['createtime'] = date('Y-m-d H:i:s', $v['createtime']);
                $v['new_info_img'] = cdnurl($v['new_info_img']);
            }
        }
        $this->success('成功',['list_array'=>$list ? $list : []]);
    }

    /**
     * 文章详情
     * @ApiMethod (POST)
     * @param File $file 文件流
     */
    public function notice_info()
    {
        $id = $this->request->param('id');
        if (empty($id)) {
            $this->error('参数错误');
        }
        $list = Db::name('new_info')
            ->where('id', $id)
            ->field('title,id,createtime,content')
            ->find();
        if (!$list) {
            $this->error('该文章不存在');
        }
        $list['createtime'] = date('Y-m-d H:i:s', $list['createtime']);
        $this->success('成功',['list_array'=>$list ? $list : []]);
    }
}
