<?php

/*
 * @author 友人a丶
 * @date 2022-08-14
 * 主题前台设置
 * 主题后台设置
 *
 * 所有表单本质都可以看做类似json的配置结构
 * */


/*
 * 已定义的表单组件
 * nicen_form_input
 * nicen_form_number
 * nicen_form_password
 * nicen_form_textarea
 * nicen_form_select  @option 数组或者回调函数代表选项
 * nicen_form_switch
 * nicen_form_color
 * */

/*
 * 后台所有表单
 *
 * 初始化函数在admin/setting.php
 *
 * document_menu_register ,初始化菜单
 * document_config_register，表单初始化
 *
 * 初始化函数在admin/admin.php
 *
 * do_settings_sections_user ,初始化分节
 * do_settings_fields_user，初始化所有输入组件
 *
 * */
const PLUGIN_NICEN = [
    /*菜单设置*/
    [
        "id" => "nicen_plugin",//主题后台设置字段
        "menu_title" => '图片本地化',
        'page_title' => '图片本地化',
        'callback' => 'nicen_setting_load',
        'capablity' => 'edit_themes',
        /*分节*/
        "sections" => [
            [
                "id" => "nicen_plugin_section",
                'title' => '基础设置',
                'fields' => [
	                [
		                'id' => 'text_info',
		                'title' => '功能设置说明',
		                'callback' => 'destination_form_text',
		                'args'=>[
							'info'=>'插件提供两种本地化外部图片的功能:<br/>【<span style="color: red;">编辑器本地化插件</span>】启用后会在文章编辑器上方显示一个小图标，点击之后可以自动检测并本地化所有外部图片；<br/>【<span style="color: red;">发布时自动本地化</span>】启用后会在文章发布时自动本地化所有外部图片；<br/>推荐使用【编辑器本地化插件】在发布前进行本地化，当图片数量过多或者文件太大【发布时自动本地化】可能会导致请求卡死。'
		                ]
	                ],
	                [
		                'id' => 'nicen_plugin_editor',
		                'title' => '启用编辑器本地化图片插件',
		                'callback' => 'nicen_form_switch',
	                ],
	                [
		                'id' => 'nicen_plugin_save',
		                'title' => '启用文章发布时自动本地化',
		                'callback' => 'nicen_form_switch',
	                ],
	                [
		                'id' => 'nicen_plugin_local',
		                'title' => '图片本地化时保存到数据库',
		                'callback' => 'nicen_form_switch',
	                ],
	                [
		                'id' => 'nicen_plugin_alt',
		                'title' => '发布时图片自动添加alt属性',
		                'callback' => 'nicen_form_switch',
		                'args'=>[
							'tip'=>'默认添加文章标题作为alt属性，只会给没有alt的标签添加'
		                ]
	                ],
	                [
		                'id' => 'nicen_plugin_alt_type',
		                'title' => '添加alt属性的方式',
		                'callback' => 'nicen_form_select',
		                'args'=>[
			                'options' => [
				                [
					                'label' => '添加文章标题',
					                'value' => '1'
				                ],
				                [
					                'label' => '添加文章分类',
					                'value' => '2'
				                ]
			                ]
		                ]
	                ],
	                [
		                'id' => 'nicen_plugin_path',
		                'title' => '本地化图片时的保存路径',
		                'callback' => 'nicen_form_input',
	                ],
	                [
		                'id' => 'nicen_plugin_private',
		                'title' => '接口密钥',
		                'callback' => 'nicen_form_password',
		                'args'=>[
							'tip'=>'防止接口被恶意提交'
		                ]
	                ],
                ]
            ],
        ]
    ]
];


/*
 * 主题所有配置
 * 键=>默认值
 *
 * 初始化在
 * wp-content/themes/destination/include/functions/theme.php
 *
 * 的documents，reload函数
 * */

const NICEN_CONFIG = [
    "nicen_plugin_local" => '1', //本地化时保存到数据库
	'nicen_plugin_private'=>'88888888', //接口密钥
	'nicen_plugin_editor'=>'1', //
	'nicen_plugin_save'=>'1',
    'nicen_plugin_save_result'=>'',
	'nicen_plugin_alt'=>'1',
	'nicen_plugin_alt_type'=>'1',
	'nicen_plugin_path'=>'/wp-content/uploads/replace'
  ];