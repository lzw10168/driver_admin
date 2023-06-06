<?php

namespace addons\cwmap;

use app\common\library\Menu;
use think\Addons;

/**
 * lksms
 */
class Cwmap extends Addons {

    /**
     * 插件安装方法
     * @return bool
     */
    public function install() {
        $menu = [
            [
                'name' => 'cwmap',
                'title' => '地图',
                'icon' => 'fa fa-location-arrow',
                'remark' => '地图管理',
                'sublist' => [
                    [
                        'name' => 'cwmap/maplocation',
                        'title' => '位置信息',
                        'icon' => 'fa fa-map-o',
                        'remark' => '位置信息管理',
                        'ismenu' => 1,
                        'sublist' => [
                            ['name' => 'cwmap/maplocation/index', 'title' => '查看'],
                            ['name' => 'cwmap/maplocation/add', 'title' => '添加'],
                            ['name' => 'cwmap/maplocation/edit', 'title' => '修改'],
                            ['name' => 'cwmap/maplocation/del', 'title' => '删除'],
                        ]
                    ]
                ]
            ]
        ];
        Menu::create($menu, 'cwmap');
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall() {
        Menu::delete('cwmap');
        return true;
    }

    /**
     * 插件启用方法
     * @return bool
     */
    public function enable() {
        Menu::enable('cwmap');
        return true;
    }

    /**
     * 插件禁用方法
     * @return bool
     */
    public function disable() {
        Menu::disable('cwmap');
        return true;
    }

    protected function writeToFile($pathname, $data) {
        $search = $replace = [];
        foreach ($data as $k => $v) {
            $search[] = $k;
            $replace[] = $v;
        }
        $stub = file_get_contents($pathname);
        $content = str_replace($search, $replace, $stub);
        return file_put_contents($pathname, $content);
    }

}
