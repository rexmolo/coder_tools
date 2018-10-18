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
 * @author: MaHui <397091486@qq.com> 2018/5/16 - 11:35
 * @license:
 */

class weixin_event_helper_coupon {


	/**
	 * 获取场景 ID
	 * @return mixed
	 */
	public static function getScene($key, $event)
	{
		$sceneKey = $event == 'subscribe' ? 1 : 0;
		$keys = strpos($key, '_') ? explode('_', $key) : $key;
		return is_array($keys) ? $keys[$sceneKey] : $keys;
	}

	/**
	 * 获取场景 ID
	 *
	 * [EventKey] => qrscene_coupon_8
	 * @return mixed
	 */
	public function getSceneId($key, $event = 'event')
	{
		$sceneKey = $event == 'subscribe' ? 2 : 1;
		$keys = strpos($key, '_') ? explode('_', $key) : $key;
		return is_array($keys) ? $keys[$sceneKey] : $keys;
	}

	/**
	 * 检测是否含有 Ticket
	 * @return bool
	 */
	public function isExistTicket($arr)
	{
		if (!array_key_exists('Ticket', $arr))
			return false;
		return true;
	}


	/**
	 * @param int  $validityData   延长的时间，例如 15、30, 默认给30天
	 *
	 * @return float|int
	 */
	public static function getCouponDelayTime($validityData)
	{
		$validity_date = intval($validityData) ? $validityData : 30;
		$delay = $validity_date * 86400;
		return $delay;
	}


    /**
     * @param $couponRule
     *
     * @return float|int
     */
    public static function getCouponDelayTime2($couponRule, $coupon)
    {
        $unknownTime = $coupon['memc_gen_time'] + ($couponRule['validity_date'] * 86400); //未知时间，可能比toTIME长 可能短
        if ($unknownTime > $couponRule['to_time']) {
            if ($coupon['memc_gen_time'] > $couponRule['to_time']) {
                return 0;
            } else {
                $difference = $couponRule['to_time'] - $coupon['memc_gen_time'];
                $delay      = round($difference / 86400);
            }
        } else {
            $delay = $couponRule['validity_date'];
        }

        return $delay * 86400;
    }

//	public static function getCouponExpireTime($couponRule)
//    {
//        if (self::existValidityDate($couponRule)) {
//            $delayTime = self::getCouponDelayTime($couponRule['validity_date']);
//
//        }
//    }

    /**
     * 有效期字段是否存在值
     *
     * @param $couponRule
     *
     * @return bool
     */
	public static function existValidityDate($couponRule)
    {
        if (intval($couponRule['validity_date']) == 0 || $couponRule['validity_date'] < 0)
            return false;
        return true;
    }
}