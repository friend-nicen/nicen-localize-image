<?php


/*
 * 权限检测
 * */
function nicen_detect() {

	/*
	 * 判断是否有保存目录
	 * */
	$upload_root = $_SERVER['DOCUMENT_ROOT'] . nicen_config( 'nicen_plugin_path' ); //站点目录
	$upload      = nicen_config( 'nicen_plugin_path' ); //主题路径


	/*
	 * 上传目录是否存在
	 * */
	if ( ! file_exists( $upload_root ) ) {
		if ( ! mkdir( $upload_root ) ) {
			/*输出本地化日志*/
			echo '<script>jQuery(function (){alert("' . $upload . '目录不存在，nicen-localize-image本地化插件无法生效！");});</script>', 'after';
		}
	}


	if ( ! is_writable( $upload_root ) ) {
		/*输出本地化日志*/
		echo '<script>jQuery(function (){alert("' . $upload . '上传目录不可写，nicen-localize-image本地化插件无法生效！");});</script>', 'after';

		return;
	}


	/*
	 * 开启了自动本地化
	 * */
	if ( nicen_config( 'nicen_plugin_save' ) ) {
		/*
		 * 是否需要输出本地化日志
		 * */
		$info = get_option( 'nicen_plugin_save_result' );

		/*判断是否有本地化日志*/
		if ( $info ) {

			echo preg_replace( '/\s/', '', vsprintf( '
			<script>
			jQuery(function(){
				layer.alert("%s");   
			});
            </script>
			', [ $info ] ) );

			update_option( 'nicen_plugin_save_result', '' ); //清空日志
		}
	}


}

if ( strpos( $_SERVER['SCRIPT_NAME'] ?? "", '/post' ) ) {
	add_action( 'admin_head', 'nicen_detect' ); //加载前台资源文件
}


/*
 * 后台主题设置页面，外部文件加载
 * */
function nicen_admin_load_source() {

	wp_enqueue_script( 'vuejs', 'https://lf6-cdn-tos.bytecdntp.com/cdn/expire-1-M/vue/2.6.14/vue.min.js', false );

	wp_enqueue_script( 'antd', 'https://lf26-cdn-tos.bytecdntp.com/cdn/expire-1-M/ant-design-vue/1.7.8/antd.min.js', [ 'jquery' ] );
	wp_enqueue_script( 'Vcolorpicker', NICEN_URL . 'assets/colorpicker.js', array(), filemtime( NICEN_PATH . 'assets/colorpicker.js' ), true );

	wp_enqueue_style( 'antdcss', 'https://lf6-cdn-tos.bytecdntp.com/cdn/expire-1-M/ant-design-vue/1.7.8/antd.min.css' );

	wp_enqueue_style( 'admincss', NICEN_URL . 'assets/admin.css', array(), filemtime( NICEN_PATH . 'assets/admin.css' ) );
	wp_enqueue_script( 'adminjs', NICEN_URL . 'assets/admin.js', array(), filemtime( NICEN_PATH . 'assets/admin.js' ), true );
	wp_enqueue_script( 'loadjs', NICEN_URL . 'assets/load.js', array(), filemtime( NICEN_PATH . 'assets/load.js' ), true );

	/*
	 * 内联的js代码
	 * */
	wp_add_inline_script( "adminjs", vsprintf( 'const PLUGIN_CONFIG=%s;', [ json_encode( nicen_config() ) ] ), 'before' );


}


/*
 * 加载layer弹窗插件
 * */
function nicen_load_layer() {


	wp_enqueue_style( 'layercss', 'https://cdn.bootcdn.net/ajax/libs/layer/3.5.1/theme/default/layer.css', array() );
	wp_enqueue_script( 'layerjs', 'https://cdn.bootcdn.net/ajax/libs/layer/3.5.1/layer.min.js', array( 'jquery' ) );
	wp_enqueue_script( 'hotkey', 'https://lf26-cdn-tos.bytecdntp.com/cdn/expire-1-M/jquery.hotkeys/0.2.0/jquery.hotkeys.min.js', array( 'jquery' ) );

	/*
	 * 内联的js，输出接口密钥
	 * */
	wp_add_inline_script( "layerjs", preg_replace( '/\s/', '', vsprintf( '
			window.POST_KEY = "%s";'
		, [ nicen_config( 'nicen_plugin_private' ) ] ) ), 'before' );

}

/*
 * 编辑器后台加载样式和脚本
 * */
if ( strpos( $_SERVER['SCRIPT_NAME'] ?? "", '/post' ) !== false && nicen_config( 'nicen_plugin_editor' ) ) {
	add_action( 'admin_enqueue_scripts', 'nicen_load_layer' ); //加载前台资源文件
}

/*
 * 后台加载样式和脚本
 * */
if ( strpos( $_SERVER['QUERY_STRING'] ?? "", 'nicen_plugin' ) !== false ) {
	add_action( 'admin_enqueue_scripts', 'nicen_admin_load_source' ); //加载前台资源文件
}



