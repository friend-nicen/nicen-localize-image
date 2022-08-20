/*
* @author 友人a丶
* @date 2022-08-12
* 
* */

/*
* 获取缓存的tab
* */
let tab = localStorage.getItem('tab');
tab = (!tab) ? 'document_theme_section' : tab;

/*
* 初始化Vue
* */
jQuery(function () {


    /*
    * 判断是否在设置页面
    * */

    Vue.use(antd);
    Vue.use(vcolorpicker);


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
                data: PLUGIN_CONFIG,
                activeKey: tab,
                loading:false
            };
        },
        methods: {
            save() {
                this.loading=true;
                this.$refs['submit'].$el.submit();
            },
            change(res) {
                localStorage.setItem('tab', res);
            },
            hasChange(res, events, field) {
                if (res) {
                    this.data[field] = 1;
                } else {
                    this.data[field] = 0;
                }
            },
            /*
            * 提交链接到百度
            * */
            postLink() {
                let that=this;
                load.confirm("确定提交站点所有URL到百度站长平台吗？", () => {
                    load.loading('正在提交');
                    axios.get('/?submit=1&private='+that.data.document_private)
                        .then((res) => {
                            message.info(res.data.result);
                        }).catch((e) => {
                        message.error(e);
                    }).finally(() => {
                        load.loaded();
                    })
                })
            },
            /*
              * 生成站点地图
              * */
            sitemap() {
                let that=this;
                load.confirm("确定生成TXT站点地图？", () => {
                    load.loading('正在生成');
                    axios.get('/?sitemap=1&private='+that.data.document_private)
                        .then((res) => {
                            message.info(res.data.result);
                        }).catch((e) => {
                        message.error(e);
                    }).finally(() => {
                        load.loaded();
                    })
                })
            },
            /*
            * 查看站点地图
            * */
            lookSitemap(){
                window.open('/sitemap.txt','_blank');
            }
        }
    });
});