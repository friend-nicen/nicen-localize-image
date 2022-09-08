<?php

/*
 * @author
 *
 * 后台表单渲染函数
 * 后台外部样式加载
 * */

include_once __DIR__ . '/render.php'; //引入各种渲染函数

/*
 * 部分组件没有输出表单元素，
 * 所以需要一个隐藏的input
 *
 * 例如select组件
 * 例如colorpicker组件
 * */


/*
 * 加载主题设置页面
 * */
function nicen_make_setting_load($options)
{

    global $plugin_page;

    // 检查用户权限
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap" id="VueApp" v-cloak>
        <a-config-provider :locale="zhCN">
            <div>
                <a-page-header
                        title="<?php echo esc_html(get_admin_page_title()); ?>"
                        :backIcon="false"
                        sub-title="加油">
                    <template #extra>
                        <a-button :loading="loading" type="primary" @click="save">
                            {{loading?"正在保存...":"保存设置"}}
                        </a-button>
                    </template>
                </a-page-header>

                <a-form
                        action="options.php"
                        method="post"
                        label-align="left"
                        :label-col="{ span: 4 }"
                        :wrapper-col="{ span: 10 }"
                        ref="submit"
                >

                    <?php
                    // 输出可允许修改的选项
                    settings_fields($plugin_page);
                    ?>
                    <div class="card-container">
                        <a-tabs type="card" v-model="activeKey" @change="change">
                            <?php
                            //输出输入域
                            nicen_make_do_settings_sections_user($plugin_page);
                            ?>
                        </a-tabs>
                    </div>
                </a-form>
            </div>
        </a-config-provider>
    </div>
    <?php
}


/**
 * 主题域输出
 *
 *
 * @支持 自定义函数输出+表单输出
 * @支持 自定义开关，显示隐藏所有表单，以及不参与该操作的表单
 * @支持 自定义表单的tip提示
 *
 * @param array $callback ,一些配置 [render,自定义渲染函数，key，显示隐藏的字段，忽略的字段 ignore
 * */
function nicen_make_do_settings_fields_user($page, $section, $callback = false)
{
    global $wp_settings_fields;
    if (!isset($wp_settings_fields[$page][$section])) {
        return;
    }

    /*
     * 判断是否有条件判断
     * */

    $param = [];//是否需要显示、隐藏切换

    if ($callback) {
        $param = $callback(); //获取预定的配置参数
    }


    /*
     * 遍历所有分节
     * */
    foreach ((array)$wp_settings_fields[$page][$section] as $field) {


        /*
         * 如果是文字说明
         * */
        if ($field['id'] == 'text_info') {
            echo sprintf('<a-form-item label=%s>', esc_html($field['title']));
            echo esc_html($field['callback']($field['args']));
            echo '</a-form-item>';
            continue;
        }

        /*
         * 是否需要自定义提示
         * */
        if (!isset($field['args']['tip'])) {
            $label = 'label=%s';
        } else {
            $label = '';
        }

        /*
         * 是否具有总开关
         * */
        if (!isset($param['key'])) {
            echo sprintf('<a-form-item ' . esc_html($label) . '>', esc_html($field['title']));
        } else {

            /*
             * 总开关或者忽略的
             * */
            if ($param['key'] == $field['id'] || in_array($field['id'], $param['ignore'])) {
                echo sprintf('<a-form-item ' . esc_html($label) . '>', esc_html($field['title']));
            } else {
                echo sprintf('<a-form-item v-show="data.' . esc_html($param['key']) . ' == 1" ' . esc_html($label) . '>', esc_html($field['title']));
            }

        }

        /*
         * 是否需要输出自定义tip
         * */
        if (isset($field['args']['tip'])) {
            echo sprintf('<template #label>
                             <a-tooltip placement="rightTop">
                            <template slot="title">
                              %s
                            </template>
                            <a-icon style="margin-right: 6px;" type="question-circle" />
                          </a-tooltip>
                            %s
                            </template>', esc_html($field['args']['tip']), esc_html($field['title']));
        }

        /*
         * 调用输出函数
         * */
        call_user_func(
            $field['callback'],
            /*合并出需要的参数*/
            array_merge(
                $field['args'] ?? [],
                [
                    'label_for' => $field['id'],
                    'title' => esc_html($field['title'])
                ]
            ));

        echo '</a-form-item>';

    }
}

/*
 * 主题设置片段页面输出
 * */
function nicen_make_do_settings_sections_user($page)
{
    global $wp_settings_sections;


    if (!isset($wp_settings_sections[$page])) {
        return;
    }

    foreach ((array)$wp_settings_sections[$page] as $key => $section) {


        /*输出tab头*/
        echo sprintf('<a-tab-pane key="%s" tab="%s" :force-render="true">', esc_html($key), esc_html($section['title']));


        $param = [];//是否需要显示、隐藏切换

        /*
         * 是否有传递回调函数
         * */
        if (isset($section['callback'])) {
            $param = $section['callback']();
        }


        /**
         * 输出输入组件
         *
         * @param $page integer "菜单页面id"
         * @param $section array 分节的信息
         * */

        nicen_make_do_settings_fields_user($page, $section['id'], $section['callback'] ?? false);

        /*
         * 回调函数如果有自定义输出
         * */
        if (isset($param['render'])) {
            esc_html($param['render']());
        }

        /*闭合*/
        echo "</a-tab-pane>";


    }
}

/*
 * 数字输入框
 * */
function nicen_make_form_number($args)
{
    ?>
    <a-input-number
            name="<?php echo esc_html($args['label_for']); ?>"
            style="width: 100%;"
            placeholder="请输入<?php echo esc_html($args['title']); ?>"
            v-model="data.<?php echo esc_html($args['label_for']); ?>"
    />
    <?php
}


/*
 * 基础输入框
 * */
function nicen_make_form_input($args)
{
    ?>
    <a-input
            name="<?php echo esc_html($args['label_for']); ?>"
            placeholder="请输入<?php echo esc_html($args['title']); ?>"
            v-model="data.<?php echo esc_html($args['label_for']); ?>"
            allow-clear/>
    <?php
}


/*
 * 基础密码输入框
 * */
function nicen_make_form_password($args)
{
    ?>
    <a-input-password
            name="<?php echo esc_html($args['label_for']); ?>"
            placeholder="请输入<?php echo esc_html($args['title']); ?>"
            v-model="data.<?php echo esc_html($args['label_for']); ?>"
            allow-clear/>
    <?php
}

/*
 * 基础开关
 * */
function nicen_make_form_switch($args)
{
    ?>
    <input name="<?php echo esc_html($args['label_for']); ?>" v-model="data.<?php echo esc_html($args['label_for']); ?>"
           hidden/>
    <a-switch
            name="<?php echo esc_html($args['label_for']); ?>"
            :checked="data.<?php echo esc_html($args['label_for']); ?> == 1"
            @change="(checked,events)=>{hasChange(checked,events,'<?php echo esc_html($args['label_for']); ?>')}"
    />
    <?php
}

/*
 * 基础开关
 * */
function nicen_make_form_textarea($args)
{
    ?>
    <a-textarea
            name="<?php echo esc_html($args['label_for']); ?>"
            v-model="data.<?php echo esc_html($args['label_for']); ?>"
            placeholder="请输入<?php echo esc_html($args['title']); ?>"
            :rows="4"
            :auto-size="{minRows: 4}"
            allow-clear/>
    <?php
}

/*
 * 基础开关
 * */
function nicen_make_form_color($args)
{
    ?>
    <div style="display: flex;align-items: center">
        <input name="<?php echo esc_html($args['label_for']); ?>"
               v-model="data.<?php echo esc_html($args['label_for']); ?>" hidden/>
        <color-picker v-model="data.<?php echo esc_html($args['label_for']); ?>"></color-picker>
        <a-input
                name="<?php echo esc_html($args['label_for']); ?>"
                placeholder="请输入<?php echo esc_html($args['title']); ?>"
                v-model="data.<?php echo esc_html($args['label_for']); ?>"
                allow-clear/>
    </div>
    <?php
}


/*
 * 文字说明
 * */
function nicen_make_plugin_form_text($args)
{
    ?>
        <!-- I need echo HTML,This isn't input from user. -->
    <div class="info"><?php echo wp_kses_post($args['info']); ?></div>
    <?php
}


/*
 * 单选
 * */
function nicen_make_form_select($args)
{
    ?>
    <input name="<?php echo esc_html($args['label_for']); ?>" v-model="data.<?php echo esc_html($args['label_for']); ?>"
           hidden/>
    <a-select
            :options='<?php echo json_encode(is_array($args['options']) ? $args['options'] : $args['options']()); ?>'
            style="width: 100%"
            show-arrow
            v-model="data.<?php echo esc_html($args['label_for']); ?>"
            placeholder="请选择<?php echo esc_html($args['title']); ?>"
    />
    <?php
}


/*
 * 单选
 * */
function nicen_make_form_multi($args)
{

    ?>

    <input name="<?php echo esc_html($args['label_for']); ?>" v-model="data.<?php echo esc_html($args['label_for']); ?>"
           hidden/>
    <a-select
            :options='<?php echo json_encode(is_array($args['options']) ? $args['options'] : $args['options']()); ?>'
            style="width: 100%"
            show-arrow
            mode="multiple"
            v-model="data.<?php echo esc_html($args['label_for']); ?>"
            placeholder="请选择<?php echo esc_html($args['title']); ?>"
    />
    <?php
}





