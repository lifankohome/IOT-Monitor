<?php
/**
 * Created by PhpStorm.
 * User: 47984
 * Date: 2016/9/23
 * Time: 16:41
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

if ($opt == 'mon') {
    $sql = "SELECT time FROM $tableName WHERE uid='{$uid}' ORDER BY id DESC limit 1";
    $stmt = $pdo->query($sql);//预处理
    if ($stmt->rowCount() > 0) {    //查询有值
        $monitor = $stmt->fetch(PDO::FETCH_ASSOC);
        $monitorLastTime = $monitor['time'];

        $presentTime = time();
        if ($presentTime - $monitorLastTime >= 120) {   //两次查询时间超过2min
            $sql = "INSERT INTO $tableName (uid, temp, humi, smog, time) VALUES ('{$uid}', '{$temp}', '{$humi}', '{$smog}', '{$presentTime}')"; //插入新的数据
            $stmt = $pdo->query($sql);
            if ($stmt->rowCount() > 0) {
                $sql = "SELECT time FROM monitor_h WHERE uid='{$uid}' ORDER BY id DESC limit 1";    //查询是否要进行小时平均
                $stmt = $pdo->query($sql1);
                if ($stmt->rowCount() > 0) {
                    $monitor_h = $stmt->fetch(PDO::FETCH_ASSOC);
                    $monitor_hLastTime = $monitor_h['time'];

                    if ($presentTime - $monitor_hLastTime >= 3600) {  //两次数据超过1小时则求平均并填进monitor_h表
                        $sql = "SELECT temp,humi,smog FROM $tableName WHERE uid='{$uid}' time>'{$monitor_hLastTime}'";

                        $tempSum = 0;
                        $humiSum = 0;
                        $smogSum = 0;
                        $num = 0;
                        foreach ($pdo->query($sql) as $row) {   //求和
                            $tempSum += $row['temp'];
                            $humiSum += $row['humi'];
                            $smogSum += $row['smog'];

                            $num += 1;
                        }
                        $averageTemp = round($tempSum / $num, 1);    //温度保留1位小数
                        $averageHumi = round($humiSum / $num);       //湿度仅保留整数
                        $averageSmog = round($smogSum / $num);       //浓度仅保留整数

                        $sql = "INSERT INTO monitor_h (uid, temp, humi, smog, time) VALUES ('{$uid}', '{$averageTemp}', '{$averageHumi}', '{$averageSmog}', '{$presentTime}')"; //插入新的数据
                        $stmt = $pdo->query($sql);

                        $sql = "SELECT time FROM monitor_d WHERE uid='{$uid}' ORDER BY id DESC limit 1";    //查询是否要进行全天平均
                        $stmt = $pdo->query($sql1);
                        if ($stmt->rowCount() > 0) {
                            $monitor_d = $stmt->fetch(PDO::FETCH_ASSOC);
                            $monitor_dLastTime = $monitor_d['time'];
                            //写到这里停止了，因为我突然想到求平均为什么要在数据录入时实现呢？完全可以在查询时进行，这里的程序相应会清晰很多
                        }
                    }
                } else {  //无小时平均数据则说明是新设备，此时进行初始化，将首个数据分别插入 monitor/monitor_h/monitor_d
                    $sql = "INSERT INTO monitor_h (uid, temp, humi, smog, time) VALUES ('{$uid}', '{$temp}', '{$humi}', '{$smog}', '{$presentTime}')"; //插入新的数据
                    $stmt = $pdo->query($sql);
                    $sql = "INSERT INTO monitor_d (uid, temp, humi, smog, time) VALUES ('{$uid}', '{$temp}', '{$humi}', '{$smog}', '{$presentTime}')"; //插入新的数据
                    $stmt = $pdo->query($sql);
                }
            } else {  //插入失败
                echo '插入新数据失败';
            }
        }
    } else {  //未查询到上一条数据，说明该设备是新设备，进行插入操作
        echo '新设备，MAC：' . $uid;
    }
}


if ($opt == "mon") {
    $temp = $_GET['T'];
    $smog = $_GET['S'];
    $humidity = $_GET['H'];
    if (!empty($temp) && !empty($smog) && !empty($humidity)) {
        $time = 0;
        $sql0 = "SELECT time FROM $tableName ORDER BY id DESC limit 1";
        $stmt0 = $pdo->query($sql0);//预处理
        if ($stmt0->rowCount() > 0) {
            $info = $stmt0->fetch(PDO::FETCH_ASSOC);
            $time = $info['time'];
        }
        $timeF = time();
        if ($timeF - $time > 120) {       //120s=两分钟
            $sql = "INSERT INTO $tableName (temp,smog,humidity,time) VALUES ('{$temp}','{$smog}','{$humidity}','{$timeF}')";
            $stmt = $pdo->query($sql);
            if ($stmt->rowCount() > 0) {
                $sql1 = "SELECT time FROM monitor_h ORDER BY id DESC limit 1";
                $stmt1 = $pdo->query($sql1);//预处理
                $timeFlag = 0;
                if ($stmt1->rowCount() > 0) {
                    $infoFlag = $stmt1->fetch(PDO::FETCH_ASSOC);
                    $timeFlag = $infoFlag['time'];
                }
                if ($timeF - $timeFlag >= 3600) {      //超过一小时
                    $numS = 0;
                    $numT = 0;
                    $totalSmog = 0;
                    $totalTemp = 0;
                    $sql2 = "SELECT smog,temp FROM $tableName WHERE time>$timeFlag";
                    foreach ($pdo->query($sql2) as $row) {
                        if ($row['smog'] < 1000) {       //除去超过1000浓度的采集
                            $totalSmog = $totalSmog + $row['smog'];
                            $numS++;
                        }
                        $totalTemp = $totalTemp + $row['temp'];
                        $numT++;
                    }
                    $averageSmog = round($totalSmog / $numS);
                    $averageTemp = round($totalTemp / $numT, 1);
                    $sql3 = "INSERT INTO monitor_h (time,average_s,average_t) VALUES ('{$timeF}','{$averageSmog}','{$averageTemp}')";
                    $stmt3 = $pdo->query($sql3);//预处理
                    if ($stmt3->rowCount() > 0) {
                        $sql11 = "SELECT time FROM monitor_d ORDER BY id DESC limit 1";
                        $stmt11 = $pdo->query($sql11);//预处理
                        $timeFlag1 = 0;
                        if ($stmt11->rowCount() > 0) {
                            $infoFlag1 = $stmt11->fetch(PDO::FETCH_ASSOC);
                            $timeFlag1 = $infoFlag1['time'];
                        }
                        if ($timeF - $timeFlag1 >= 3600 * 24) {
                            $numS = 0;
                            $numT = 0;
                            $totalSmog = 0;
                            $totalTemp = 0;
                            $sql22 = "SELECT average_s,average_t FROM monitor_h WHERE time>$timeFlag1";
                            foreach ($pdo->query($sql22) as $row) {
                                if ($row['average_s'] < 500) {       //除去超过500浓度的采集
                                    $totalSmog = $totalSmog + $row['average_s'];
                                    $numS++;
                                }
                                $totalTemp = $totalTemp + $row['average_t'];
                                $numT++;
                            }
                            $averageSmog = round($totalSmog / $numS);
                            $averageTemp = round($totalTemp / $numT, 1);
                            $sql33 = "INSERT INTO monitor_d (time,average_s,average_t) VALUES ('{$timeF}','{$averageSmog}','{$averageTemp}')";
                            $stmt33 = $pdo->query($sql33);//预处理
                            if ($stmt3->rowCount() > 0) {
                                echo "OK--------------------";
                            }
                        }
                    } else {
                        echo "OK1--------------------";
                    }
                } else {
                    echo "OK2--------------------";
                }
            } else {
                echo "FAIL--------------------";
            }
        } else {
            echo "OK0--------------------";
        }
    } else {
        echo "LOST--------------------";
    }
}

if (!empty($tip) && !empty($uid) && strlen($uid) == "17") {
    $status = "0";
    $time0 = 0;

    $sql0 = "SELECT status,time FROM $tableName WHERE uid = '$uid'";
    $stmt0 = $pdo->query($sql0);
    if ($stmt0->rowCount() == 1) {
        $ad = $stmt0->fetch(PDO::FETCH_ASSOC);
        $time0 = $ad['time'];
        $status = $ad['status'];
        if ($tip == "11") { //11——开启
            if ($status == "tip_on") {
                echo "开启";
            } else {
                $sql = "UPDATE $tableName SET status='tip_on' WHERE uid = '$uid'";
                $rw = $pdo->exec($sql);
                if ($rw == 1) {
                    echo "开启";
                } else {
                    echo "无法更新数据";
                }
            }
        } else if ($tip == "13") { //——13关闭
            if ($status == "tip_off") {
                echo "关闭";
            } else {
                $sql = "UPDATE $tableName SET status='tip_off' WHERE uid = '$uid'";
                $rw = $pdo->exec($sql);
                if ($rw > 0) {
                    echo "关闭";
                } else {
                    echo "无法更新数据";
                }
            }
        } else if ($tip == "15") { //15——是否在线
            if (time() - $time0 < 20) {
                echo "在线";
            } else {
                echo "离线";
            }
        } else if ($tip == "mcu") {
            $time = time();         //设置在线、放置时间戳，限制刷新频率
            if (empty($T)) {
                $sql = "UPDATE $tableName SET time='$time' WHERE uid = '$uid'";
            } else {
                $sql = "UPDATE $tableName SET time='$time',t='$T' WHERE uid = '$uid'";
            }
            $rw = $pdo->exec($sql);
            if ($rw != 1) {
                echo "tip_oUFail";
            } else {
                $sql = "SELECT status FROM $tableName WHERE uid = '$uid'";//返回结果
                $stmt = $pdo->query($sql);
                if ($stmt->rowCount() == 1) {
                    $ad = $stmt->fetch(PDO::FETCH_ASSOC);
                    $status = $ad['status'];
                    $arr = json_decode($status, true);

                    $num = $arr[0] * 8 + $arr[1] * 4 + $arr[2] * 2 + $arr[3];
                    echo 'tip_o' . dechex($num);
                } else {
                    echo "tip_oQFail";
                }
            }
        } else if ($tip == "mon") {
            $tableName = "monitor";
            $temp = $_GET['T'];
            $smog = $_GET['S'];
            $humidity = $_GET['H'];
            if (!empty($temp) && !empty($smog) && !empty($humidity)) {
                $time = 0;
                $sql0 = "SELECT time FROM $tableName ORDER BY id DESC limit 1";
                $stmt0 = $pdo->query($sql0);//预处理
                if ($stmt0->rowCount() > 0) {
                    $info = $stmt0->fetch(PDO::FETCH_ASSOC);
                    $time = $info['time'];
                }
                $timeF = time();
                if ($timeF - $time > 120) {       //120s=两分钟
                    $sql = "INSERT INTO $tableName (temp,smog,humidity,time) VALUES ('{$temp}','{$smog}','{$humidity}','{$timeF}')";
                    $stmt = $pdo->query($sql);
                    if ($stmt->rowCount() > 0) {
                        $sql1 = "SELECT time FROM monitor_h ORDER BY id DESC limit 1";
                        $stmt1 = $pdo->query($sql1);//预处理
                        $timeFlag = 0;
                        if ($stmt1->rowCount() > 0) {
                            $infoFlag = $stmt1->fetch(PDO::FETCH_ASSOC);
                            $timeFlag = $infoFlag['time'];
                        }
                        if ($timeF - $timeFlag >= 3600) {      //超过一小时
                            $numS = 0;
                            $numT = 0;
                            $totalSmog = 0;
                            $totalTemp = 0;
                            $sql2 = "SELECT smog,temp FROM $tableName WHERE time>$timeFlag";
                            foreach ($pdo->query($sql2) as $row) {
                                if ($row['smog'] < 1000) {       //除去超过1000浓度的采集
                                    $totalSmog = $totalSmog + $row['smog'];
                                    $numS++;
                                }
                                $totalTemp = $totalTemp + $row['temp'];
                                $numT++;
                            }
                            $averageSmog = round($totalSmog / $numS);
                            $averageTemp = round($totalTemp / $numT, 1);
                            $sql3 = "INSERT INTO monitor_h (time,average_s,average_t) VALUES ('{$timeF}','{$averageSmog}','{$averageTemp}')";
                            $stmt3 = $pdo->query($sql3);//预处理
                            if ($stmt3->rowCount() > 0) {
                                $sql11 = "SELECT time FROM monitor_d ORDER BY id DESC limit 1";
                                $stmt11 = $pdo->query($sql11);//预处理
                                $timeFlag1 = 0;
                                if ($stmt11->rowCount() > 0) {
                                    $infoFlag1 = $stmt11->fetch(PDO::FETCH_ASSOC);
                                    $timeFlag1 = $infoFlag1['time'];
                                }
                                if ($timeF - $timeFlag1 >= 3600 * 24) {
                                    $numS = 0;
                                    $numT = 0;
                                    $totalSmog = 0;
                                    $totalTemp = 0;
                                    $sql22 = "SELECT average_s,average_t FROM monitor_h WHERE time>$timeFlag1";
                                    foreach ($pdo->query($sql22) as $row) {
                                        if ($row['average_s'] < 500) {       //除去超过500浓度的采集
                                            $totalSmog = $totalSmog + $row['average_s'];
                                            $numS++;
                                        }
                                        $totalTemp = $totalTemp + $row['average_t'];
                                        $numT++;
                                    }
                                    $averageSmog = round($totalSmog / $numS);
                                    $averageTemp = round($totalTemp / $numT, 1);
                                    $sql33 = "INSERT INTO monitor_d (time,average_s,average_t) VALUES ('{$timeF}','{$averageSmog}','{$averageTemp}')";
                                    $stmt33 = $pdo->query($sql33);//预处理
                                    if ($stmt3->rowCount() > 0) {
                                        echo "OK--------------------";
                                    }
                                }
                            } else {
                                echo "OK1--------------------";
                            }
                        } else {
                            echo "OK2--------------------";
                        }
                    } else {
                        echo "FAIL--------------------";
                    }
                } else {
                    echo "OK0--------------------";
                }
            } else {
                echo "LOST--------------------";
            }
        } else {
            echo "Unknown command";
        }
    } else if ($tip == "mcu") {
        $date = date('Y-m-d H:i', time());
        $timeN = time();
        $uid = strtoupper($uid);
        $sql = "INSERT INTO $tableName (uid,remark,date,time) VALUES ('{$uid}','{$date}','{$date}','{$timeN}')";
        $stmt = $pdo->query($sql);
        if ($stmt->rowCount() > 0) {
            echo "tip_off";
        }
    } else {
        echo "tip_oNone";
    }
} else {
    echo "tip_oAFail";
}