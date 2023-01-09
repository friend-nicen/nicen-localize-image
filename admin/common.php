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


/**
 * 获取所有标签和分类
 * */
function nicen_plugin_getAllCat() {

	$cat = [];

	/*
	 * 遍历目录
	 * */
	$terms = get_terms( 'category', 'orderby=name&hide_empty=0' );

	if ( count( $terms ) > 0 ) {
		foreach ( $terms as $term ) {
			$cat[] = [
				'label' => '分类：' . $term->name,
				'value' => $term->term_id
			];
		}
	}

	return $cat;
}


/*
 * 获取图片类型
 * 1 = GIF，2 = JPG，3 = PNG，4 = SWF，5 = PSD，6 = BMP，7 = TIFF(intel byte order)，8 = TIFF(motorola byte order)，9 = JPC，10 = JP2，11 = JPX，12 = JB2，13 = SWC，14 = IFF，15 = WBMP，16 = XBM
 * */
function nicen_plugin_getType( $path ) {

	$info = getimagesize( $path );

	switch ( $info[2] ) {
		case 1 :
			return 'gif';
		case 2 :
			return 'jpeg';
		case 3 :
			return 'png';
		case 15 :
			return 'wbmp';
		default :
			return 'png';
	}
}