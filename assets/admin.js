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

console.log(tab)


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
                localStorage.setItem('nicen_make_plugin_tab', res);
            },
            hasChange(res, events, field) {
                if (res) {
                    this.data[field] = 1;
                } else {
                    this.data[field] = 0;
                }
            }
        }
    });
});