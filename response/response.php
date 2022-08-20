<?php
/*
* @author 友人a丶
* @date ${date}
* 外部图片下载
*/



/*
 * 链接提交
 * */
function nicen_auth() {

	$private = get_option( "nicen_plugin_private" );

	if ( empty( $_GET['private'] ) && empty( $_POST['private'] ) ) {
		exit( json_encode( [
			'code'   => 0,
			'result' => "密钥为空"
		] ) );
	}


	if ( ( $_GET['private'] ?? "" ) != $private && ( $_POST['private'] ?? "" ) != $private ) {
		exit( json_encode( [
			'code'   => 0,
			'result' => "密钥有误"
		] ) );
	}
}


if ( isset( $_GET['nicen_replace'] ) ) {


	nicen_auth(); //权限验证

	nicen_local_image($_POST['img']);
}
