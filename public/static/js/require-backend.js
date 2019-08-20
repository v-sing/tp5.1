require.config({
    urlArgs: "v=" + requirejs.s.contexts._.config.config.version,
    packages: [{
        name: 'moment',
        location: '../libs/moment',
        main: 'moment'
    }
    ],
    include: ['jquery','layui'],
    paths: {
        'layui': '../libs/layui/layui/layui',
        'jquery': '../libs/jquery/dist/jquery',
    },
    shim: {
        'addons': ['backend'],
        'jquery': [
            'css!../libs/layui/layui/css/layui.css'
        ],
        'layui': [
            'css!../libs/layui/style/admin.css'
        ]
        // 'md5': [
        //     '../libs/crypto-js-develop/src/core',
        //     '../libs/crypto-js-develop/src/cipher-core',
        //
        // ]
    },
    map: {
        '*': {
            'css': '../libs/require-css/css'
        }
    },
    waitSeconds: 30,
    charset: 'utf-8', // 文件编码
    baseUrl: requirejs.s.contexts._.config.config.cdnurl + '/static/js/', //资源基础路径
});

require(['jquery', 'layui'], function ($, undefined) {
    //初始配置
    var Config = requirejs.s.contexts._.config.config;
    //将Config渲染到全局
    window.Config = Config;
    var paths = {};
    // 避免目录冲突
    paths['backend/'] = 'backend/';
    require.config({paths: paths});
    // 初始化
    $.ajaxSetup({ //设置全局ajax选项参数
        headers: {
            api_token: "api"
        },
    })
    $(function () {
        layui.config({
            base: requirejs.s.contexts._.config.config.cdnurl + '/static/libs/layui/' //静态资源所在路径
        }).extend({
            index: 'lib/index' //主入口模块
        });
        require(['backend'], function (Backend) {
            //加载相应模块
            if (Config.jsname) {
                require([Config.jsname], function (Controller) {
                    if (typeof Controller == 'undefined') {

                        console.error(Config.jsname + ':未定义Controller');
                        return false;
                    }
                    if (Controller.hasOwnProperty(Config.actionname)) {
                        Controller[Config.actionname]();
                    } else {
                        if (Controller.hasOwnProperty("_empty")) {
                            Controller._empty();
                        }
                    }
                }, function (e) {
                    console.error(e);
                    // 这里可捕获模块加载的错误
                });
            }
        });
    });
});
