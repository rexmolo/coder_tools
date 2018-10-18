<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2018 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/** action.php
 *
 *
 * @version:
 * @copyright: 2018 ShopEx
 * @author: MaHui <397091486@qq.com> 2018/5/15 - 15:22
 * @license:
 */
class weixin_event_action {

	private $processor;
	private $configPath = 'config/';

	public function __construct() {
		$this->processor = include_once "{$this->configPath}processor.php";
	}

	/**
	 * @param $weChatPostData
	 *
	 * @return bool
	 */
	public function handle( $weChatPostData ) {
		try {
			if ( empty( $this->processor ) ) {
				return true;
			}
			$this->log(  print_r($weChatPostData, 1) );
			$scene = weixin_event_helper_coupon::getScene( $weChatPostData['EventKey'], $weChatPostData['Event'] );
			$this->log("场景为：" . $scene);
//			foreach ($this->processor as $_class => $val) {
			$event = $this->processor[ $scene ];
			$obj   = $this->_getClassObj( $event['_class'], $weChatPostData );
			if ( $obj instanceof weixin_event_interface_event ) {
				call_user_func_array(
					[ $obj, $event['_func'] ], [ 'params' => $event['params'] ]
				);
			} else {

				$this->log( '未实现接口' . 'weixin_event_interface_event' );
				unset( $obj );
			}
//			}
		} catch ( LogicException $le ) {
			$this->log( '事件处理入口抛出错误：' );
			$this->log( $le->getMessage() );
		} catch ( Exception $e ) {
			$this->log( '事件处理入口抛出错误：' );
			$this->log( $e->getMessage() );
//			echo sprintf("错误：%s 文件发生位置: %s <b>%s行</b>，调用栈如下 \n\n", $e->getMessage(), $e->getFile(), $e->getLine());
//			exit($e->getTraceAsString());
		}
	}


	public function setCallFuncParams( $params ) {

	}

	/**
	 * @param $_class
	 * @param $val
	 *
	 * @return mixed
	 */
	private function _getClassObj( $_class, $val ) {
		return new $_class( $val );
	}


	/**
	 * 记录日志
	 *
	 * @param $msg
	 * @param bool $boolean
	 *
	 * @return bool
	 */
	private function log( $msg, $boolean = true ) {
		kernel::log( $msg, '', 'weixin_subscribe.php' );

		return $boolean;
	}

}