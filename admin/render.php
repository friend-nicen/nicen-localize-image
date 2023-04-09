<?php

/*
* @author 友人a丶
* @date 2022/8/27
* 表单自定义的渲染函数
*/


/*
 * 输出本地化日志
 * */
function nicen_plugin_local_log() {

	$logs = ( ( Nicen_log::getInstance() )->get_logs() );

	echo '<a-form-item :label-col="labelCol" label="相关操作">';
	echo '<a-button type="primary" @click="clearLogs">清空插件日志</a-button>';
	echo '</a-form-item>';

	echo '
		<a-form-item :label-col="labelCol" label="详细日志">
		  <a-textarea
	            value="' . esc_html( ( empty( $logs ) ? '暂无日志' : $logs ) ) . '"
	            :auto-size="{minRows: 5,maxRows:15}"
	            read-only/>
	';
	echo '</a-form-item>';


}


/**
 * 插件升级
 */
function nicen_plugin_update() {

	echo '
		<a-form-item :label-col="labelCol" label="版本信息">
		  		当前版本（' . esc_html( NICEN_VERSION ) . '）/ 最新版本（{{version}}）
	    </a-form-item>
	    <a-form-item :label-col="labelCol" label="BUG反馈">
		  		微信号good7341、Github提交issue、博客nicen.cn下方留言均可
	    </a-form-item>
	    <a-form-item :label-col="labelCol" label="仓库地址">
		  		Github：<a target="_blank" href="https://github.com/friend-nicen/nicen-localize-image">https://github.com/friend-nicen/nicen-localize-image</a>
				<br />
				Gitee：<a target="_blank" href="https://gitee.com/friend-nicen/nicen-localize-image">https://gitee.com/friend-nicen/nicen-localize-image</a>
				<br />
				博客：<a target="_blank" href="https://nicen.cn/2893.html">https://nicen.cn/2893.html</a>
				<br />
				仓库内的版本永远是最新版本，如您觉得插件给你带来了帮助，欢迎star！祝您早日达成自己的目标！
	    </a-form-item>
	     <a-form-item :label-col="labelCol" label="礼轻情意重">
	     
	      <a-popover placement="top" trigger="hover">
		    <template slot="content">
		      <img style="max-width:300px" :src="donate[0]"/>
		    </template>
		    
		    <a-button type="link">
		      微信支持
		    </a-button>
		    
		  </a-popover>
		  <a-popover placement="top" trigger="hover"> 
		  
		    <template slot="content">
		      <img style="max-width:300px" :src="donate[1]"/>
		    </template>
		    
		    <a-button type="link">
		      支付宝支持
		    </a-button>
		    
		  </a-popover>
		  <a-popover placement="top" trigger="hover">
		  
		    <template slot="content">
		     <img style="max-width:300px" :src="donate[2]"/>
		    </template>
		    
		    <a-button type="link">
		      QQ支持
		    </a-button>
		    
		  </a-popover>
	     		
	     		
	     		
	    </a-form-item>
	';

}


/*
 * 定时任务
 * */
function Nicen_form_timer() {

	echo '<input name="nicen_make_publish_date_start" :value="data.nicen_make_publish_date_start" hidden/>
		<input name="nicen_make_publish_date_end" :value="data.nicen_make_publish_date_end" hidden/>
    <a-form-item :label-col="labelCol" label="定时发布的日期范围：">
				<a-range-picker :default-value="range_picker"  @change="selectPublishRange" allow-clear></a-range-picker>
		</a-form-item>';


	echo '
		<input name="nicen_make_publish_time_start" :value="data.nicen_make_publish_time_start" hidden/>
		<input name="nicen_make_publish_time_end" :value="data.nicen_make_publish_time_end" hidden/>
		<a-form-item :label-col="labelCol" label="每日定时发布的时间范围">
		 <a-time-picker read-only v-model="time_start" style=" width: 165px; text-align: center" placeholder="每日开始时间"></a-time-picker>
	      <a-input
	        style=" width: 30px; border-left: 0; pointer-events: none; backgroundColor: #fff"
	        placeholder="~"
	        disabled
	      ></a-input>
	      <a-time-picker read-only v-model="time_end" style="width: 165px; text-align: center; border-left: 0" placeholder="每日结束时间">
	        </a-time-picker>';


}


/**
 * 文章批量本地化
 */
function Nicen_form_batch() {

	$count = wp_count_posts();//文章总数
	$last  = get_option( 'nicen_last_batch' ); //上次本地化的ID

	/*
	 * 获取上次的ID
	 * */
	if ( ! empty( $last ) ) {
		$last = '上次批量本地化的文章ID为：' . esc_html( $last ) . '，';
	}

	echo '	<a-form-item :label-col="labelCol" label="功能说明">
	<div style="line-height: 1.8; width: 150%; overflow-wrap: break-word; word-spacing: normal; word-break: break-all;">
	按照指定的文章ID范围批量进行图片本地化，点击开始后任务自动运行，运行过程中可以随时暂停，关闭网页表示强制暂停！运行过程中将会展示实时日志！
	<br/><br/>
	' . esc_html( $last ) . '当前共有已发布文章' . esc_html( $count->publish ) . '篇，草稿' . esc_html( $count->draft ) . '篇！不填起始ID默认批量本地化所有文章！
	</div>
	</a-form-item>
	<a-form-item :label-col="labelCol" label="文章ID范围">
	 <a-input-number v-model="batch.start" style=" width: 120px; text-align: center" placeholder="开始ID" /></a-input-number>
      <a-input
        style=" width: 30px; border-left: 0; pointer-events: none; backgroundColor: #fff"
        placeholder="~"
        disabled
      ></a-input>
      <a-input-number v-model="batch.end" style="width: 120px; text-align: center; border-left: 0" placeholder="结束ID">
  		</a-input-number>
		</a-form-item>';


	/*
	 * 新增可选项
	 * */
	$options = '[
				{
	                label:"不限",
	                value:1
            	},
            	{
	                label:"草稿",
	                value:2
            	},
            	{
	                label:"已发布",
	                value:3
            	}
            ]';


	echo "
		<a-form-item :label-col='labelCol' label='按照文章发布状态'>
	    <a-select
            :options='" . $options . "'
			style='width: 100%'
			show-arrow
			v-model='batch.status'
			placeholder='请选择文章发布状态'
			>
			</a-select>
	</a-form-item>";

	echo '<a-form-item :label-col="labelCol" label="文章创建时间范围">
				<a-range-picker @change="selectRange" allow-clear></a-range-picker>
		</a-form-item>';


	echo "
		<a-form-item :label-col='labelCol' label='选择指定分类'>
	    <a-select
            :options='" . json_encode( nicen_plugin_getAllCat() ) . "'
			style='width: 100%'
			show-arrow
			mode='multiple'
			v-model='batch.category'
			placeholder='请选择需要批量本地化的分类'
			>
			</a-select>
	</a-form-item>";

	echo '<a-form-item :label-col="labelCol" label="相关操作">';
	echo '<a-space>
			<a-button type="primary" :loading="batch.loading" @click="getBatch">{{batch.loading?"正在批量本地化，点击取消运行...":"开始批量本地化"}}</a-button>
			<a-button type="primary" v-if="batch.loading" @click="getBatch">取消运行</a-button>
		</a-space>';
	echo '</a-form-item>';

}


/*
 * 文章批量本地化
 * */
function Nicen_form_compress() {

	echo '<a-form-item :label-col="labelCol" label="功能说明">
			选择指定的目录或者图片进行压缩，默认根目录为 wordpress媒体文件存放目录，压缩前请先点击加载图片目录，然后再选中目录或图片进行压缩，压缩服务由 resmush.it 提供。
		 </a-form-item>';

	echo '<a-form-item :label-col="labelCol" label="相关操作">';
	echo '<a-space>
			<a-button type="primary" :loading="tree.loading" @click="compress">{{tree.loading?"正在压缩第"+tree.count+"张图片，点击取消压缩...":"开始压缩"}}</a-button>
			<a-button type="primary" v-if="tree.loading" @click="compress">取消压缩</a-button>
			<a-button type="primary" @click="loadFiles()">加载图片目录</a-button>
		</a-space>';
	echo '</a-form-item>';

	echo "<a-form-item :label-col='labelCol' label='选择指定文件或目录'>
	  <a-tree
	    checkable
	    :load-data='loadFiles'
	    v-model='tree.selected'
	    :tree-data='tree.data'
	  >
	  </a-tree>
	</a-form-item>";


}


/**
 * 插件升级
 */
function nicen_plugin_vip() {
	?>
    <div class="vip">
        <div class="top">开源版已进入LTS（Long-term
            support，长期支持，缩写：LTS）阶段，以后只修复BUG不再更新新功能，新功能将发布在Pro版：
        </div>
        <ul class="features">
            <li class="item ok">1. 图片本地化后按照年月日存放在指定的本地化图片保存目录</li>
            <li class="item ok">2. 编辑器内可直接粘贴截图，并自动上传到服务器</li>
            <li class="item ok">3. 文章发布时自动给a标签添加target="_blank"属性</li>
            <li class="item ok">4. 自定义下图片本地化时的referer，绕过防盗链</li>
            <li class="item">5. 本地化图片后自动添加设置的水印</li>
            <li class="item">6. 本地化时直接上传对象存储，并替换链接为对象存储的链接</li>
            <li class="item">7. .......还有更多</li>
        </ul>


        <div class="bottom">
            -------> 39.9元/Pro版永久+更新+售后（无使用限制，后台可下载最新版插件）；如您有意请加微信good7341
        </div>

    </div>

	<?php
}