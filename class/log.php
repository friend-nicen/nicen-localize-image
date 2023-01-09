<?php


/**
 * @author 友人a丶
 * @date 2022/8/27
 * 错误日志类
 */
class Nicen_Log {

	private static $self;
	private $option = 'nicen_plugin_error_log';
	private $logs;

	private function __construct() {
		add_option( $this->option );//初始化选项
		$this->logs = get_option( $this->option );
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
	 * 新增一条日志
	 *
	 * @param log，操作之后的结果
	 *
	 * @return array
	 */
	public function add( $log ) {

		$now = date( "Y-m-d H:i:s", time() );

		/*
		 * 判断结果
		 * */
		if ( $log['code'] ) {
			$this->logs .= $now . '，' . '本地化成功，' . $log['result'] . "\n";
		} else {
			$this->logs .= $now . '，' . '本地化失败，' . $log['result'] . "\n";
		}

		update_option( $this->option, $this->logs );

		return $log;
	}


	/**
	 * @return false|mixed|null
	 */
	public function get_logs() {
		return $this->logs;
	}

	/**
	 * 清空日志
	 * */
	public function clear() {
		update_option( $this->option, "" );
	}

}