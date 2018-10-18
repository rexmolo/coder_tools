<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2018 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/** coupon.php
 *
 *
 * @version:
 * @copyright: 2018 ShopEx
 * @author: MaHui <397091486@qq.com> 2018/5/15 - 15:47
 * @license:
 */

/**
 * Class wexin_event_handler_coupon_action
 *
 */
class weixin_event_handler_coupon_action extends weixin_abstract_mdlInstance implements weixin_event_interface_event {

	/**
	 *  //ToUserName    开发者微信号
	 * //FromUserName    发送方帐号（一个OpenID）
	 * //CreateTime    消息创建时间 （整型）
	 * //MsgType        消息类型，event
	 * //Event            事件类型，subscribe or SCAN
	 * //EventKey        事件KEY值，qrscene_为前缀，后面为二维码的参数值
	 * //Ticket        二维码的ticket，可用来换取二维码图片
	 * @var
	 */
	private $weChatPostData;
	private $objWidgetCoupons;
	private $helper;
	private $errFile = 'wexin_event_handler_coupon_action.php';

	private $context;

	public function __construct( $weChatPostData ) {
		$this->weChatPostData   = $weChatPostData;
		$this->objWidgetCoupons = kernel::single( 'wap_widgets_coupons' );
		$this->helper           = kernel::single( 'weixin_event_helper_coupon' );
		$this->instanceMdl( 'member_coupon_receive_qrcode', 'b2c' );
		$this->context['couponId'] = $this->helper->getSceneId( $this->weChatPostData['EventKey'] , $this->weChatPostData['Event']);
	}

	/**
	 * @param $data
	 *
	 * @return bool
	 */
	public function execute( $data ) {
		$this->log( 'weixin_event_handler_coupon_action func execute 获取数据 data:' );
		$this->log( $data );

		$this->log( 'from weChat:' );
		$this->log( $this->weChatPostData );

		if ( ! $this->helper->isExistTicket( $this->weChatPostData ) ) {
			return false;
		}

		if ( $this->isReceivedCoupon() ) {
            $this->sendWeiChatMsg('您已领取，请勿重复操作');
			return $this->log( 'current user has received the coupon, user openid:' . $this->weChatPostData['FromUserName'] );
		}

		if ( ! $this->checkCoupon( $this->context['couponId'], $msg ) ) {
			$this->log( $msg );

			return false;
		}

		$this->instanceMdl( [ 'b2c' => [ 'member_coupon_receive_qrcode', 'member_coupon' ] ] );
		if ( ! $code = $this->makeCode( $this->context['couponId'], $msg ) ) {
			return false;
		}

		$this->saveQrcodeCoupon();

		//通过openid 查找用户是否存在
		$memberId = $this->getMemberIdByOpenId();
		$this->log( 'get member id by openid ... ... as:' . $memberId );
		if ( $memberId ) {
            $moveCoupons = kernel::single('weixin_event_handler_coupon_helper')
                                 ->mvQrCoupon2memberCoupon($this->weChatPostData['FromUserName'], $memberId);
            $this->context['moveCoupons'] = $moveCoupons;
		}

		$this->log( 'moving done, please check your table and start push wx template message to mq queue' );
		//微信模板消息跳转至会员中心的我的优惠券列表，在优惠券列表这里新用户产生了微信登录，注册动作，先执行用户的优惠券发放，之后show列表给用户
		$this->rabbitQueue();
		$this->log( 'process done, and please confirm your rabbitMq is running... ...' );
	}


	/**
	 * 模板写入消息队列
	 */
	private function rabbitQueue() {
		$coupon = $this->mdlS['coupons']->getCouponById( $this->context['couponId'] );
		$cpRule = $this->mdlS['sales_rule_order']->getRow( '*', [ 'rule_id' => $coupon['rule_id'] ] );

		$delay = weixin_event_helper_coupon::getCouponDelayTime2( $cpRule, $this->context['moveCoupons'][0] );

		$expireTime = weixin_event_helper_coupon::existValidityDate($cpRule) ? $this->context['moveCoupons'][0]['memc_gen_time'] + $delay : $cpRule['to_time']; // time() == $coupon['memc_gen_time'] 基本一致
		try {
			$envelopeData = [
				'caption'      => "您已成功领取（{$coupon['cpns_name']}）",
				'keywords'     => [
					'keyword_1' => $coupon['cpns_name'],
//					'keyword_2' => $this->context['code'],
					'keyword_2' => "点击详情查看",
					'keyword_3' => '至' . date( 'Y年m月d日', $expireTime ),
				],
				'keep_keyword' => true,
				'remark'       => '点击查看优惠券详情',
				'template_id'  => 'redeem-code-present',
				'redirecturl'  => '/index.php/wap/member-coupon.html',
				'oAuth'        => true, //是否生成免登陆
				'openid'       => $this->weChatPostData['FromUserName']
			];

			$publisher = kernel::single( 'weixin_template_publisher' );
			$publisher->setConsumer( 'weixin_template_consumer.doAction' )->doAction( $envelopeData );

		} catch ( LogicException $le ) {
			$this->log( '消息队列抛出错误：' );
			$this->log( $le->getMessage() );
		} catch ( Exception $e ) {
			$this->log( '消息队列抛出错误：' );
			$this->log( $e->getMessage() );
		}
	}

	/**
	 * 保存通过二维码领取的优惠券
	 *
	 * @return mixed
	 */
	private function saveQrcodeCoupon() {
		//新老用户全部写入qrcode coupon 表
		$data = [
			'openid'      => $this->weChatPostData['FromUserName'],
			'cpns_id'     => $this->context['couponId'],
			'memc_code'   => $this->context['code'],
			'member_id'   => $this->getMemberIdByOpenId(),
			'create_time' => time(),
			'expire_time' => time(),
		];

		return $this->mdlS['member_coupon_receive_qrcode']->insert( $data );
	}


	/**
	 * 根据openid 获取用户member id
	 *
	 * @return int
	 */
	private function getMemberIdByOpenId() {
		$this->instanceMdl( 'members', 'b2c' );
		if ( $this->context['member_id'] ) {
			return $this->context['member_id'];
		}

		$memberInfo = $this->mdlS['members']->getRow( 'member_id', array( 'wx_openid' => $this->weChatPostData['FromUserName'] ) );
		if ( empty( $memberInfo ) || ! $memberInfo['member_id'] ) {
			return 0;
		}

		return $this->context['member_id'] = $memberInfo['member_id'];
	}

	/**
	 * @param $cpnsId
	 * @param $memberId
	 *
	 * @return bool
	 */
	private function makeCode( $cpnsId, &$msg ) {
		$this->log( 'making code ... ...' );

		$this->instanceMdl( [ 'b2c' => [ 'coupons', 'sales_rule_order' ] ] );

		$coupon = $this->mdlS['coupons']->dump( $cpnsId );

		if ( ! $coupon['rule']['rule_id'] ) {
			return $this->log( $msg = "未查询到规则ID", false );
		}

		$arr_rule_order = $this->mdlS['sales_rule_order']->dump( $coupon['rule']['rule_id'] );

		if ( ! $arr_rule_order ) {
			return $this->log( $msg = "未查询到规则", false );
		}

		do {
			$code = $this->mdlS['coupons']->_makeCouponCode( $coupon['cpns_gen_quantity'] + 1, $coupon['cpns_prefix'], $coupon['cpns_key'] );

		} while ( $this->isExistCode( $code ) );

		//优惠券生成数量加1
		$coupon['cpns_gen_quantity'] += 1;
		$this->mdlS['coupons']->save( $coupon );

		$this->log( 'current code is : ' . $code );

		return $this->context['code'] = $code;
	}

	/**
	 * 优惠券是否存在
	 *
	 * @param $code
	 *
	 * @return bool
	 */
	private function isExistCode( $code ) {
		$table_1 = $this->mdlS['member_coupon_receive_qrcode']->getRow( 'id', [ 'memc_code' => $code ] );
		$table_2 = $this->mdlS['member_coupon']->getRow( 'member_id', [ 'memc_code' => $code ] );
		if ( ! empty( $table_1 ) && ! empty( $table_2 ) ) {
			return true;
		}

		return false;
	}


	/**
	 * 是否已经收过优惠券
	 *
	 * @param $cpnsId
	 *
	 * @return bool
	 */
	private function isReceivedCoupon() {
		//check 临时表
		$coupon = $this->mdlS['member_coupon_receive_qrcode']->getRow( '*', [
			'openid'  => $this->weChatPostData['FromUserName'],
			'cpns_id' => $this->context['couponId'],
		] );
		if ( ! empty( $coupon ) ) {
			return true;
		}

		return false;
	}


	/**
	 * 优惠券目前的状态
	 *
	 * @param $cpnsId
	 * @param $msg
	 *
	 * @return bool
	 */
	private function checkCoupon( $cpnsId, &$msg ) {
		$msgArr = array(
			'2' => '优惠券不存在',//cpns_id为空
			'3' => '优惠券已经领光',
			'4' => '会员等级不符',
			'5' => '活动暂未开始',
			'6' => '活动已到期',
			'8' => '活动未开启',
		);

		$verify_status = $this->verifyCoupon( $cpnsId );
		$this->log( '当前处理的优惠券的状态为：' . $verify_status );

		//推送优惠券当前状态给用户
        if ($verify_status != 1) {
            $msg       = $msgArr[$verify_status];
            $this->sendWeiChatMsg($msg);
            return false;
        }

		return true;
	}

	/**
	 * 拷贝自 app/wap/lib/widgets/coupons.php ，因为当前业务无法拿到用户ID去判断用户等级，所以去掉了判断会员等级的部分
	 *
	 * @param $cpns_id
	 *
	 * @return int
	 */
	private function verifyCoupon( $cpns_id ) {
		if ( ! $cpns_id ) {
			return 2;
		}
		$time = time();
		$this->instanceMdl( 'coupons', 'b2c' );

		$coupons = $this->mdlS['coupons']->getCouponById( $cpns_id );

		//1、 这里暂时去掉 判断优惠券数量，因为后台就没有开启填写数量，判断无用，
		//2、开始结束时间也去掉，因为该优惠券是以用户领取时间加 设置的一个有效期来定义的
        //@MH 2018-9-7修改：要按照开始时间结束时间了
		$remain = intval($coupons['cpns_max_receive_num']) - intval($coupons['cpns_gen_quantity']);

		if( $remain <= 0 )
			return 3;

		$rule_info = app::get( 'b2c' )->model( 'sales_rule_order' )
                                      ->getRow( 'member_lv_ids,from_time,to_time,status', array( 'rule_id' => $coupons['rule_id'] ) );

		if ( ! $rule_info['status'] ) {
			return 8;
		}

		if (intval($rule_info['validity_date']) == 0) {
            if( $time < $rule_info['from_time'] )
                return 5;

            if( $time >= $rule_info['to_time'] )
                return 6;
        }

		return 1;
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
		kernel::log( $msg, '', $this->errFile );

		return $boolean;
	}

    /**
     * 通过微信推送给用户的消息
     * @param $msg
     *
     * @return mixed
     */
    private function getWeiChatMsg($content)
    {

        $message['ToUserName']   = $this->weChatPostData['FromUserName'];
        $message['FromUserName'] = $this->weChatPostData['ToUserName'];
        $message['CreateTime']   = time();
        $message['MsgType']      = 'text';
        $message['Content']      = $content;

        return $message;
    }

    /**
     * 发送一条微信消息
     *
     * @param $message
     */
    private function sendWeiChatMsg($message)
    {
        $weChatAPI = kernel::single('weixin_object');
        $weChatAPI->send($this->getWeiChatMsg($message));
    }
}