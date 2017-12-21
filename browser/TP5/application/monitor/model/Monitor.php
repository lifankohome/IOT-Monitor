<?php
/**
 * Created by PhpStorm.
 * User: lifanko  lee
 * Date: 2017/12/20
 * Time: 22:39
 */
namespace app\monitor\model;

use think\Cookie;
use think\Db;
use think\Model;

class Monitor extends Model
{
    //数据表名称
    protected $table = 'monitor_home';

    public static function getDataMin($uid = '')
    {
        if (strlen($uid) != 17) {
            $data = Db::table('monitor')->field('temp, humi, smog, time')->where('uid', '11-11-00-00-00-00')->order('id DESC')->limit(30)->select();    //查询30条记录，不用时间作为筛选条件，因为硬件设备有可能会掉线——某些时候查询可能会出现空数据
        } else {
            $data = Db::table('monitor')->field('temp, humi, smog, time')->where('uid', $uid)->order('id DESC')->limit(30)->select();
        }

        return $data;
    }

    public static function checkHome($homeId, $homeName)
    {
        $Monitor = Monitor::get(['uid' => $homeId]);
        if ($Monitor == null) {
            $result = 'null';
        } else if ($Monitor['name'] == '家') {
            $result = 'new';
        } else if ($Monitor['name'] == $homeName) {
            $result = 'success';
            //设置cookie
            Cookie::set('homeId', $homeId, 60 * 60 * 24 * 30);    //一个月
            Cookie::set('homeName', $homeName, 60 * 60 * 24 * 30);    //一个月
        } else {
            $result = 'unMatch';
        }

        return $result;
    }

    public static function linkHome($homeId, $homeName)
    {
        $Monitor = new Monitor();
        $Monitor->save(['name' => $homeName], ['uid' => $homeId]);

        //设置cookie
        Cookie::set('homeId', $homeId, 60 * 60 * 24 * 30);    //一个月
        Cookie::set('homeName', $homeName, 60 * 60 * 24 * 30);    //一个月

        return 'success';
    }
}