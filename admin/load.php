<?php


/**
 * 权限检测
 * */
function nicen_make_detect() {

	/**
	 * 判断是否有保存目录
	 * */
	$upload_root = nicen_local_image_root . nicen_make_config( 'nicen_make_plugin_path' ); //站点目录
	$upload      = nicen_make_config( 'nicen_make_plugin_path' ); //主题路径


	/**
	 * 上传目录是否存在
	 * */
	if ( ! file_exists( $upload_root ) ) {
		if ( ! mkdir( $upload_root ) ) {
			/*输出本地化日志*/
			echo '<script>jQuery(function (){alert("' . esc_js( $upload ) . '目录不存在，nicen_make-localize-image本地化插件无法生效！");});</script>', 'after';
		}
	}


	if ( ! is_writable( $upload_root ) ) {
		/*输出本地化日志*/
		echo '<script>jQuery(function (){alert("' . esc_js( $upload ) . '上传目录不可写，nicen_make-localize-image本地化插件无法生效！");});</script>', 'after';

		return;
	}


	/**
	 * 开启了自动本地化
	 * */
	if ( nicen_make_config( 'nicen_make_plugin_save' ) ) {
		/**
		 * 是否需要输出本地化日志
		 * */
		$info = get_option( 'nicen_make_plugin_save_result' );

		/*判断是否有本地化日志*/
		if ( $info ) {

			echo preg_replace( '/\s/', '', vsprintf( '
			<script>
			jQuery(function(){
                jQuery("#message").append("<p>%s可在插件设置页面查看日志！</p>");
			});
            </script>
			', [ esc_js( $info ) ] ) );

			update_option( 'nicen_make_plugin_save_result', '' ); //清空日志
		}
	}


}

/**
 * 如果是文章编辑页面，则加载插件
 * */
if ( strpos( $_SERVER['SCRIPT_NAME'] ?? "", '/post' ) ) {
	add_action( 'admin_head', 'nicen_make_detect' ); //加载前台资源文件
}


/**
 * 后台主题设置页面，外部文件加载
 * */
function nicen_make_admin_load_source() {

	wp_enqueue_script( 'vuejs', nicen_local_image_url . 'assets/vue.min.js', [ 'jquery' ] );

	// 使用WordPress内置的moment.js
	wp_enqueue_script( 'moments', 'wp-includes/js/moment.min.js', [], false );
	wp_enqueue_script( 'base64', nicen_local_image_url . 'assets/base64.min.js' );

	wp_enqueue_script( 'antd', nicen_local_image_url . 'assets/antd.min.js', [ 'jquery', 'vuejs' ] );
	wp_enqueue_script( 'Vcolorpicker', nicen_local_image_url . 'assets/colorpicker.js', array(), filemtime( nicen_local_image_path . 'assets/colorpicker.js' ), true );

	wp_enqueue_style( 'antdcss', nicen_local_image_url . 'assets/antd.min.css' );

	wp_enqueue_style( 'admincss', nicen_local_image_url . 'assets/admin.css', array(), filemtime( nicen_local_image_path . 'assets/admin.css' ) );
	wp_enqueue_script( 'adminjs', nicen_local_image_url . 'assets/admin.js', array(), filemtime( nicen_local_image_path . 'assets/admin.js' ), true );
	wp_enqueue_script( 'loadjs', nicen_local_image_url . 'assets/load.js', array(), filemtime( nicen_local_image_path . 'assets/load.js' ), true );

	wp_enqueue_script( 'axios', nicen_local_image_url . 'assets/axios.min.js' );

	/**
	 * 内联的js代码
	 * */
	wp_add_inline_script( "adminjs", vsprintf( "
	const PLUGIN_CONFIG=%s;
	const NICEN_VERSION='%s';", [
		json_encode( nicen_make_config() ),
		esc_js( NICEN_VERSION )
	] ), 'before' );


}


/**
 * 加载layer弹窗插件
 * */
function nicen_make_load_layer() {


	wp_enqueue_style( 'layercss', nicen_local_image_url . 'assets/style.min.css', array() );
	wp_enqueue_script( 'layerjs', nicen_local_image_url . 'assets/layer.js', array( 'jquery' ) );
	// 使用WordPress内置的jquery-hotkeys
	wp_enqueue_script( 'jquery-hotkeys' );

	/**
	 * 内联的js，输出接口密钥
	 * */
	wp_add_inline_script( "layerjs", preg_replace( '/\s/', '', vsprintf( '
			window.POST_KEY = "%s";
			window.SYNC_NUMBER = %s;'

		, [
			esc_html( nicen_make_config( 'nicen_make_plugin_private' ) ),
			esc_html( nicen_make_config( 'nicen_make_sync_number' ) ),
		] ) ), 'before' );

}

/**
 * 编辑器后台加载样式和脚本
 * */
if ( strpos( $_SERVER['SCRIPT_NAME'] ?? "", '/post' ) !== false && nicen_make_config( 'nicen_make_plugin_editor' ) ) {
	add_action( 'admin_enqueue_scripts', 'nicen_make_load_layer' ); //加载前台资源文件
}

/**
 * 后台加载样式和脚本
 * */
if ( strpos( $_SERVER['QUERY_STRING'] ?? "", 'nicen_make_plugin' ) !== false ) {
	add_action( 'admin_enqueue_scripts', 'nicen_make_admin_load_source' ); //加载前台资源文件
}



