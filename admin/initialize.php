<?php
/*
* @author 友人a丶
* @date ${date}
* 说明
*/


/*
 * 插件初始化
 * */
function nicen_make_initialize() {

	/*
	 * 是否启用经典编辑器
	 * */

	//禁止Gutenberg编辑器
	add_filter( 'use_block_editor_for_post', '__return_false' );
	//禁止新版小工具
	add_filter( 'gutenberg_use_widgets_block_editor', '__return_false' );
	add_filter( 'use_widgets_block_editor', '__return_false' );

	//判断用户是否有编辑文章和页面的权限
	if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
		return;
	}

	//判断用户是否使用可视化编辑器
	if ( get_user_option( 'rich_editing' ) == 'true' ) {
		/*tinymce加载时引入插件的js*/
		add_filter( 'mce_external_plugins', function ( $plugin_array ) {
			/*
			 * 引入插件的js
			 * */
			$plugin_array['local'] = nicen_local_image_url . 'tinymcc/local.js';/*指定要加载的插件*/

			return $plugin_array;
		} );

		/*过滤 TinyMCE 按钮的第一行列表（Visual 选项卡）,在可视编辑器中注册一个按钮*/
		add_filter( 'mce_buttons', function ( $buttons ) {
			$buttons[] = 'local';
			return $buttons;
		} );
	}
}


/*
 * 判断是否开启编辑器本地化插件
 * */
if ( nicen_make_config( 'nicen_make_plugin_editor' ) ) {
	add_action( 'admin_init', 'nicen_make_initialize' );//启用经典编辑器，加载编辑器插件
}

