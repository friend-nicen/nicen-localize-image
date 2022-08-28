<?php


/*
 * 权限检测
 * */
function nicen_make_detect() {

	/*
	 * 判断是否有保存目录
	 * */
	$upload_root = $_SERVER['DOCUMENT_ROOT'] . nicen_make_config( 'nicen_make_plugin_path' ); //站点目录
	$upload      = nicen_make_config( 'nicen_make_plugin_path' ); //主题路径


	/*
	 * 上传目录是否存在
	 * */
	if ( ! file_exists( $upload_root ) ) {
		if ( ! mkdir( $upload_root ) ) {
			/*输出本地化日志*/
			echo '<script>jQuery(function (){alert("' . $upload . '目录不存在，nicen_make-localize-image本地化插件无法生效！");});</script>', 'after';
		}
	}


	if ( ! is_writable( $upload_root ) ) {
		/*输出本地化日志*/
		echo '<script>jQuery(function (){alert("' . $upload . '上传目录不可写，nicen_make-localize-image本地化插件无法生效！");});</script>', 'after';

		return;
	}


	/*
	 * 开启了自动本地化
	 * */
	if ( nicen_make_config( 'nicen_make_plugin_save' ) ) {
		/*
		 * 是否需要输出本地化日志
		 * */
		$info = get_option( 'nicen_make_plugin_save_result' );

		/*判断是否有本地化日志*/
		if ( $info ) {

			echo preg_replace( '/\s/', '', vsprintf( '
			<script>
			jQuery(function(){
				layer.alert("%s可在插件设置页面查看日志！");   
			});
            </script>
			', [ $info ] ) );

			update_option( 'nicen_make_plugin_save_result', '' ); //清空日志
		}
	}


}

/*
 * 如果是文章编辑页面，则加载插件
 * */
if ( strpos( $_SERVER['SCRIPT_NAME'] ?? "", '/post' ) ) {
	add_action( 'admin_head', 'nicen_make_detect' ); //加载前台资源文件
}


/*
 * 后台主题设置页面，外部文件加载
 * */
function nicen_make_admin_load_source() {

	wp_enqueue_script( 'vuejs', 'https://lf6-cdn-tos.bytecdntp.com/cdn/expire-1-M/vue/2.6.14/vue.min.js', false );

	wp_enqueue_script( 'moments', 'https://lf3-cdn-tos.bytecdntp.com/cdn/expire-1-M/moment.js/2.29.1/moment.min.js' );
	wp_enqueue_script( 'base64', nicen_make_URL . 'assets/base64.min.js' );

	wp_enqueue_script( 'antd', 'https://lf26-cdn-tos.bytecdntp.com/cdn/expire-1-M/ant-design-vue/1.7.8/antd.min.js', [ 'jquery' ] );
	wp_enqueue_script( 'Vcolorpicker', nicen_make_URL . 'assets/colorpicker.js', array(), filemtime( nicen_make_PATH . 'assets/colorpicker.js' ), true );

	wp_enqueue_style( 'antdcss', 'https://lf6-cdn-tos.bytecdntp.com/cdn/expire-1-M/ant-design-vue/1.7.8/antd.min.css' );

	wp_enqueue_style( 'admincss', nicen_make_URL . 'assets/admin.css', array(), filemtime( nicen_make_PATH . 'assets/admin.css' ) );
	wp_enqueue_script( 'adminjs', nicen_make_URL . 'assets/admin.js', array(), filemtime( nicen_make_PATH . 'assets/admin.js' ), true );
	wp_enqueue_script( 'loadjs', nicen_make_URL . 'assets/load.js', array(), filemtime( nicen_make_PATH . 'assets/load.js' ), true );

	wp_enqueue_script( 'axios', 'https://lf26-cdn-tos.bytecdntp.com/cdn/expire-1-M/axios/0.26.0/axios.min.js' );

	/*
	 * 内联的js代码
	 * */
	wp_add_inline_script( "adminjs", vsprintf( "const PLUGIN_CONFIG=%s;const NICEN_VERSION='%s';", [
		json_encode( nicen_make_config() ),
		NICEN_VERSION
	] ), 'before' );


}


/*
 * 加载layer弹窗插件
 * */
function nicen_make_load_layer() {


	wp_enqueue_style( 'layercss', 'https://cdn.bootcdn.net/ajax/libs/layer/3.5.1/theme/default/layer.css', array() );
	wp_enqueue_script( 'layerjs', 'https://cdn.bootcdn.net/ajax/libs/layer/3.5.1/layer.min.js', array( 'jquery' ) );
	wp_enqueue_script( 'hotkey', 'https://lf26-cdn-tos.bytecdntp.com/cdn/expire-1-M/jquery.hotkeys/0.2.0/jquery.hotkeys.min.js', array( 'jquery' ) );

	/*
	 * 内联的js，输出接口密钥
	 * */
	wp_add_inline_script( "layerjs", preg_replace( '/\s/', '', vsprintf( '
			window.POST_KEY = "%s";'
		, [ nicen_make_config( 'nicen_make_plugin_private' ) ] ) ), 'before' );

}

/*
 * 编辑器后台加载样式和脚本
 * */
if ( strpos( $_SERVER['SCRIPT_NAME'] ?? "", '/post' ) !== false && nicen_make_config( 'nicen_make_plugin_editor' ) ) {
	add_action( 'admin_enqueue_scripts', 'nicen_make_load_layer' ); //加载前台资源文件
}

/*
 * 后台加载样式和脚本
 * */
if ( strpos( $_SERVER['QUERY_STRING'] ?? "", 'nicen_make_plugin' ) !== false ) {
	add_action( 'admin_enqueue_scripts', 'nicen_make_admin_load_source' ); //加载前台资源文件
}



