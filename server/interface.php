<?php
/**
 * Created by PhpStorm.
 * User: lifanko  lee
 * Date: 2017/12/22
 * Time: 12:50
 */
header("Content-type: text/html;charset=utf-8");

$tableName = "monitor";

try {   //创建pdo连接对象，全局使用
    $pdo = new PDO("mysql:host=localhost;dbname=lifanko", "root", "lifanko521");
} catch (PDOException $e) {
    die("Unable to connect to the database, please contact manager: lifankohome@163.com");
}
$pdo->query("set names utf8");

if (isset($_GET['homeId']) && strlen($_GET['homeId']) == 17) {
    $uid = $_GET['homeId'];
} else {
    $uid = '5c-cf-7f-81-20-6b';
}

if (!empty($_GET['option'])) {
    $option = $_GET['option'];

    $buffer = array();
    switch ($option) {
        case "monitorT":
            $min = 100;
            $max = -20;
            $arrayT = array();

            $sql = "SELECT temp,time FROM $tableName WHERE uid='{$uid}' ORDER BY id DESC limit 45";
            foreach ($pdo->query($sql) as $row) {
                $buffer['mark'] = date('H:i', $row['time']);
                $buffer['value'] = $row['temp'];

                if ($min > $buffer['value']) {
                    $min = $buffer['value'];
                }

                if ($max < $buffer['value']) {
                    $max = $buffer['value'];
                }

                array_push($arrayT, $buffer);
            }

            unset($buffer['mark'], $buffer['value']);

            $buffer['min'] = $min;
            $buffer['max'] = $max;
            array_push($arrayT, $buffer);

            echo json_encode($arrayT);

            break;
        case "monitorH":
            $min = 99;
            $max = 0;
            $arrayH = array();

            $sql = "SELECT humi,time FROM $tableName WHERE uid='{$uid}' ORDER BY id DESC limit 45";
            foreach ($pdo->query($sql) as $row) {
                $buffer['mark'] = date('H:i', $row['time']);
                $buffer['value'] = $row['humi'];

                if ($min > $buffer['value']) {
                    $min = $buffer['value'];
                }

                if ($max < $buffer['value']) {
                    $max = $buffer['value'];
                }

                array_push($arrayH, $buffer);
            }

            unset($buffer['mark'], $buffer['value']);

            $buffer['min'] = $min;
            $buffer['max'] = $max;
            array_push($arrayH, $buffer);

            echo json_encode($arrayH);

            break;
        case "monitorS":
            $min = 9999;
            $max = 0;
            $arrayS = array();

            $sql = "SELECT smog,time FROM $tableName WHERE uid='{$uid}' ORDER BY id DESC limit 45";
            foreach ($pdo->query($sql) as $row) {
                $buffer['mark'] = date('H:i', $row['time']);
                $buffer['value'] = $row['smog'];

                if ($min > $buffer['value']) {
                    $min = $buffer['value'];
                }

                if ($max < $buffer['value']) {
                    $max = $buffer['value'];
                }

                array_push($arrayS, $buffer);
            }

            unset($buffer['mark'], $buffer['value']);

            $buffer['min'] = $min;
            $buffer['max'] = $max;
            array_push($arrayS, $buffer);

            echo json_encode($arrayS);

            break;
        case "monitorSCN":
            $min = 9999;
            $max = 0;
            $arrayS = array();

            $sql = "SELECT smog,time FROM $tableName WHERE uid='{$uid}' ORDER BY id DESC limit 45";
            foreach ($pdo->query($sql) as $row) {
                $buffer['mark'] = date('H:i', $row['time']);
                $buffer['value'] = AQI_CN($row['smog']);

                if ($min > $buffer['value']) {
                    $min = $buffer['value'];
                }

                if ($max < $buffer['value']) {
                    $max = $buffer['value'];
                }

                array_push($arrayS, $buffer);
            }

            unset($buffer['mark'], $buffer['value']);

            $buffer['min'] = $min;
            $buffer['max'] = $max;
            array_push($arrayS, $buffer);

            echo json_encode($arrayS);

            break;
        default:
            echo "当前时间：" . date('Y年m月d日 H:i', time()) . " 河南理工大学1420监测站";
    }
} else {
    echo "当前时间：" . date('Y年m月d日 H:i', time()) . " 河南理工大学1420监测站";
}

/**
 * @param $concentration
 * @return int
 */
function AQI_CN($concentration)
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