<?php

/*
 * @author 友人a丶
 * @date 2022-08-14
 * 主题前台设置
 * 主题后台设置
 *
 * 所有表单本质都可以看做类似json的配置结构
 * */


/**
 * 已定义的表单组件
 * nicen_make_form_input
 * nicen_make_form_number
 * nicen_make_form_password
 * nicen_make_form_textarea
 * nicen_make_form_select  @option 数组或者回调函数代表选项
 * nicen_make_form_switch
 * nicen_make_form_color
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
define( 'PLUGIN_nicen_make', [
	/*菜单设置*/
	[
		"id"         => "nicen_make_plugin",//主题后台设置字段
		"menu_title" => '图片本地化',
		'page_title' => '图片本地化',
		'callback'   => 'nicen_make_setting_load',
		'capablity'  => 'edit_themes',
		/*分节*/
		"sections"   => [
			[
				"id"     => "nicen_make_plugin_section",
				'title'  => '基础设置',
				'fields' => [
					[
						'id'       => 'text_info',
						'title'    => '功能设置说明',
						'callback' => 'nicen_make_plugin_form_text',
						'args'     => [
							'info' => '插件提供两种本地化外部图片的功能:<br/>【<span style="color: red;">编辑器本地化插件</span>】启用后会在文章编辑器上方显示一个小图标，点击之后可以自动检测并本地化所有外部图片；<br/>【<span style="color: red;">发布时自动本地化</span>】启用后会在文章发布时自动本地化所有外部图片；<br/>推荐使用【编辑器本地化插件】在发布前进行本地化，当图片数量过多或者文件太大【发布时自动本地化】可能会导致请求卡死。'
						]
					],
					[
						'id'       => 'nicen_make_plugin_editor',
						'title'    => '启用编辑器本地化图片插件',
						'callback' => 'nicen_make_form_switch',
					],
					[
						'id'       => 'nicen_make_plugin_save',
						'title'    => '启用文章发布时自动本地化',
						'callback' => 'nicen_make_form_switch',
					],
					[
						'id'       => 'nicen_make_plugin_local',
						'title'    => '图片本地化时保存到数据库',
						'callback' => 'nicen_make_form_switch',
					],
					[
						'id'       => 'nicen_make_plugin_add_domain',
						'title'    => '本地化的图片链接添加域名',
						'callback' => 'nicen_make_form_switch',
						'args'     => [
							'tip' => '不开启是默认为/wp/image.png这种格式，开启后链接变为http://domain.com/wp/image.png格式'
						]
					],
					[
						'id'       => 'nicen_make_plugin_alt',
						'title'    => '发布时图片自动添加alt属性',
						'callback' => 'nicen_make_form_switch',
						'args'     => [
							'tip' => '默认添加文章标题作为alt属性，只会给没有alt的标签添加'
						]
					],
					[
						'id'       => 'nicen_make_plugin_alt_type',
						'title'    => '添加alt属性的方式',
						'callback' => 'nicen_make_form_select',
						'args'     => [
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
						'id'       => 'nicen_make_plugin_path',
						'title'    => '本地化图片时的保存路径',
						'callback' => 'nicen_make_form_input',
					],
					[
						'id'       => 'nicen_make_save_type',
						'title'    => '保存后的文件类型',
						'callback' => 'nicen_make_form_input',
						'args'=>[
							'tip'     => '保存后的文件类型，默认是png格式！'
						]
					]
				]
			],
			[
				"id"     => "nicen_make_plugin_crontab",
				'title'  => '定时发布',
				'fields' => [
					[
						'id'       => 'text_info',
						'title'    => '定时发布功能说明',
						'callback' => 'nicen_make_plugin_form_text',
						'args'     => [
							'info' => '默认按照文章添加的顺序定时将未发布的草稿进行发布，基于wp自带的定时任务。<br/>【<span style="color: red;">Wp自带的定时任务</span>】是在网站有用户访问时才会去执行，假设任务是16:00执行，但是这个时间段没有人访问网站，一直到17:00才有人访问，那么任务17点才会被执行，于是文章发布时间就比预定的时间晚了一小时；所以建议通过宝塔或者其他工具设置定时访问wp的任务接口，用以保证定时任务执行的准时性<br/><br/>您的wordpress触发定时任务接口为：<a href="' . $crontab . '" target="_blank">' . $crontab . '</a>，每访问一次都会重新检测定时任务是否需要执行，插件日志页面可查看运行日志<br /><br/>【<span style="color: red;">日志</span>】' . getAutoInfo()
						]
					],
					[
						'id'       => 'nicen_make_plugin_auto_publish',
						'title'    => '开启自动发布文章',
						'callback' => 'nicen_make_form_switch',
					],
					[
						'id'       => 'nicen_make_plugin_interval',
						'title'    => '每间隔多少秒发布一次',
						'callback' => 'nicen_make_form_number',
					],
					[
						'id'       => 'nicen_make_publish_date',
						'title'    => '是否同步发布时间',
						'callback' => 'nicen_make_form_select',
						'args'     => [
							'options' => [
								[
									'label' => '保持默认的发布时间',
									'value' => '0'
								],
								[
									'label' => '修改为自动发布的时间',
									'value' => '1'
								]
							],
						]
					],
					[
						'id'       => 'nicen_make_plugin_order',
						'title'    => '选择文章发表顺序',
						'callback' => 'nicen_make_form_select',
						'args'     => [
							'options' => [
								[
									'label' => '随机发布',
									'value' => 'rand'
								],
								[
									'label' => '按创建时间',
									'value' => 'ID'
								]
							],
						]
					],
					[
						'id'       => 'nicen_make_plugin_publish_local',
						'title'    => '发布时是否本地化图片',
						'callback' => 'nicen_make_form_select',
						'args'     => [
							'options' => [
								[
									'label' => '不进行本地化',
									'value' => '0'
								],
								[
									'label' => '发布时本地化图片',
									'value' => '1'
								]
							],
							'tip'     => '图片太多太大时，可能会本地化失败！'
						]
					],
				]
			],
			[
				"id"       => "nicen_make_plugin_batch_local",
				'title'    => '批量本地化',
				'callback' => [
					"render" => "Nicen_form_batch"
				]
			],
			[
				"id"       => "nicen_make_plugin_compress",
				'title'    => '图片压缩',
				'callback' => [
					"render" => "Nicen_form_compress"
				]
			],
			[
				"id"     => "nicen_make_plugin_white",
				'title'  => '域名白名单',
				'fields' => [
					[
						'id'       => 'text_info',
						'title'    => '功能说明',
						'callback' => 'nicen_make_plugin_form_text',
						'args'     => [
							'info' => '某些外链可能是有意为之，你可能并不需要进行本地化；处于白名单的域名的图片链接不会进行本地化操作【跳过白名单链接时会提示失败】；格式应当如下（一行一个）：
							<br />
							nicen.cn<br />
							1.nicen.cn<br />
							2.nicen.cn，这样这三个域名的链接都不会被本地化；'
						]
					],
					[
						'id'       => 'nicen_make_publish_white',
						'title'    => '域名白名单',
						'callback' => 'nicen_make_form_textarea',
					],
				]
			],
			[
				"id"       => "nicen_make_plugin_local_log",
				'title'    => '插件日志',
				'callback' => [
					"render" => "nicen_plugin_local_log"
				],
			],
			[
				"id"       => "nicen_make_plugin_update",
				'title'    => '插件升级',
				'callback' => [
					"render" => "nicen_plugin_update"
				],
			]
		]
	]
] );


/**
 * 主题所有配置
 * 键=>默认值
 * */
define( 'nicen_make_CONFIG', [
	"nicen_make_plugin_local"         => '1', //本地化时保存到数据库
	'nicen_make_plugin_private'       => md5( time() ), //初次安装时的接口密钥
	'nicen_make_plugin_editor'        => '1', //开启编辑器插件
	'nicen_make_plugin_save'          => '1', //保存到数据库
	'nicen_make_plugin_save_result'   => '', //临时保存本地化结果
	'nicen_make_plugin_alt'           => '1', //自动新增alt
	'nicen_make_plugin_alt_type'      => '1', //alt增加的类型
	'nicen_make_plugin_path'          => '/wp-content/uploads/replace', //资源保存的路径
	'nicen_make_plugin_add_domain'    => '0', //链接是否增加域名
	'nicen_make_save_type'=>'png',

	/*定时任务*/
	'nicen_make_plugin_order'         => 'ID',
	'nicen_make_plugin_auto_publish'  => "0",
	'nicen_make_plugin_interval'      => 300,
	'nicen_make_plugin_publish_local' => '0',
	'nicen_make_publish_date'         => "0",


	/*白名单*/
	'nicen_make_publish_white'        => '',
] );