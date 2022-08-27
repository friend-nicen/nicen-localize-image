<?php
/*
* @author 友人a丶
* @date ${date}
* 外部图片下载
*/


/*
 * 链接提交
 * */

class Nicen_response {

	public $private;

	public function __construct() {
		$this->private = get_option( "nicen_make_plugin_private" );
	}

	/**
	 * 验证接口权限
	 * */
	function auth() {

		if ( empty( $_GET['private'] ) && empty( $_POST['private'] ) ) {
			exit( json_encode( [
				'code'   => 0,
				'result' => "密钥为空"
			] ) );
		}

		if ( ( $_GET['private'] ?? "" ) != $this->private && ( $_POST['private'] ?? "" ) != $this->private ) {
			exit( json_encode( [
				'code'   => 0,
				'result' => "密钥有误"
			] ) );
		}
	}

	/**
	 * 接收响应
	 * */
	public function response() {

		/*
		 * 接手响应
		 * */
		if ( isset( $_GET['nicen_make_replace'] ) ) {
			$this->auth(); //权限验证
			( Nicen_local::getInstance() )->localImage( $_POST['img'] );
		}

		/*
		 * 本地化图片的请求
		 * */
		if ( isset( $_GET['nicen_make_replace'] ) ) {
			$this->auth(); //权限验证
			( Nicen_local::getInstance() )->localImage( $_POST['img'] );
		}

		/*
		 * 清空日志
		 * */
		if ( isset( $_GET['nicen_make_clear_log'] ) ) {
			$this->auth(); //权限验证
			update_option( 'nicen_plugin_error_log', "" );
			exit( json_encode( [
				'code'   => 1,
				'result' => "清除成功！"
			] ) );
		}

		/*
		 * 批量本地化
		 * */
		if ( isset( $_GET['nicen_make_batch'] ) ) {


			$this->auth(); //权限验证
			global $wpdb; //数据库操作

			$json = json_decode( file_get_contents( 'php://input' ), true );

			/*
			 * 判断参数完整性
			 * 有一个没填那就获取所有文章
			 * */

			if ( ! $json['start'] || ! $json['end'] ) {
				$sql = 'select `ID` from `wp_posts` where (`post_status` = "publish" or `post_status` = "draft") and `post_type` = "post" order by `post_date`';
			} else {
				$sql = 'select `ID` from `wp_posts` where (`post_status` = "publish" or `post_status` = "draft") and `post_type` = "post" and (`ID` > ' . $json['start'] . ' and `ID` < ' . $json['end'] . ') order by `post_date`';
			}


			$result = $wpdb->get_results( $sql );

			/*
			 * 判断本地化结果
			 * */
			if ( empty( $result ) ) {
				exit( json_encode( [
					'code'   => 1,
					'errmsg' => "没有符合条件的文章或草稿！"
				] ) );
			} else {
				exit( json_encode( [
					'code'   => 1,
					'errmsg' => "查询成功！",
					'data'   => $result
				] ) );
			}

		}


		/*
		 * 开始本地化
		 * */
		if ( isset( $_GET['nicen_make_local_batch'] ) && isset( $_GET['batch_id'] ) ) {

			$this->auth(); //权限验证
			$ID   = $_GET['batch_id']; //文章ID
			$post = get_post( $ID ); //获取文章
			$log  = nicen_make_when_save_post( $ID, false ); //开始本地化

			/*
			 * 返回结果
			 * */
			exit( json_encode( [
				'code'   => 1,
				'errMsg' => empty( $log ) ? "文章【" . $post->post_title . '】没有检测到外部图片' : "文章【" . $post->post_title . '】' . $log
			] ) );
		}

	}


}


( new Nicen_response() )->response(); //接收请求