define(['layui'], function (undefined) {
    var Backend = {
        des: {
            //加密
            encode: function (options) {
                var message = '';
                var data = {};
                for (key in options.data) {
                    message += options.data[key];
                    data[key] = options.data[key];
                }
                message += Config.key;
                data['key'] = CryptoJS.MD5(message).toString()
                message = JSON.stringify(data);
                Backend.ajax({url: '/encrypt', data: {data: message}}, function (res) {
                    delete options.data
                    options = $.extend({data: {datami: res}}, options);
                    Backend.ajax(options);
                })
            }
        },
        //发送Ajax请求
        ajax: function (options, success, error) {
            var des = false;
            if (options.hasOwnProperty('des') && options.des == true) {
                des = true;
            }
            delete options.des;
            options = typeof options === 'string' ? {url: options} : options;
            var index;
            if (typeof options.loading === 'undefined' || options.loading) {
                index = layer.load(1, {
                    shade: [1, '#fff'] //0.1透明度的白色背景
                });
            }
            options = $.extend({
                type: "POST",
                dataType: "json",
                success: function (ret) {
                    index && layer.close(index);
                    ret = Backend.events.onAjaxResponse(ret);
                    if (ret.status == '0000') {
                        Backend.events.onAjaxSuccess(ret, success);
                    } else {
                        Backend.events.onAjaxError(ret, error);
                    }
                },
                error: function (xhr) {
                    index && layer.close(index);
                    var ret = {status: xhr.status, codemsg: xhr.statusText, data: null};
                    Backend.events.onAjaxError(ret, error);
                }
            }, options);
            if (des) {
                Backend.des.encode(options)
            } else {
                $.ajax(options);
            }

        },
        //查询Url参数
        query: function (name, url) {
            if (!url) {
                url = window.location.href;
            }
            name = name.replace(/[\[\]]/g, "\\$&");
            var regex = new RegExp("[?&/]" + name + "([=/]([^&#/?]*)|&|#|$)"),
                results = regex.exec(url);
            if (!results)
                return null;
            if (!results[2])
                return '';
            return decodeURIComponent(results[2].replace(/\+/g, " "));
        },
        events: {
            //请求成功的回调
            onAjaxSuccess: function (ret, onAjaxSuccess) {
                var data = typeof ret.data !== 'undefined' ? ret.data : null;
                var codemsg = typeof ret.codemsg !== 'undefined' && ret.codemsg ? ret.codemsg : 'Operation completed'

                if (typeof onAjaxSuccess === 'function') {
                    var result = onAjaxSuccess.call(this, data, ret);
                    if (result === false)
                        return;
                }

            },
            //请求错误的回调
            onAjaxError: function (ret, onAjaxError) {
                var data = typeof ret.data !== 'undefined' ? ret.data : null;
                if (typeof onAjaxError === 'function') {
                    var result = onAjaxError.call(this, data, ret);
                    if (result === false) {
                        return;
                    }
                }

            },
            //服务器响应数据后
            onAjaxResponse: function (response) {
                try {
                    var ret = typeof response === 'object' ? response : JSON.parse(response);
                    if (!ret.hasOwnProperty('status')) {
                        $.extend(ret, {status: -2, codemsg: response, data: null});
                    }
                } catch (e) {
                    var ret = {status: -1, codemsg: e.message, data: null};
                }
                return ret;
            }
        }

    }
    window.Backend = Backend;
    return Backend;
})