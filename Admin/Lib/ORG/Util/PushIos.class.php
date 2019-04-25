<?php

/*
 * @author vini
 * IOS消息推送，服务器端PHP代码
 */

class iospush {

	// Put your device token here (without spaces): example='b5988f280ac851997b02739bd20624d9e2b9aa62a6345725cb65c01f2d726e1e'
	private $_deviceToken = '';
	// Put your private key's passphrase here:
	private $_passphrase = '';

	/**
	 * 构造函数
	 * @param string $username
	 * @param string $password
	 * @param string $appkeys
	 */
	function __construct($deviceToken = '', $passphrase = '123123') {
		$this->_deviceToken = $deviceToken;
		$this->_passphrase = $passphrase;
	}

	function send($message) {
		//echo $this->_deviceToken."/t".$this->_passphrase;
		$ctx = stream_context_create();
		$stream_context1 = stream_context_set_option($ctx, 'ssl', 'local_cert', dirname(__FILE__) . '/ck.pem');
		$stream_context2 = stream_context_set_option($ctx, 'ssl', 'passphrase', $this->_passphrase);
		// Open a connection to the APNS server
		// 这个为正是的发布地址
		 $fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT, $ctx);
		// 这个是沙盒测试地址，发布到appstore后记得修改哦
		 //$fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
		if (!$fp) {
			return "Failed to connect: $err $errstr .";
			//exit("Failed to connect: $err $errstr" . PHP_EOL);
		}
		//echo 'Connected to APNS' . PHP_EOL;
		// Build the binary notification
		$msg = chr(0) . pack('n', 32) . pack('H*', $this->_deviceToken) . pack('n', strlen($message)) . $message;

		// Send it to the server
		$result = fwrite($fp, $msg, strlen($msg));
		if (!$result) {
			return 'Message not delivered.';
			//echo 'Message not delivered' . PHP_EOL;
		} else {
			return 'Message successfully delivered';
			//echo 'Message successfully delivered' . PHP_EOL;
		}
		// Close the connection to the server
		fclose($fp);
	}

}

/* @example
//设备标识，与设备绑定，只有用户最后一次登陆的设备可收到推送消息
$deviceToken = 'd7a174521a287839f8f9ec37bef2069aa405f3ec39f615c82ae5faa4b0115af3';
$passphrase = '123123';
$IOS = new iospush($deviceToken, '123123');
$msg = json_encode(array('aps' => array('alert' => "订单编号172378917239872已发货，我们的快递人员(大将：13700000001）会尽快将物品送到您的手上。", 'sound' => 'default')));
$res = $IOS->send($msg);
echo $res;
*/
?>