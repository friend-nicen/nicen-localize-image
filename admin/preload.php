<?php
/**
 * @author 友人a丶
 * @date 2022/8/27
 * 预加载数据
 */


/**
 * 获取定时任务状态
 * */
global $nicen_crontab_tab;
$nicen_crontab_tab = "处于开启状态，自动发布可以正常执行";

/**
 * 判断定时任务是否开启
 * */
if ( defined( 'DISABLE_WP_CRON' ) ) {
	if ( DISABLE_WP_CRON == true ) {
		$nicen_crontab_tab = "处于关闭状态，自动发布无法正常执行";
	} else {
		$nicen_crontab_tab = "处于开启状态，自动发布可以正常执行";
	}
}

const NICEN_VERSION = '1.4.6'; //插件版本
/**
 * 定时任务接口
 * */
$crontab = site_url() . '/wp-cron.php';


/**
 * 获取自动发布相关信息
 * */
function nicen_getAutoInfo() {

	$count_posts = wp_count_posts();
	if ( $count_posts ) {
		$draft_posts = $count_posts->draft;
	} else {
		$draft_posts = 0;
	}

	$last = get_option( 'nicen_last_auto_publish' ); //上一次的运行日志

	return "当前草稿总数：" . $draft_posts . '篇，上一次自动发布运行时间为：' . ( empty( $last ) ? '暂未运行' : $last );
}
