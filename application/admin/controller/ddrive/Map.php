<?php
/**
 * Map.php
 * @des
 * Created by PhpStorm.
 * Date: 2020/12/29
 * Time: 14:24
 */

namespace app\admin\controller\ddrive;

use app\common\controller\Backend;

class Map extends Backend
{
    public function index()
    {
//        return $this->view->fetch();
        return redirect("http://map.xiaoerdj.com/");
    }
}
