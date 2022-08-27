<?php
/*
* @author 友人a丶
* @date ${date}
* 文章图片本地化
*/


/**
 * 保存文章时触发的钩子
 *
 * @param $post_id integer 文章ID
 * @param bool $flag 是否需要记录日志
 *
 * */
function nicen_make_when_save_post( $post_id, $flag = true ) {

	remove_action( 'edit_post', 'nicen_make_when_save_post' );

	$log=""; //保存日志

	/*判断是否启用了本地化图片的功能*/
	if ( nicen_make_config( 'nicen_make_plugin_save' ) || !$flag ) {

		//获取文章对象
		$post = get_post( $post_id );

		if ( empty( $post ) ) {
			return;
		}

		//匹配所有图片
		preg_match_all( '/<img(?:.*?)src="(.*?)"(?:.*?)\/>/', $post->post_content, $match );

		/*如果没有图片*/
		if ( empty( $match ) ) {
			return;
		}


		$images   = array_unique( $match[1] ); //去重
		$site_url = site_url(); //站点url

		/*
		 * 循环所有图片，判断是否需要本地化
		 * */

		$success = 0;
		$failed  = 0; //成功和失败的数量
		$content = $post->post_content;

		foreach ( $images as $value ) {

			/*
			 * 如果没有http
			 * 代表是相对路径
			 * */
			if ( strpos( $value, 'http' ) === false ) {
				continue;
			}

			/*
			* 如果图片不包含本地域名
			* 如果没有重复的包含
			 * 代表是外部图片需要进行本地化
			* */
			if ( strpos( $value, $site_url ) === false ) {

				$res = ( Nicen_local::getInstance() )->localImage( $value, false );//下载图片

				/*判断下载结果*/
				if ( $res['code'] ) {

					$content = str_replace( $value, $res['result'], $content );

					//更新文章
					wp_update_post( [
						'ID'           => $post_id,
						'post_content' => $content
					], false, false );

					$success ++; //加1
				} else {
					$failed ++; //加1
				}
			}

		}

		/*记录到日志*/
		if ( $success || $failed ) {
			$log = '上一次保存文章后，本地化图片成功' . $success . "张，失败" . $failed . '张！';
		}
	}

	/*
	 * 自动添加alt
	 * */
	if ( nicen_make_config( 'nicen_make_plugin_alt' ) ) {


		//获取文章对象
		$post = get_post( $post_id );
		/*文章是否存在*/
		if ( empty( $post ) ) {
			return;
		}
		//匹配所有图片
		preg_match_all( '/<img(?:.*?)\/>/', $post->post_content, $match );

		/*如果没有图片*/
		if ( empty( $match ) ) {
			return;
		}

		$success = 0; //成功和失败的数量
		$content = $post->post_content;

		$replace = nicen_make_config( 'nicen_make_plugin_alt_type' ) == 1 ? $post->post_title : nicen_make_getCategory( $post->ID );

		foreach ( $match[0] as $value ) {

			/*
			 * 如果没有alt
			 * 代表是相对路径
			 * */
			if ( strpos( $value, 'alt' ) === false ) {

				$content = str_replace( $value, str_replace( '<img', '<img alt="' . $replace . '"', $value ), $content );
				//更新文章
				wp_update_post( [
					'ID'           => $post_id,
					'post_content' => $content
				], false, false );

				$success ++; //加1
			}
		}


		/*记录到日志*/
		if ( $success ) {
			if ( empty( $log ) ) {
				$log = '上一次保存文章后，自动添加alt属性' . $success . '个！';
			} else {
				$log = $log . '自动添加alt属性' . $success . '个！';
			}
		}

	}

	/*
	 * 插入日志
	 * */
	if ( $flag ) {
		update_option( 'nicen_make_plugin_save_result', $log );
	} else {
		return $log;
	}

}


add_action( 'edit_post', 'nicen_make_when_save_post' );


