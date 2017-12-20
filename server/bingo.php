<?php
/**
 * Created by PhpStorm.
 * User: lifanko  lee
 * Date: 2017/12/20
 * Time: 20:59
 */
header("Content-type: text/html;charset=utf-8");

$serverName = "localhost";
$dbName = "lifanko";
$dbUserName = "root";
$dbPassword = "lifanko521";
$tableName = "monitor";

try {   //创建pdo连接对象，全局使用
    $pdo = new PDO("mysql:host=$serverName;dbname=$dbName", "$dbUserName", "$dbPassword");
} catch (PDOException $e) {
    die("Unable to connect to the server, please contact manager: lifankohome@163.com");
}
$pdo->query("set names utf8");

function getValue($index)
{
    if (isset($_GET[$index]) && !empty($_GET[$index])) {
        return $_GET[$index];
    } else {
        return '';
    }
}

$opt = getValue('opt'); //查询选项
$uid = getValue('uid'); //唯一标识

$temp = getValue('T');  //温度
$humi = getValue('H');  //湿度
$smog = getValue('S');  //颗粒物浓度

if ($opt == 'mon') {    //mon(itor)
    $sql = "SELECT time FROM $tableName WHERE uid='{$uid}' ORDER BY id DESC limit 1";
    $stmt = $pdo->query($sql);

    $presentTime = time();
    if ($stmt->rowCount() > 0) {    //查询有记录
        $monitor = $stmt->fetch(PDO::FETCH_ASSOC);
        $monitorLastTime = $monitor['time'];

        if ($presentTime - $monitorLastTime >= 120) {   //两次查询时间超过2min则允许添加新数据
            $sql = "INSERT INTO $tableName (uid, temp, humi, smog, time) VALUES ('{$uid}', '{$temp}', '{$humi}', '{$smog}', '{$presentTime}')"; //插入新的数据
            $stmt = $pdo->query($sql);
            if ($stmt->rowCount() > 0) {
                echo 'OK!';
            } else {
                echo 'FAIL!';
            }
        } else {
            echo $presentTime - $monitorLastTime;
        }
    } else {  //未查询到上一条数据，说明该设备是新设备，进行初始化操作
        $sql = "INSERT INTO $tableName (uid, temp, humi, smog, time) VALUES ('{$uid}', '{$temp}', '{$humi}', '{$smog}', '{$presentTime}')"; //插入新的数据
        $stmt = $pdo->query($sql);
        if ($stmt->rowCount() > 0) {
            echo 'Initial Complete!';
        } else {
            echo 'Initial FAIL!';
        }
    }
} else {
    echo 'No Suit Option Code';
}