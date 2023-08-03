<?php
/**
 * @author 友人a丶
 * @date 2022/8/27
 * 定时任务的tab
 */


class Nicen_crontab {

	private static $self;
	private $interval;//间隔时间
	private $type;  //发布类型，随机还是顺序
	private $localImage;//判断是否需要本地化
	private $date = []; //开始日期
	private $time = []; //时间范围
	private $syncDatetime; //是否需要同步当前时间
	private $number; //定时发布的文章数量
	private $status; //文章状态


	/**
	 * 初始化定时任务的功能
	 * */
	private function __construct() {

		/*初始化*/
		$this->interval     = get_option( 'nicen_make_plugin_interval' );
		$this->type         = get_option( 'nicen_make_plugin_order' );
		$this->localImage   = get_option( 'nicen_make_plugin_publish_local' );
		$this->syncDatetime = get_option( 'nicen_make_publish_date' );
		$this->number       = get_option( 'nicen_make_plugin_publish_num' );
		$this->status       = get_option( 'nicen_make_publish_type' );


		/* 定时任务是否允许 */
		$this->running = get_option( 'nicen_make_plugin_auto_publish' );


		/**
		 * 开始和结束日期
		 * */
		$this->date = [
			get_option( 'nicen_make_publish_date_start' ),
			get_option( 'nicen_make_publish_date_end' )
		];

		/**
		 * 开始和结束时间
		 * */
		$this->time = [
			get_option( 'nicen_make_publish_time_start' ),
			get_option( 'nicen_make_publish_time_end' )
		];

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
	 * 准备任务
	 * */
	public function start() {

		/*初始化钩子*/
		add_filter( 'cron_schedules', array( $this, 'add_schedules' ) ); //自定义间隔时间

		/* 并且定时任务是开着的 */
		/* 钩子没有注册成功 */
		if ( $this->running && ! wp_get_scheduled_event( 'nicen_plugin_auto_publish' ) ) {
			wp_schedule_event( time(), 'nicen_crontab', 'nicen_plugin_auto_publish' );
		}


		add_action( 'nicen_plugin_auto_publish', array( $this, 'publish' ) );//自定义发布任务
	}


	/**
	 * 添加一个间隔时间
	 * */
	function add_schedules( $schedules ) {

		$schedules['nicen_crontab'] = [
			'interval' => intval( $this->interval ), //获取设置的间隔时间
			'display'  => '定时发布草稿文章'
		];

		return $schedules;
	}


	/**
	 * 发布文章
	 * */
	public function publish() {

		$time = time(); //当前秒数
		$date = strtotime( date( "Y-m-d", $time ) ); //当日0点
		$ymd  = date( "Y-m-d", $time );
		$now  = date( "Y-m-d H:i:s", time() );


		/**
		 * 当天日期小于最小日期
		 * */
		if ( ! empty( $this->date[0] ) ) {
			if ( strtotime( $this->date[0] ) > $date ) {

				$this->log( "当前日期小于设置的最小日期，任务终止，" . $now );

				return;
			}
		}

		/**
		 * 当天日期大于最大日期
		 * */
		if ( ! empty( $this->date[1] ) ) {
			if ( strtotime( $this->date[1] ) < $date ) {
				$this->log( "当前日期超过设置的最晚日期，任务终止，" . $now );

				return;
			}
		}


		/**
		 * 当天时间小于最小时间
		 * */
		if ( ! empty( $this->time[0] ) ) {
			if ( strtotime( $ymd . " " . $this->time[0] ) > $time ) {
				$this->log( "当前时间小于设置的最小时间，任务终止，" . $now );

				return;
			}
		}

		/**
		 * 当天时间大于最小时间
		 * */

		if ( ! empty( $this->time[1] ) ) {
			if ( strtotime( $ymd . " " . $this->time[1] ) < $time ) {
				$this->log( "当前时间超过设置的最大时间，任务终止，" . $now );

				return;
			}
		}


		/**
		 * 定义文章指针
		 * */
		$log = date( "Y-m-d H:i:s", time() ) . "任务被触发，";

		query_posts( [
			'posts_per_page' => $this->number,
			'orderby'        => $this->type,
			'post_status'    => [ '', 'draft', 'pending', 'future' ][ $this->status ],
			'post_type'      => 'post',
			'order'          => 'ASC'
		] );

		$count = 0;
		$title = '';

		while ( have_posts() ) {
			the_post();
			kses_remove_filters();
			$title = get_the_title();


			/**
			 * 判断是否同步发布时间
			 * */
			if ( $this->syncDatetime ) {
				wp_update_post( [
						'ID'          => get_the_ID(),
						'post_status' => 'publish',
						'post_date'   => date( "Y-m-d H:i:s", time() )
					]
				);
			} else {
				wp_update_post( [
						'ID'          => get_the_ID(),
						'post_status' => 'publish',
					]
				);
			}


			/**
			 * 是否需要本地化
			 * */
			if ( $this->localImage ) {
				nicen_make_when_save_post( get_the_ID(), false );
			}

			kses_init_filters();


			$count ++;
		}

		wp_reset_query();

		if ( ! $count ) {
			$this->log( $log . "无可以发布文章，下次运行时间：" . date( "Y-m-d H:i:s", time() + $this->interval ) );

			return;
		}

		$datetime = $this->syncDatetime ? date( "Y-m-d H:i:s", time() ) : "最后一次编辑时间";

		$log .= "发布文章" . $count . "篇，【{$title}】,文章发布时间为【{$datetime}】,下次运行时间：" . date( "Y-m-d H:i:s", time() + $this->interval );
		$this->log( $log );
	}

	/**
	 * 记录运行时间，以及下一次运行时间
	 * */
	public function log( $log ) {
		update_option( 'nicen_last_auto_publish', $log );
	}

}


( Nicen_crontab::getInstance() )->start(); //开始
