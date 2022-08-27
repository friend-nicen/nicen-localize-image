<?php

/*
 * 公共数据和方法
 * */

global $nicen_make_CONFIGS; //声明全局变量

$nicen_make_CONFIGS = []; //保存所有插件配置

/*
 * 遍历整个配置
 * */
foreach ( nicen_make_CONFIG as $key => $value ) {
	$nicen_make_CONFIGS[ $key ] = get_option( $key );
}

/*
 * 返回指定配置
 * */
function nicen_make_config( $key = '' ) {
	global $nicen_make_CONFIGS;
	if ( empty( $key ) ) {
		return $nicen_make_CONFIGS;
	} else {
		return $nicen_make_CONFIGS[ $key ];
	}
}


/**
 * 获取文章分类
 * */
function nicen_make_getCategory( $id ) {
	$cat = get_the_category( $id );
	if ( $cat ) {
		return $cat[0]->name;
	} else {
		return "暂无分类";
	}
}


