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
		$this->root_path        = nicen_local_image_root . $this->site_path; //站点目录
		$this->add_thumd        = nicen_make_config( 'nicen_make_plugin_add_thumb' );//生成缩略图
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

		/**
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


		/**
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


		/**
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

		/**
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

		/**
		 * 获取保存的文件类型
		 * */
		$url_md5 = md5( $url ); //md5图片链接


		/**
		 * 判断文件是否已经存在
		 * 已经存在则直接返回数据
		 * */
		$file_search = glob( $upload_root . '/' . $url_md5 . '.*' );

		if ( ! empty( $file_search ) ) {
			/**
			 * 判断链接是否需要添加域名
			 * */
			$link = $this->getLink( $file_search[0] ); //获取链接

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


		/**
		 * 判断链接是否可以访问
		 * */
		if ( $this->getImage( $url, 1 ) != 200 ) {
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


		/**
		 * 获取图片内容
		 * html反转义
		 * */
		$content = @$this->getImage( $url );

		/**
		 * 如果读取成功
		 * */
		if ( $content ) {

			$tmp = $upload_root . '/' . $url_md5; //临时文件
			/**
			 * 写入文件
			 * */
			if ( file_put_contents( $tmp, $content, LOCK_EX ) ) {


				$filename = $tmp . "." . nicen_plugin_getType( $tmp );
				rename( $tmp, $filename ); //重命名文件
				$link = $this->getLink( $filename ); //获取链接

				/**
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

		/* 移除左右特殊字符 */
		foreach ( $white as $key => $host ) {
			$white[ $key ] = trim( $host );
		}

		$link = parse_url( $url ); //解析

		if ( in_array( $link['host'], $white ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @param $filename
	 * 获取文件链接
	 */
	function getLink( $filename ) {
		$filename = basename( $filename ); //文件名
		/**
		 * 判断链接是否需要添加域名
		 * */
		if ( $this->is_add_domain ) {
			$link = site_url() . $this->site_path . '/' . $filename;
		} else {
			$link = $this->site_path . '/' . $filename;
		}

		return $link;
	}

	/**
	 * 获取图片内容
	 *
	 * @param $url string,图片的链接
	 * */
	function getImage( $url, $option = 0, $self_referer = null ) {

		$referer = nicen_make_config( 'nicen_make_plugin_referer' ); //读取自定义referer


		/**
		 * 请求头模拟
		 * */
		$headers = [
			"Accept-Encoding" => "gzip, deflate",
			'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/106.0.0.0 Safari/537.36',
			'Referer'         => $self_referer ?: ( empty( $referer ) ? $this->getReferer( $url ) : $referer )
		];


		/**
		 * 请求数据
		 * */
		$args = [
			'headers'   => $headers,
			'sslverify' => false,
			'timeout'   => 120,
		];

		if ( $option === 1 ) {
			$res = wp_remote_head( $url, $args );
		} else {
			$res = wp_remote_get( $url, $args );
		}


		if ( ! is_array( $res ) ) {
			$this->error = $res->get_error_message();
		}

		$code = wp_remote_retrieve_response_code( $res );


		/**
		 * 是否是状态码判断
		 * */
		if ( in_array( $code, [ 301, 302 ] ) ) {
			$newUrl = wp_remote_retrieve_header( $res, 'Location' );

			return $this->getImage( $newUrl, $option, "  " ); // 递归调用
		} else {
			if ( $option ) {

				/* 二次确认 */
				if ( $option === 1 && $code !== 200 ) {
					$code = $this->getImage( $url, 2 ); // 递归调用
				}

				return $code;
			} else {
				return wp_remote_retrieve_body( $res );
			}
		}


	}

	/**
	 * @param $imageUrl
	 * 获取默认的referer规则
	 *
	 * @return mixed|string
	 */
	function getReferer( $imageUrl ) {

		/* 定义平台的图片域名或关键词 */
		$platforms = [
			'163'        => [ '126.net', '163.com' ],
			'微信公众号' => [ 'mmbiz.qpic.cn' ],
			'QQ'         => [ 'qpic.cn' ],
			'搜狐'       => [ 'sohu.com' ],
			'今日头条'   => [ 'toutiao.com' ],
			'微博'       => [ 'weibo.com' ],
			'知乎'       => [ 'zhihu.com' ],
			'抖音'       => [ 'douyin.com' ],
			'小红书'     => [ 'xiaohongshu.com' ],
			'B站'        => [ 'bilibili.com' ]
		];

		/* 检测图片属于哪个平台 */
		$platform = '未知平台';
		foreach ( $platforms as $plat => $keywords ) {
			foreach ( $keywords as $keyword ) {
				if ( strpos( $imageUrl, $keyword ) !== false ) {
					$platform = $plat;
					break;
				}
			}
			if ( $platform !== '未知平台' ) {
				break;
			}
		}

		/* 根据平台返回对应的Referer */
		switch ( $platform ) {
			case '163':
				$referer = 'https://news.163.com'; // 假设网易新闻首页
				break;
			case '微信公众号':
				$referer = 'https://mp.weixin.qq.com'; // 微信公众号平台
				break;
			case 'QQ':
				$referer = 'https://qzone.qq.com'; // QQ空间
				break;
			case '搜狐':
				$referer = 'https://www.sohu.com'; // 搜狐首页
				break;
			case '今日头条':
				$referer = 'https://www.toutiao.com'; // 今日头条首页
				break;
			case '微博':
				$referer = 'https://weibo.com'; // 微博首页
				break;
			case '知乎':
				$referer = 'https://www.zhihu.com'; // 知乎首页
				break;
			case '抖音':
				$referer = 'https://www.douyin.com'; // 抖音首页
				break;
			case '小红书':
				$referer = 'https://www.xiaohongshu.com'; // 小红书首页
				break;
			case 'B站':
				$referer = 'https://www.bilibili.com'; // B站首页
				break;
			default:
				$link    = parse_url( $imageUrl );//解析链接
				$referer = $link['scheme'] . '://' . $link['host'];
				break;
		}

		return $referer;
	}

	/**
	 * 保存图片到数据库
	 *
	 * @param $filename string 文件名
	 * */
	function saveAsData( $filename ) {

		global $nicen_Post_ID; //正在操作的文章

		/**
		 * 获取设置的文件保存目录
		 * */
		$link = $this->getLink( $filename ); //访问链接

		/**
		 * 拼接插入的数据
		 * */
		$attachment = array(
			'guid'           => $link,
			'post_mime_type' => wp_get_image_mime( $filename ),
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
			'post_content'   => '',
			'post_status'    => 'inherit'
		);

		// Insert the attachment.
		if ( isset( $nicen_Post_ID ) ) {

			$attach_id = wp_insert_attachment( $attachment, $filename, $nicen_Post_ID );

			/**
			 * 是否作为特色图片
			 * */

			if ( nicen_make_config( 'nicen_make_save_as_thumb' ) ) {
				set_post_thumbnail( $nicen_Post_ID, $attach_id ); //设置特色图片
			}

		} else {
			$attach_id = wp_insert_attachment( $attachment, $filename );
		}

		/**
		 * 更新访问的URL
		 * */
		include_once ABSPATH . WPINC . '/pluggable.php';
		include_once( ABSPATH . 'wp-admin/includes/image.php' );

		/* 保存到数据库不生成缩略图 */
		if ( empty( $this->add_thumd ) ) {
			/* 不生成缩略图 */
			add_filter( 'intermediate_image_sizes_advanced', function ( $sizes ) {
				return array();
			} );
		}

		// 更新元数据
		$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
		wp_update_attachment_metadata( $attach_id, $attach_data );
	}

}

