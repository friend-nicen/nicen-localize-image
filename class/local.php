<?php
/**
 * @author 友人a丶
 * @date 2022/8/27
 * 图片本地化封装类
 */

class Nicen_local {


	private static $self;
	private $is_sava_database; //本地化是否保存到数据库
	private $root_path; //从文件系统根目录开始的绝对路径
	private $site_path; //从站点根目录开始的绝对路径
	private $is_add_domain; //是否需要添加域名


	private function __construct() {
		$this->is_sava_database = nicen_make_config( 'nicen_make_plugin_local' );
		$this->site_path        = nicen_make_config( 'nicen_make_plugin_path' ); //站点目录
		$this->is_add_domain    = nicen_make_config( 'nicen_make_plugin_add_domain' ); //是否需要域名
		$this->root_path        = NICEN_ROOT . $this->site_path; //站点目录
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
	 * 获取网站状态码
	 * */
	function getHttpcode( $url ) {
		$response  = wp_remote_get( $url, [
			'sslverify' => false
		] );
		$http_code = wp_remote_retrieve_response_code( $response );

		return $http_code;
	}


	/**
	 * 获取图片内容
	 *
	 * @param $url string,图片的链接
	 * */
	function getImage( $url ) {

		$link = parse_url( $url );//解析链接

		/*
		 * 请求头模拟
		 * */
		$headers = [
			'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/106.0.0.0 Safari/537.36',
			'Referer'    => $link['scheme'] . '://' . $link['host']
		];

		/*
		 * 请求数据
		 * */
		$res = wp_remote_get( $url, [
			'headers'   => $headers,
			'sslverify' => false
		] );

		return wp_remote_retrieve_body( $res );
	}


	/**
	 * 保存图片到数据库
	 *
	 * @param $filename string 文件名
	 * */
	function saveAsData( $filename ) {


		/*
		 * 获取设置的文件保存目录
		 * */
		$document_root = $this->site_path; //站点目录

		/*
		 * 拼接插入的数据
		 * */
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


	/**
	 * 判断指定链接是否是白名单
	 *
	 * @param string $url
	 *
	 * @return boolean
	 * */
	public function is_white( $url ) {

		$white = explode( "\n", get_option( 'nicen_make_publish_white' ) ); //获取列表
		/*判断是否为空*/
		if ( empty( $white ) ) {
			return false;
		}

		$link = parse_url( $url ); //解析

		if ( in_array( $link['host'], $white ) ) {
			return true;
		} else {
			return false;
		}
	}


	/**
	 * 本地化图片
	 *
	 * @param $flag  false时return，true终止脚本直接输出
	 * */
	function localImage( $url, $flag = true ) {

		$upload_root = $this->root_path; //站点目录
		$upload      = $this->site_path; //主题路径
		$log         = Nicen_Log::getInstance(); //获取日志对象

		/**
		 * 指定的图片保存目录是否存在
		 * 不存在则创建
		 * 创建不成功直接退出
		 * */
		if ( ! file_exists( $upload_root ) ) {
			if ( ! mkdir( $upload_root ) ) {

				if ( $flag ) {
					exit( json_encode( $log->add( [
						'code'   => - 1,
						'result' => $upload . '指定的图片保存目录创建失败！'
					] ) ) );
				} else {
					return $log->add( [
						'code'   => - 1,
						'result' => $upload . '指定的图片保存目录创建失败！'
					] );
				}

			}
		}

		/*
		 * 判断目录是否可写
		 * */
		if ( ! is_writable( $upload_root ) ) {
			if ( $flag ) {
				exit( json_encode( $log->add( [
					'code'   => - 1,
					'result' => $upload . '指定的图片保存目录不可写，替换失败！'
				] ) ) );
			} else {
				return $log->add( [
					'code'   => - 1,
					'result' => $upload . '指定的图片保存目录不可写，替换失败！'
				] );
			}
		}


		/*
		 * 判断是否传递图片
		 * */
		if ( empty( $url ) ) {
			if ( $flag ) {
				exit( json_encode( $log->add( [
					'code'   => 0,
					'result' => '图片链接为空！'
				] ) ) );
			} else {
				return $log->add( [
					'code'   => 0,
					'result' => '图片链接为空！'
				] );
			}
		}


		/*
		 * 判断是否传递图片
		 * */
		if ( strpos( $url, 'http' ) === false ) {

			if ( $flag ) {
				exit( json_encode( $log->add( [
					'code'   => 0,
					'result' => '图片链接不规范！'
				] ) ) );
			} else {
				return $log->add( [
					'code'   => 0,
					'result' => '图片链接不规范！'
				] );
			}
		}


		$url = html_entity_decode( $url ); //反转义出真实链接

		/*
		 * 判断是否处于白名单
		 * */
		if ( $this->is_white( $url ) ) {
			if ( $flag ) {
				exit( json_encode( $log->add( [
					'code'   => 0,
					'result' => "白名单链接，无需进行本地化"
				] ) ) );
			} else {
				return $log->add( [
					'code'   => 0,
					'result' => "白名单链接，无需进行本地化"
				] );
			}
		}

		/*
		 * 获取保存的文件类型
		 * */
		$filetype = get_option( 'nicen_make_save_type' );

		if ( empty( $filetype ) ) {
			$filetype = 'png';
		}

		$filename = md5( $url ) . '.' . $filetype; //md5防止文件重复下载


		/*
		 * 判断链接是否需要添加域名
		 * */
		if ( $this->is_add_domain ) {
			$link = site_url() . $upload . '/' . $filename;
		} else {
			$link = $upload . '/' . $filename;
		}

		/*
		 * 判断文件是否已经存在
		 * 已经存在则直接返回数据
		 * */
		if ( file_exists( $upload_root . '/' . $filename ) ) {

			if ( $flag ) {
				exit( json_encode( $log->add( [
					'code'   => 1,
					'result' => $link
				] ) ) );
			} else {
				return $log->add( [
					'code'   => 1,
					'result' => $link
				] );
			}

		}


		/*
		 * 判断链接是否可以访问
		 * */
		if ( $this->getHttpcode( $url ) != 200 ) {
			if ( $flag ) {
				exit( json_encode( ( [
					'code'   => 0,
					'result' => $url . '图片链接无法访问！'
				] ) ) );
			} else {
				return $log->add( [
					'code'   => 0,
					'result' => $url . '图片链接无法访问！'
				] );
			}
		}


		/*
		 * 获取图片内容
		 * html反转义
		 * */
		$content = @$this->getImage( $url );

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

				if ( $this->is_sava_database ) {
					$this->saveAsData( $filename );
				}


				if ( $flag ) {
					exit( json_encode( $log->add( [
						'code'   => 1,
						'result' => $link
					] ) ) );
				} else {
					return $log->add( [
						'code'   => 1,
						'result' => $link
					] );
				}

			} else {

				if ( $flag ) {
					exit( json_encode( $log->add( [
						'code'   => 0,
						'result' => $url . '图片保存失败！'
					] ) ) );
				} else {
					return $log->add( [
						'code'   => 0,
						'result' => $url . '图片保存失败！'
					] );
				}

			}

		} else {

			if ( $flag ) {
				exit( json_encode( $log->add( [
					'code'   => 0,
					'result' => $url . '图片下载失败！'
				] ) ) );
			} else {
				return $log->add( [
					'code'   => 0,
					'result' => $url . '图片下载失败！'
				] );
			}
		}
	}
}

