/*
* @author 友人a丶
* @date 2022-08-12
* 
* */

/*
* 获取缓存的tab
* */
let tab = localStorage.getItem('nicen_make_plugin_tab');
tab = (!tab) ? 'nicen_make_plugin_section' : tab;


/*
* 初始化Vue
* */
jQuery(function () {

    /*
    * 判断是否在设置页面
    * */

    Vue.use(antd); //加载antd
    Vue.use(vcolorpicker); //加载颜色选择

    /*
    * 需要处理的数据
    * */
    let vm = new Vue({
        el: "#VueApp",
        data() {
            /*
            * 数据对象
            * */
            return {
                data: PLUGIN_CONFIG, //已设置的表单
                activeKey: tab, //激活的yab
                loading: false, //是否正在处理
                version: '',
                donate: [],
                batch: {
                    start: null,
                    end: null,
                    list: [],
                    loading: false,
                    flag: false
                }
            };
        },
        methods: {
            /*
            * 保存设置
            * */
            save() {
                this.loading = true;
                this.$refs['submit'].$el.submit();
            },
            /*
            * tab改变
            * */
            change(res) {
                localStorage.setItem('nicen_make_plugin_tab', res);
            },
            /*
            * 开关改变
            * */
            hasChange(res, events, field) {
                if (res) {
                    this.data[field] = 1;
                } else {
                    this.data[field] = 0;
                }
            },
            /*
            * 清空日志
            * */
            clearLogs() {
                let that = this;
                load.confirm("确定清空所有本地化日志吗？", () => {
                    load.loading('正在提交');
                    axios.get('/?nicen_make_clear_log=1&private=' + that.data.nicen_make_plugin_private)
                        .then((res) => {
                            load.info(res.data.result);
                            setTimeout(() => {
                                location.reload();
                            }, 500)
                        }).catch((e) => {
                        load.error(e);
                    }).finally(() => {
                        load.loaded();
                    })
                })

            },
            /*
            * 批量本地化
            * */
            async getBatch() {

                let that = this;
                /*
                * 判断运行状态
                * */
                if (that.batch.flag) {
                    that.batch.flag = false;
                    load.error("已取消运行...");
                    return;
                }else{
                    that.batch.flag = true;
                }


                let batch = '';

                if (!that.batch.start || !that.batch.end) {
                    batch = "所有";
                } else {
                    batch = `ID${that.batch.start}~${that.batch.end}`;
                    if (that.batch.start >= that.batch.end) {
                        load.error("开始ID不能大于或等于结束ID");
                        return;
                    }
                }

                let code = false; //操作结果

                /*
                * 弹出确认框
                * 获取文章数量和列表
                * */
                code = await new Promise(resolve => {
                    load.confirm(`确定要本地化的${batch}文章内所有的外部图片吗？`, () => {
                        load.loading('正在请求');
                        axios.post(`/?nicen_make_batch=1&private=${that.data.nicen_make_plugin_private}`, that.batch)
                            .then((res) => {

                                /*
                                * 判断请求结果
                                * */
                                if (res.data.code) {
                                    that.batch.list = res.data.data;
                                    resolve(true)
                                } else {
                                    load.error(res.data.errMsg);
                                    resolve(false)
                                }

                            }).catch((e) => {
                            load.error(e);
                            resolve(false)
                        }).finally(() => {
                            load.loaded();
                        })
                    }, () => {
                        resolve(false)
                    });
                });

                /*
                * 获取文章的结果
                * */
                if (code) {

                    code = await new Promise(resolve => {
                        load.confirm(`符合条件的文章共有${that.batch.list.length}篇，是否继续？`, () => {
                            resolve(true)
                        }, () => {
                            resolve(false)
                        })
                    })

                    console.log(that.batch.list)
                    /*
                    * 判断选择的结果
                    * */
                    if (code) {
                        for (let i of that.batch.list) {
                            /*
                            * 如果已经被中断
                            * */
                            if (!that.batch.flag) {
                                load.loaded();
                                return;
                            }
                            await that.localImage(i.ID);
                        }
                    }

                }
            },
            /*
            * 提交本地化图片
            * */
            localImage(id) {

                let that = this;

                return new Promise((resolve) => {
                    load.loading('正在本地化文章：'.id);
                    axios.get(`/?nicen_make_local_batch=1&private=${that.data.nicen_make_plugin_private}&batch_id=${id}`)
                        .then((res) => {
                            load.success(res.data.errMsg);
                        }).catch((e) => {
                        load.error(e);
                    }).finally(() => {
                        resolve(true);
                        load.loaded();
                    })
                });


            }
        },
        created() {
            let that = this;
            axios.get("https://weixin.nicen.cn/api/update")
                .then((res) => {
                    if (res.data.code) {
                        that.version = res.data.data.latest;
                        that.donate = res.data.data.donate;
                    }
                })
        }
    });
})
;