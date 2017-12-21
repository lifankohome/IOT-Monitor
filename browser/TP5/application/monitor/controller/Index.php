<?php
namespace app\monitor\controller;

use app\monitor\model\Monitor;
use think\Controller;
use think\Cookie;
use think\Request;

//CREATE TABLE `lifanko`.`monitor_home` ( `id` INT NOT NULL AUTO_INCREMENT , `uid` VARCHAR(32) NOT NULL DEFAULT '00-00-00-00-00-00' , `name` VARCHAR(32) NOT NULL DEFAULT '家' , `time` VARCHAR(32) NOT NULL DEFAULT '0' , PRIMARY KEY (`id`)) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT = '家庭环境监测所有设备记录表';

class Index extends Controller
{
    public function index($home = '')
    {
        if (strlen($home) != 17) {
            if (strlen(Cookie::get('homeId')) == 17) {
                $home = Cookie::get('homeId');
            } else {
                $home = '00-00-00-00-00-00';
            }
        }

        $dataMin = Monitor::getDataMin($home);

        $arrayTemp = $arrayHumi = $arraySmog = $arraySmogCN = $arraySmogUS = $arrayTime = array();
        foreach ($dataMin as $record) {
            array_push($arrayTemp, round($record['temp'], 1));
            array_push($arrayHumi, round($record['humi']));
            array_push($arraySmog, round($record['smog']));
            array_push($arraySmogCN, self::AQI_CN($record['smog']));
            array_push($arraySmogUS, self::AQI_US($record['smog']));
            array_push($arrayTime, date('H:i', $record['time']));
        }

        $jsonTemp = json_encode(array_reverse($arrayTemp));
        $jsonHumi = json_encode(array_reverse($arrayHumi));
        $jsonSmog = json_encode(array_reverse($arraySmog));
        $jsonSmogCN = json_encode(array_reverse($arraySmogCN));
        $jsonSmogUS = json_encode(array_reverse($arraySmogUS));
        $jsonTime = json_encode(array_reverse($arrayTime));

        $this->assign('tempMin', $jsonTemp);
        $this->assign('humiMin', $jsonHumi);
        $this->assign('smogMin', $jsonSmog);
        $this->assign('smogMinCN', $jsonSmogCN);
        $this->assign('smogMinUS', $jsonSmogUS);
        $this->assign('timeMin', $jsonTime);
        $this->assign('homeId', $home);

        if (Cookie::has('homeName')) {
            $this->assign('homeName', '<a href="/monitor/login" title="重新登录">' . Cookie::get('homeName') . '</a>');
        } else {
            $this->assign('homeName', '<a href="/monitor/login">登录我的家庭</a>');
        }

        return view();
    }

    public function login()
    {
        return view();
    }

    public function checkHome(Request $request)
    {
        $homeId = $request->post('homeId');
        $homeName = $request->post('homeName');

        if (strlen($homeId) == 17) {
            echo Monitor::checkHome($homeId, $homeName);
        } else {
            echo '非正常请求，请停止操作';
        }
    }

    public function linkHome(Request $request)
    {
        $homeId = $request->post('homeId');
        $homeName = $request->post('homeName');

        if (strlen($homeId) == 17) {
            echo Monitor::linkHome($homeId, $homeName);
        } else {
            echo '非正常请求，请停止操作';
        }
    }

    /**
     * @param $concentration
     * @return int
     */
    private
    static function AQI_CN($concentration)
    {
        if ($concentration > 500) {
            return 500;
        } else if ($concentration > 350.1) {
            return round(99 / 149.9 * ($concentration - 350.1) + 401);
        } else if ($concentration > 250.1) {
            return round(99 / 99.9 * ($concentration - 250.1) + 301);
        } else if ($concentration > 150.1) {
            return round(99 / 99.9 * ($concentration - 150.1) + 201);
        } else if ($concentration > 115.1) {
            return round(49 / 34.9 * ($concentration - 115.1) + 151);
        } else if ($concentration > 75.1) {
            return round(49 / 39.9 * ($concentration - 75.1) + 101);
        } else if ($concentration > 35.1) {
            return round(49 / 39.9 * ($concentration - 35.1) + 51);
        } else {
            return round(50 / 35 * $concentration);
        }
    }

    /**
     * @param $concentration
     * @return int
     */
    private
    static function AQI_US($concentration)
    {
        if ($concentration > 500) {
            return 500;
        } else if ($concentration > 350.5) {
            return round(99 / 149.9 * ($concentration - 350.5) + 401);
        } else if ($concentration > 250.5) {
            return round(99 / 99.9 * ($concentration - 250.5) + 301);
        } else if ($concentration > 150.5) {
            return round(99 / 99.9 * ($concentration - 150.5) + 201);
        } else if ($concentration > 65.5) {
            return round(49 / 84.9 * ($concentration - 65.5) + 151);
        } else if ($concentration > 40.5) {
            return round(49 / 24.9 * ($concentration - 40.5) + 101);
        } else if ($concentration > 15.5) {
            return round(49 / 24.9 * ($concentration - 15.5) + 51);
        } else {
            return round(50 / 15.4 * ($concentration - 0) + 0);
        }
    }
}
