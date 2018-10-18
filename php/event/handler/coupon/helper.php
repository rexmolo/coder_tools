<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2018 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/** helper.php
 *
 *
 * @version:
 * @copyright: 2018 ShopEx
 * @author: MaHui <397091486@qq.com> 2018/5/22 - 11:25
 * @license:
 */

class weixin_event_handler_coupon_helper extends weixin_abstract_mdlInstance {

	public function __construct() {
		$this->instanceMdl(['b2c' => ['member_coupon_receive_qrcode', 'member_coupon', 'members']]);
	}

	/**
	 * 从暂存表移动到用户优惠券关联表
	 *
	 * @param $openId
	 * @param $memberId
	 *
	 * @return mixed
	 */
	public function mvQrCoupon2memberCoupon($openId='', $memberId)
	{
	    $moveCoupons = [];
		$this->log('found member id , moving coupon info to table of [sdb_b2c_member_coupon_receive_qrcode]');

		if (empty($openId)) {
			$memberInfo = $this->mdlS['members']->getRow('wx_openid',array('member_id' => $memberId));
			if (!empty($memberInfo))
				$openId = $memberInfo['wx_openid'];
		}

		if (empty($openId))
			return $this->log('openid 不能为空', false);

		$qrCoupon = $this->mdlS['member_coupon_receive_qrcode']->getList('*', ['openid' => $openId]);
		if (empty($qrCoupon))
			return $this->log('二维码优惠券表不存在记录', false);

		foreach ($qrCoupon as $key => $value) {
			$foundCoupon = $this->mdlS['member_coupon']->count(['member_id' => $memberId, 'cpns_id'=> $value['cpns_id']]);
			if($foundCoupon) {
				$this->log('用户优惠券已经存在,信息如下：', false);
				$this->log($foundCoupon);
				continue;
			}

			$memberCoupon = [
				'cpns_id'         => $value['cpns_id'],
				'member_id'       => $memberId,
				'memc_used_times' => 0,
				'memc_gen_time'   => $value['create_time'],
				'memc_code'       => $value['memc_code'],
				'source'          => '3',
			];

			$insertID = $this->mdlS['member_coupon']->insert($memberCoupon);
			if ($insertID)
				$affected = $this->mdlS['member_coupon_receive_qrcode']->update(['member_id' => $memberId],
					[
						'openid'  => $openId,
						'cpns_id' => $qrCoupon['cpns_id']
					]);

            $moveCoupons[] = $memberCoupon;
		}

		return $moveCoupons;
	}

	/**
	 * 记录日志
	 *
	 * @param $msg
	 * @param bool $boolean
	 *
	 * @return bool
	 */
	private function log($msg, $boolean = true)
	{
		kernel::log($msg, '', 'wexin_event_handler_coupon_action.php');
		return $boolean;
	}
}