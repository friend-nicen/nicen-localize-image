<?php
/**
 * Plugin Name: nicen-localize-image
 * Plugin URI:https://nicen.cn/2893.html
 * Description: 用于本地化文章的外部图片的插件，支持文章发布前通过编辑器插件本地化、文章发布时自动本地化、定时发布文章时自动本地化、已发布的文章批量本地化。
 * Version:1.4.2
 * Author: 友人a丶
 * Author URI: https://nicen.cn
 * Text Domain: nicen-localize-image
 * License: GPLv2 or later
 */

/**
 * 定义全局命名空间
 * */

namespace nicen\local\images;


define( 'nicen_local_image_path', plugin_dir_path( __FILE__ ) ); //插件目录
define( 'nicen_local_image_url', plugin_dir_url( __FILE__ ) ); //插件URL

//this is the root of website's path. call $_SERVER['DOCUMENT_ROOT'],I think it's safe;
//Think's

/*文件系统根目录*/
define( 'nicen_local_image_root', $_SERVER['DOCUMENT_ROOT'] );

/*站点根目录*/
define( 'nicen_local_image_site_root', str_replace( $_SERVER['DOCUMENT_ROOT'], "", WP_CONTENT_DIR ) );

/**
 * 是否需要校准时区
 * */
if ( get_option( "nicen_make_plugin_adjust" ) ) {
	date_default_timezone_set( get_option( 'timezone_string' ) ); //设置时区
}


include_once nicen_local_image_path . '/admin/preload.php'; //加载插件配置
include_once nicen_local_image_path . '/config.php'; //加载插件配置
include_once nicen_local_image_path . '/admin/install.php'; //安装时触发


register_activation_hook( __FILE__, "nicen_make_install" );//初始化插件
register_deactivation_hook( __FILE__, 'nicen_make_end' ); //卸载插件

/*导入类库*/
include_once nicen_local_image_path . '/class/log.php'; //错误日志封装类

include_once nicen_local_image_path . '/class/local.php'; //图片本地化封装类
include_once nicen_local_image_path . '/class/crontab.php'; //定时任务封装类
include_once nicen_local_image_path . '/class/compress.php'; //图片压缩封装类

/*导入模块*/
include_once nicen_local_image_path . '/admin/common.php'; //公共变量和方法
include_once nicen_local_image_path . '/admin/when-post.php'; //文章保存时触发的钩子
include_once nicen_local_image_path . '/response/response.php'; //接口响应

/**
 * 只在后台才触发
 * */
if ( is_admin() ) {
	include_once nicen_local_image_path . '/admin/load.php'; //加载后台插件资源
	include_once nicen_local_image_path . '/admin/form.php'; //加载后台设置表单
	include_once nicen_local_image_path . '/admin/setting.php';//渲染表单
	include_once nicen_local_image_path . '/admin/initialize.php'; //初始化插件功能
}


