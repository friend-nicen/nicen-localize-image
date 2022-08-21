<?php
/**
Plugin Name: nicen-localize-image
Plugin URI:https://nicen.cn/2893.html
Description: 用于本地化文章的外部图片，启用之后会给新增两个功能：编辑器增加在前端本地化外部图片的插件和文章保存时在后端自动本地化外部图片的功能（区别在于一个是发布前本地化和发布后本地化）
Version: 1.0.0
Author: 友人a丶
Author URI: https://nicen.cn
Text Domain: nicen-localize-image
License: GPLv2 or later
 */

define('NICEN_PATH',plugin_dir_path(__FILE__));
define('NICEN_URL',plugin_dir_url(__FILE__));
date_default_timezone_set('PRC');

include_once NICEN_PATH . '/config.php'; //加载插件配置
include_once NICEN_PATH . '/admin/install.php'; //安装时触发
register_activation_hook(__FILE__ , "nicen_install" );//初始化插件
include_once NICEN_PATH . '/admin/common.php'; //公告变量和方法
include_once NICEN_PATH . '/response/response.php'; //公告变量和方法

/*
 * 只在后台才触发
 * */

include_once NICEN_PATH . '/admin/load.php'; //加载插件后台资源
include_once NICEN_PATH. '/admin/form.php'; //加载表单
include_once NICEN_PATH . '/admin/setting.php';//渲染表单
include_once NICEN_PATH . '/admin/initialize.php'; //初始化插件功能
include_once NICEN_PATH . '/admin/when-post.php'; //公告变量和方法



/*
 * 错误信息调试
	add_action('activated_plugin','save_error');
	function save_error(){
		update_option('install_error',ob_get_contents());
	}
	 echo get_option('install_error');
	 update_option('install_error',"");
*/

