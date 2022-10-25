<?php
/*
* @author 友人a丶
* @date ${date}
* 插件安装时写入默认配置
*/


/*
 * 初始化默认配置
 * */
function nicen_make_install() {
	foreach ( nicen_make_CONFIG as $key => $value ) {
		add_option( $key, $value ); //添加配置参数，和默认值
	}
}

nicen_make_install(); //插件自动设置

/*
 * 关闭插件时注销任务
 * */
function nicen_make_end() {
	wp_clear_scheduled_hook( 'nicen_plugin_auto_publish' ); //清除任务
}





