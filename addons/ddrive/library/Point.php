<?php

namespace addons\ddrive\library;

use think\Db;

class Point
{
    /**
     * 订单完成，用户增加金额
     *
     * @param [type] $params
     * @return void
     */
    public function orderDone($params)
    {
        // 增加用户积分score
        Db::name('user')->where('id', $params['user_id'])->setInc('score', $params['price']);
        // 记录日志
        $this->addScore($params['user_id'], $params['price'], '订单奖励');
    }

    /**
     * 发帖增加积分
     *
     * @param [type] $params
     * @return void
     */
    public function messageAdd($params)
    {
        $this->addScore($params['user_id'], 5, '发起话题');
    }

    /**
     * 评论增加积分
     *
     * @param [type] $params
     * @return void
     */
    public function commentAdd($params)
    {
        $this->addScore($params['user_id'], 1, '话题被评论');
    }

    /**
     * 增加积分
     *
     * @param [type] $user_id
     * @param [type] $score
     * @return void
     */
    public function addScore($user_id, $score, $memo)
    {
        // 查询修改前积分
        $before = Db::name('user')->where('id', $user_id)->value('score');
        $log    = [
            'user_id'    => $user_id,
            'score'      => $score,
            'before'     => $before,
            'after'      => $before + $score,
            'memo'       => $memo,
            'createtime' => time(),
        ];
        Db::name('user')->where('id', $user_id)->setInc('score', $score);
        Db::name('user_score_log')->insert($log);
    }
}
