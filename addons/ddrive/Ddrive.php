<?php

namespace addons\ddrive;

use app\common\library\Menu;
use think\Addons;

/**
 * 插件
 */
class Ddrive extends Addons
{

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        $menu = [
            [
                'name'    => 'ddrive',
                'title'   => '代驾管理',
                'icon'    => 'fa fa-magic',
                'sublist' => [
                    [
                        'name'    => 'ddrive/banner',
                        'title'   => '轮播图管理',
                        'icon'    => 'fa fa-file-picture-o',
                        'sublist' => [
                            ['name' => 'ddrive/banner/index', 'title' => '查看'],
                            ['name' => 'ddrive/banner/edit', 'title' => '变更'],
                            ['name' => 'ddrive/banner/del', 'title' => '删除'],
                            ['name' => 'ddrive/banner/multi', 'title' => '批量更新'],
                        ],
                    ],
                    [
                        'name'    => 'ddrive/order',
                        'title'   => '订单管理',
                        'icon'    => 'fa fa-exchange',
                        'sublist' => [
                            ['name' => 'ddrive/order/index', 'title' => '查看'],
                            ['name' => 'ddrive/order/edit', 'title' => '变更'],
                            ['name' => 'ddrive/order/del', 'title' => '删除'],
                            ['name' => 'ddrive/order/multi', 'title' => '批量更新'],
                        ],
                    ],
                    [
                        'name'    => 'ddrive/message',
                        'title'   => '话题列表',
                        'icon'    => 'fa fa-bars',
                        'sublist' => [
                            ['name' => 'ddrive/message/index', 'title' => '查看'],
                            ['name' => 'ddrive/message/edit', 'title' => '变更'],
                            ['name' => 'ddrive/message/del', 'title' => '删除'],
                            ['name' => 'ddrive/message/multi', 'title' => '批量更新'],
                        ],
                    ],
                    [
                        'name'    => 'ddrive/message_comment',
                        'title'   => '话题评论',
                        'icon'    => 'fa fa-comment-o',
                        'sublist' => [
                            ['name' => 'ddrive/message_comment/index', 'title' => '查看'],
                            ['name' => 'ddrive/message_comment/edit', 'title' => '变更'],
                            ['name' => 'ddrive/message_comment/del', 'title' => '删除'],
                            ['name' => 'ddrive/message_comment/multi', 'title' => '批量更新'],
                        ],
                    ],
                    [
                        'name'    => 'ddrive/feedback',
                        'title'   => '意见反馈',
                        'icon'    => 'fa fa-feed',
                        'sublist' => [
                            ['name' => 'ddrive/feedback/index', 'title' => '查看'],
                            ['name' => 'ddrive/feedback/edit', 'title' => '变更'],
                            ['name' => 'ddrive/feedback/del', 'title' => '删除'],
                            ['name' => 'ddrive/feedback/multi', 'title' => '批量更新'],
                        ],
                    ],
                    [
                        'name'    => 'ddrive/apply',
                        'title'   => '代驾申请',
                        'icon'    => 'fa fa-check-square-o',
                        'sublist' => [
                            ['name' => 'ddrive/apply/index', 'title' => '查看'],
                            ['name' => 'ddrive/apply/edit', 'title' => '变更'],
                            ['name' => 'ddrive/apply/del', 'title' => '删除'],
                            ['name' => 'ddrive/apply/multi', 'title' => '批量更新'],
                        ],
                    ],
                    [
                        'name'    => 'ddrive/withdraw',
                        'title'   => '提现管理',
                        'icon'    => 'fa fa-cny',
                        'sublist' => [
                            ['name' => 'ddrive/withdraw/index', 'title' => '查看'],
                            ['name' => 'ddrive/withdraw/edit', 'title' => '变更'],
                            ['name' => 'ddrive/withdraw/del', 'title' => '删除'],
                            ['name' => 'ddrive/withdraw/multi', 'title' => '批量更新'],
                        ],
                    ],
                ],
            ],
        ];
        Menu::create($menu);
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {
        Menu::delete("ddrive");
        return true;
    }

    /**
     * 插件启用方法
     * @return bool
     */
    public function enable()
    {
        Menu::enable("ddrive");
        return true;
    }

    /**
     * 插件禁用方法
     * @return bool
     */
    public function disable()
    {
        Menu::disable("ddrive");
        return true;
    }

}
