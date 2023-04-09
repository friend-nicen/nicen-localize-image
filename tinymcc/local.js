(function () {

    const $ = jQuery; //JQ


    /*
    * 警告文字块
    * ed.selection.setContent("文字");
    * ed.selection.select(ed.getBody(), true);
    * */
    tinymce.create('tinymce.plugins.local', {
        init: function (ed, url) {

            /*
            * 替换编辑器内容
            * */
            function replaceC(content) {
                ed.setContent(content);
            }

            /*
            * 字符转义
            * */
            function html2Escape(sHtml) {
                return sHtml.replace(/[<>&"]/g, function (c) {
                    return {'<': '&lt;', '>': '&gt;', '&': '&amp;', '"': '&quot;'}[c];
                });
            }


            /*
            * 新增插件
            * */
            ed.addButton('local', {
                title: '外部图片替换',
                image: url + '/icon/local.svg',
                onclick: function () {

                    const confirm = layer.confirm("是否要检索文章内所有外部图片？", function () {

                        //关闭弹出层
                        layer.close(confirm);


                        /*
                        * 弹出检测窗口
                        * */

                        let domain = location.host;

                        let replace = []; //替换列表
                        let link = [];

                        /*
                        * 获取所有图片
                        * */

                        let count = 1; //计数

                        /*
                        * 遍历所有本地图片
                        * */
                        $(ed.getBody()).find("img").each(function (index) {
                            let that = $(this).attr("src");

                            /*
                            * 如果没有图片
                            * */
                            if (!that) {
                                return
                            }

                            /*
                             * 如果没有http
                             * */
                            if (that.indexOf('http') < 0) {
                                return
                            }

                            /*
                            * 如果图片不包含本地域名
                            * 如果没有重复的包含
                            * */
                            if (that.indexOf(domain) < 0 && link.indexOf(that) < 0) {
                                link.push(that); //记录链接
                                replace.push(`<div style="border: 1px solid #f3f3f3;"><span>${count}</span>.&nbsp;<a target="_blank" href="${that}">${that}</a></div>`);
                                count++; //加1
                            }
                        });

                        /*
                        * 判断是否有外部图片
                        * */

                        if (replace.length == 0) {
                            layer.msg("没有找到外部图片");
                            return;
                        }

                        let content = ed.getContent(); //获取编辑器内容


                        /*
                        * 弹出
                        * */
                        let index = layer.open({
                            title: "共检测到外部图片" + (count - 1) + "张",
                            content: `<div style="width: 100%;height: 100%;">${replace.join('')}</div>`,
                            area: ['30vw', '50vh'],
                            maxmin: true,
                            closeBtn: 1,
                            btn: ['开始替换'],
                            yes: async function () {

                                let number = 1;
                                let index = null;
                                let flag = true;

                                /*
                                * 终止抓取
                                * */
                                function terminate(text = false) {
                                    /*
                                      * 替换编辑器的内容
                                      * */
                                    replaceC(content);

                                    try {
                                        flag = false;
                                        layer.close(index);
                                        if (!text) {
                                            layer.msg("成功替换" + (number - 1) + "张外部图片");
                                        } else {
                                            layer.msg(text);
                                        }
                                        $(document).off('keydown');
                                    } catch (e) {
                                        console.log(e);
                                    }
                                }

                                /*
                                * 监听关闭事件
                                * */
                                $(document).bind('keydown', 'esc', terminate);

                                /*
                                * 开始循环处理
                                * */
                                for (let i of link) {

                                    /*
                                    * 如果被终止
                                    * */
                                    if (!flag) {
                                        break;
                                    }

                                    index = layer.msg(`正在替换第${number}张，按Esc可停止替换...`, {
                                        icon: 16
                                        , shade: 0.1,
                                        time: 0
                                    });

                                    let code = await new Promise((resolve) => {

                                        /*
                                        * 请求服务器接口
                                        * */
                                        $.post('/?nicen_make_replace=1', {
                                            private: POST_KEY,
                                            img: i
                                        }, function (res) {

                                            if (res.code === -1) {
                                                layer.msg(res.result);
                                                resolve(0);
                                            } else if (res.code === 1) {
                                                /*
                                                  * 替换变量里的内容
                                                  * */
                                                content = content.replace(html2Escape(i), res.result);

                                            } else {
                                                number--; //未成功
                                                layer.msg(res.result);
                                            }

                                            resolve(1); //退出本次任务

                                        }, 'json')

                                    })


                                    /*
                                    * 如果失败
                                    * */
                                    if (!code) {
                                        terminate("成功替换" + (number - 1) + "张外部图片，记得保存哈，可在插件设置页面查看日志！");
                                        break;
                                    }

                                    number++; //数量增加
                                }

                                terminate("成功替换" + (number - 1) + "张外部图片，记得保存哈，可在插件设置页面查看日志！");
                            }
                        });


                    })

                }
            });
        },
        createControl: function (n, cm) {
            return null;
        },
    });


    tinymce.PluginManager.add('local', tinymce.plugins.local);
})();