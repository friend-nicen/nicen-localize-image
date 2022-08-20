<?php

/*
 * 公共数据和方法
 * */

$NICEN_CONFIGS = [];

/*
 * 遍历整个配置
 * */
foreach ( NICEN_CONFIG as $key => $value ) {
	$NICEN_CONFIGS[ $key ] = get_option( $key );
}

/*
 * 返回指定配置
 * */
function nicen_config( $key = '' ) {
	global $NICEN_CONFIGS;
	if ( empty( $key ) ) {
		return $NICEN_CONFIGS;
	} else {
		return $NICEN_CONFIGS[ $key ];
	}
}


/*
 * 获取网站状态码
 * */
function nicen_httpcode( $url ) {
	$ch      = curl_init();
	$timeout = 3;
	curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );//禁止 cURL 验证对等证书
	curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );//是否检测服务器的域名与证书上的是否一致
	curl_setopt( $ch, CURLOPT_HEADER, 1 );
	curl_setopt( $ch, CURLOPT_NOBODY, true );
	curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_exec( $ch );
	$httpcode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
	curl_close( $ch );

	return $httpcode;
}


/*
 * 保存图片到数据库
 * */
function nicen_saveAsData( $filename ) {


	/*
	 * 获取文件类型
	 * */


	$document_root = nicen_config( 'nicen_plugin_path' ); //站点目录

	$attachment = array(
		'guid'           => $document_root . '/' . basename( $filename ),
		'post_mime_type' => 'image/png',
		'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
		'post_content'   => '',
		'post_status'    => 'inherit'
	);

	// Insert the attachment.
	$attach_id = wp_insert_attachment( $attachment, $filename );

	/*
	 * 更新访问的URL
	 * */
	update_post_meta( $attach_id, '_wp_attached_file', $document_root . '/' . $filename );
}


/*
 * 获取分类
 * */
function nicen_getCategory( $id ) {

	$cat = get_the_category( $id );
	if ( $cat ) {
		return $cat[0]->name;
	} else {
		return "暂无分类";
	}
}


/*
 * 本地化图片
 * */
function nicen_local_image( $url, $flag = true ) {

	$upload_root = $_SERVER['DOCUMENT_ROOT'] . nicen_config( 'nicen_plugin_path' ); //站点目录
	$upload      = nicen_config( 'nicen_plugin_path' ); //主题路径

	/*
	 * 上传目录是否存在
	 * */
	if ( ! file_exists( $upload_root ) ) {
		if ( ! mkdir( $upload_root ) ) {

			if ( $flag ) {
				exit( json_encode( [
					'code'   => - 1,
					'result' => $upload . '上传目录不存在！'
				] ) );
			} else {
				return [
					'code'   => - 1,
					'result' => $upload . '上传目录不存在！'
				];
			}

		}
	}

	/*
	 * 判断目录是否可写
	 * */
	if ( ! is_writable( $upload_root ) ) {
		if ( $flag ) {
			exit( json_encode( [
				'code'   => - 1,
				'result' => $upload . '上传目录不可写，替换失败！'
			] ) );
		} else {
			return [
				'code'   => - 1,
				'result' => $upload . '上传目录不可写，替换失败！'
			];
		}
	}


	/*
	 * 判断是否传递图片
	 * */
	if ( empty( $url ) ) {
		if ( $flag ) {
			exit( json_encode( [
				'code'   => 0,
				'result' => '图片链接为空！'
			] ) );
		} else {
			return [
				'code'   => 0,
				'result' => '图片链接为空！'
			];
		}
	}


	/*
	 * 判断是否传递图片
	 * */
	if ( strpos( $url, 'http' ) === false ) {

		if ( $flag ) {
			exit( json_encode( [
				'code'   => 0,
				'result' => '图片链接不规范！'
			] ) );
		} else {
			return [
				'code'   => 0,
				'result' => '图片链接不规范！'
			];
		}
	}


	$url      = html_entity_decode( $url );
	$filename = md5( $url ) . '.png'; //md5防止文件重复下载


	/*
	 * 判断文件是否已经存在
	 * */
	if ( file_exists( $upload_root . '/' . $filename ) ) {

		if ( $flag ) {
			exit( json_encode( [
				'code'   => 1,
				'result' => $upload . '/' . $filename
			] ) );
		} else {
			return [
				'code'   => 1,
				'result' => $upload . '/' . $filename
			];
		}

	}


	/*
	 * 获取图片内容
	 * html反转义
	 * */
	$content = @file_get_contents( $url );

	/*
	 * 如果读取成功
	 * */
	if ( $content ) {

		/*
		 * 写入文件
		 * */
		if ( file_put_contents( $upload_root . '/' . $filename, $content, LOCK_EX ) ) {

			/*
			 * 是否需要保存到数据库
			 * */

			if ( nicen_config( 'nicen_plugin_local' ) ) {
				nicen_saveAsData( $filename );
			}


			if ( $flag ) {
				exit( json_encode( [
					'code'   => 1,
					'result' => $upload . '/' . $filename
				] ) );
			} else {
				return [
					'code'   => 1,
					'result' => $upload . '/' . $filename
				];
			}

		} else {
			if ( $flag ) {
				exit( json_encode( [
					'code'   => 0,
					'result' => '图片保存失败！'
				] ) );
			} else {
				return [
					'code'   => 0,
					'result' => '图片保存失败！'
				];
			}
		}

	}


}