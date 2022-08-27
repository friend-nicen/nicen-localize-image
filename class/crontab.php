<?php
/*
* @author 友人a丶
* @date 2022/8/27
* 定时任务的tab
*/


class Nicen_crontab {

	private $interval; //间隔时间
	private $type; //发布类型，随机还是顺序
	private $localImage; //判断是否需要本地化
	private static $self;


	/*
	 * 初始化定时任务的功能
	 * */
	private function __construct() {

		/*初始化*/
		$this->interval   = get_option( 'nicen_make_plugin_interval' );
		$this->type       = get_option( 'nicen_make_plugin_order' );
		$this->localImage = get_option( 'nicen_make_plugin_publish_local' );
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

	/*
	 * 准备任务
	 * */
	public function start() {
		/*初始化钩子*/
		add_filter( 'cron_schedules', array( $this, 'add_schedules' ) ); //自定义间隔时间
		add_action( 'nicen_plugin_auto_publish', array( $this, 'publish' ) );//自定义发布任务
	}


	/*
	 * 添加一个间隔时间
	 * */
	function add_schedules( $schedules ) {
		$schedules['nicen_crontab'] = array(
			'interval' => $this->interval, //获取设置的间隔时间
			'display'  => '定时发布草稿文章'
		);
		return $schedules;
	}


	/*
	 * 发布文章
	 * */
	public function publish() {

		file_put_contents($_SERVER['DOCUMENT_ROOT'].'/log.txt','666666666666660',LOCK_EX);

		/*
		 * 定义文章指针
		 * */
		$log = date( "Y-m-d H:i:s", time() ) . "任务被触发，";

		query_posts( [
			'posts_per_page' => 1,
			'orderby'        => $this->type,
			'post_status'    => 'draft',
			'post_type'      => 'post',
			'order'          => 'ASC'
		] );

		$count = 0;

		while ( have_posts() ) {
			the_post();
			kses_remove_filters();

			/*
			 * 判断是否同步发布时间
			 * */
			if(get_option('nicen_make_publish_lish')){
				wp_update_post( [
						'ID'          => get_the_ID(),
						'post_status' => 'publish',
						'post_date' => date( "Y-m-d H:i:s", time() )
					]
				);
			}else{
				wp_update_post( [
						'ID'          => get_the_ID(),
						'post_status' => 'publish',
					]
				);
			}


			/*
			 * 是否需要本地化
			 * */
			if ( $this->localImage ) {
				nicen_make_when_save_post( get_the_ID(), false );
			}

			kses_init_filters();

			$count ++;
		}

		wp_reset_query();

		$log .= "发布文章" . $count . "篇，下次运行时间：" . date( "Y-m-d H:i:s", time() + $this->interval );
		$this->log( $log );
	}

	/*
	 * 记录运行时间，以及下一次运行时间
	 * */
	public function log( $log ) {
		update_option( 'nicen_last_auto_publish', $log );
	}

}


( Nicen_crontab::getInstance() )->start(); //开始
