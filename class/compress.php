<?php
/*
* @author 友人a丶
* @date 2022/8/28
* 图片压缩
*/


class Nicen_comress {

	private static $self;

	private function __construct() {

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
	 * 读取所有图片
	 *
	 * @param string $path 指定读取路径
	 * */
	public function readDirs( $path ) {

		$root = $_SERVER['DOCUMENT_ROOT'];

		$list       = [];
		$dir_handle = opendir( $root . $path );

		if ( ! $dir_handle ) {
			return [];
		}


		while ( ( $file = readdir( $dir_handle ) ) !== false ) {

			if ( $file != "." && $file != ".." ) {

				$abs = $root . $path . '/' . $file; //绝对路径


				if ( ! is_dir( $abs ) ) {

					/*
					 * 过滤掉非图片文件
					 * */
					$finfo    = finfo_open( FILEINFO_MIME );
					$mimetype = finfo_file( $finfo, $abs );
					finfo_close( $finfo );

					if ( strpos( $mimetype, 'image' ) !== false ) {

						$title = $file . "（" . round( filesize( $abs ) / 1024, 2 ) . "kb）";
						$key   = $path . '/' . $file;

						$list[] = [
							"title" => base64_encode( $title ),
							"key"   => base64_encode( $key )
						];
					}


				} else {

					/*读取文件*/
					$children = $this->readDirs( $path . '/' . $file );

					if ( ! empty( $children ) ) {
						$list[] = [
							"title"    => base64_encode( $file ),
							"key"      => base64_encode( $path . '/' . $file ),
							"children" => $children
						];
					}

				}

			}
		}

		closedir( $dir_handle );

		return $list;

	}

	/**
	 * 获取图片内容
	 *
	 * @param $url string,图片的链接
	 * */
	function get( $url ) {

		try {
			$headers = [
				'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/106.0.0.0 Safari/537.36',
			];

			/*
			 * 请求数据
			 * */
			$res = wp_remote_get( $url, [
				'headers'   => $headers,
				'sslverify' => false
			] );

			return wp_remote_retrieve_body( $res );

		} catch ( \Throwable $e ) {
			return false;
		}
	}


	/**
	 * 获取图片内容
	 *
	 * @param $url string,图片的链接
	 * */
	function post( $file ) {

		try {

			$post_data = [
				'files' => new \CURLFile( $file )
			];

			$Http = new WP_Http_Curl;

			/*
			 * 修改请求配置
			 * */
			$args = array(
				'method'      => 'POST',
				'sslverify'   => false,
				'body'        => $post_data,
				'httpversion' => '1.1'
			);

			$res = $Http->request( 'http://api.resmush.it/ws.php', $args );

			if ( is_wp_error( $res ) ) {
				$errors = $res->get_error_messages();
				return $errors;
			}

			return wp_remote_retrieve_body( $res );

		} catch ( \Throwable $e ) {
			return $e->getMessage();
		}
	}


	/**
	 * 获取压缩后的图片
	 *
	 * @param string $file
	 * */
	public function getCompress( $file ) {

		$root = $_SERVER['DOCUMENT_ROOT'];
		$abs  = $root . $file; //绝度路径

		/*
		 * 判断文件是否存在
		 * */
		if ( ! file_exists( $abs ) ) {
			return [
				'code'   => 0,
				'errMsg' => '文件不存在，压缩失败！'
			];
		}


		if ( ! is_writable( $abs ) ) {
			return [
				'code'   => 0,
				'errMsg' => '文件不可写，压缩失败！'
			];
		}

		$res    = $this->post( $abs );
		$result = json_decode( $this->post( $abs ), true ); //请求压缩

		if ( isset( $result['dest'] ) ) {

			$data = $this->get( $result['dest'] );

			if ( ! $data ) {
				return [
					'code'   => 0,
					'errMsg' => '文件文件读取失败，接口超时！'
				];
			} else {
				file_put_contents( $abs, $data, LOCK_EX ); //写入文件

				return [
					'code'   => 1,
					'errMsg' => '压缩成功，压缩前' . round( $result['src_size'] / 1024, 2 ) . 'kb，压缩后' . round( $result['dest_size'] / 1024, 2 ) . 'kb，压缩率' . $result['percent'] . '%！'
				];
			}


		} else {
			return [
				'code'   => 0,
				'errMsg' => $res
			];
		}

	}

}


