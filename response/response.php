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

	private $private;
	private static $self;

	private function __construct() {
		$this->private = get_option( "nicen_make_plugin_private" );
	}


	/**
	 * 获取单例
	 * */
	public static function getInstance() {
		/*如果实例不存在*/
		if ( ! self::$self ) {
			self::$self = new self();
		}

		return self::$self;
	}

	/**
	 * 验证接口权限
	 * */
	public function auth() {

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

	/*
	 * 添加一个间隔时间
	 * */
	public function add_schedules( $schedules ) {
		$schedules['nicen_crontab'] = array(
			'interval' => get_option( 'nicen_make_plugin_interval' ), //获取设置的间隔时间
			'display'  => '定时发布草稿文章'
		);

		return $schedules;
	}

	/**
	 * 接收响应
	 * */
	public function response() {

		/*
		 * 接手响应
		 * */

		try {

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
				delete_option( 'nicen_plugin_error_log' );
				var_dump( get_option( 'nicen_plugin_error_log' ) );
				exit( json_encode( [
					'code'   => 1,
					'result' => "清除成功！"
				] ) );
			}

			/**
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

				$condition = [
					'`post_type` = "post"', //指定文章类型
					'(`post_status` = "publish" or `post_status` = "draft")'  //指定草稿和已发布（爬出自动草稿）
				];

				/*
				 * ID范围
				 * */
				if ( ! empty( $json['start'] ) && ! empty( $json['end'] ) ) {
					$condition[] = '(`ID` >= ' . $json['start'] . ' and `ID` <= ' . $json['end'] . ')';
				}


				/*
				 * 时间范围
				 * */
				if ( ! empty( $json['range'] ) ) {
					$condition[] = '(`post_date` >= "' . $json['range'][0] . '" and `post_date` <= "' . $json['range'][1] . '")';
				}

				/*
				 * 分类范围
				 * */
				if ( ! empty( $json['category'] ) ) {
					$condition[] = '`ID` in (select DISTINCT `object_id` from `wp_term_relationships` where `term_taxonomy_id` in (' . join( ',', $json['category'] ) . '))';
				}

				$sql = 'select `ID` from `wp_posts` where ' . join( ' and ', $condition ) . ' order by `post_date`';

				$result = $wpdb->get_results( $sql );

				/*
				 * 判断本地化结果
				 * */
				if ( empty( $result ) ) {
					exit( json_encode( [
						'code'   => 0,
						'errMsg' => "没有符合条件的文章或草稿！"
					] ) );
				} else {
					exit( json_encode( [
						'code'   => 1,
						'errMsg' => "查询成功！",
						'data'   => $result
					] ) );
				}

			}


			/**
			 * 开始本地化
			 * */
			if ( isset( $_GET['nicen_make_local_batch'] ) && isset( $_GET['batch_id'] ) ) {

				$this->auth(); //权限验证
				$ID   = $_GET['batch_id']; //文章ID
				$post = get_post( $ID ); //获取文章
				$log  = nicen_make_when_save_post( $ID, false ); //开始本地化

				update_option( 'nicen_last_batch', $ID ); //记录本地化
				/*
				 * 返回结果
				 * */
				exit( json_encode( [
					'code'   => 1,
					'errMsg' => empty( $log ) ? "文章【" . $post->post_title . '】没有检测到外部图片' : "文章【" . $post->post_title . '】' . $log
				] ) );
			}


			/**
			 * 是否修改了定时任务的执行状态
			 * */
			if ( isset( $_POST['nicen_make_plugin_auto_publish'] ) ) {

				/*
				 * 对比
				 * */
				$list = [
					'nicen_make_plugin_order',
					'nicen_make_plugin_auto_publish',
					'nicen_make_plugin_interval',
					'nicen_make_plugin_publish_local',
					'nicen_make_publish_date'
				];

				/*
				 * 表单值是否有了变化
				 * */

				$hasChange = false; //变化

				foreach ( $list as $value ) {
					if ( nicen_make_config( $value ) != $_POST[ $value ] ) {
						$hasChange = true;
						break;
					}
				}


				/*
				 * 配置是否发生改变
				 * */
				if ( $hasChange ) {

					$current = $_POST['nicen_make_plugin_auto_publish']; //修改的状态


					/**
					 * 如果是开启
					 * */
					if ( $current ) {
						/*重新初始化钩子*/
						add_filter( 'cron_schedules', array( $this, 'add_schedules' ) ); //自定义间隔时间
						wp_clear_scheduled_hook( 'nicen_plugin_auto_publish' ); //清除任务
						wp_schedule_event( time(), 'nicen_crontab', 'nicen_plugin_auto_publish' );
					} else {
						update_option( 'nicen_last_auto_publish', "任务已关闭" );
						wp_clear_scheduled_hook( 'nicen_plugin_auto_publish' ); //清除任务
					}

				}


			}


			/**
			 * 图片压缩
			 * */
			if ( isset( $_GET['nicen_make_compress'] ) ) {
				$this->auth(); //权限验证

				$json = json_decode( file_get_contents( 'php://input' ), true );

				if ( isset( $json['file'] ) ) {
					exit( json_encode( ( Nicen_comress::getInstance() )->getCompress( $json['file'] ) ) );
				}
			}


			/**
			 * 加载目录
			 * */
			if ( isset( $_GET['nicen_make_files'] ) ) {
				$this->auth(); //权限验证

				$lists = ( Nicen_comress::getInstance() )->readDirs( '/wp-content/uploads' );

				$result = json_encode( [
					'code' => 1,
					'data' => $lists,
					'errMsg'=>"加载成功！"
				] );

				exit( $result );

			}


		} catch ( \Throwable $e ) {
			exit( json_encode( [
				'code'   => 0,
				'errMsg' => $e->getMessage()
			] ) );
		}

	}


}


( Nicen_response::getInstance() )->response(); //接收请求