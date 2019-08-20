<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/8/17
 * Time: 10:56
 */

namespace app\common\controller;


use think\Controller;
use think\facade\Config;

class Backend extends Controller
{
    protected $requset = null;

    /**
     * 密钥
     * @var string
     */
    protected $key = 'JYO2O01';

    //默认公共模板
    protected $layout = 'default';
    protected $layout_on = true;

    /**
     * 项目初始化
     */
    protected function initialize()
    {
        //获取配置
        $this->requset = request();
        $site          = [
            'jsname'     => 'backend/' . strtolower($this->requset->controller()),
            'actionname' => strtolower($this->requset->action()),
            'cdnurl'     => $this->requset->domain(),
            'version'    => config('admin.version'),
            'debug'      => config('app.app_debug'),
            'des_key'    => Config::get("des_key"),
            'key'        => $this->key
        ];
        $this->assign('config', json_encode($site, true));
        $this->assign('version', $site['version']);
        if ($this->layout_on) {
            $this->view->engine->layout('layout/' . $this->layout);
        }
    }
}