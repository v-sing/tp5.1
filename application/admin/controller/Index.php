<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/8/17
 * Time: 9:46
 */

namespace app\admin\controller;


use app\common\controller\Backend;

class Index extends Backend
{

    public function index()
    {
        $this->view->engine->layout(false);
        return $this->fetch();
    }

    public function test()
    {

        return $this->fetch();
    }
}