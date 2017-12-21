<?php
/**
 * Created by PhpStorm.
 * User: lifanko
 * Date: 2016/12/20
 * Time: 13:10
 */
try {   //创建pdo连接对象，全局使用
    $pdo = new PDO("mysql:host=localhost;dbname=lifanko", "root", "");
} catch (PDOException $e) {
    die("Unable to connect to the database, please contact manager: lifankohome@163.com");
}
$pdo->query("set names utf8");

$tableName = "monitor";

$num = 0;
$arrayTM[] = "";
$arraySM[] = "";
$arraySMCN[] = "";
$arraySMUS[] = "";
$arrayTimeM[] = "";

$sql = "SELECT temp, humi, smog, time FROM $tableName ORDER BY id desc limit 30";
foreach ($pdo->query($sql) as $row) {
    $arrayTM[$num] = round($row['temp'], 1);
    $arraySM[$num] = (int)$row['smog'];
    $arraySMCN[$num] = AQI_CN($row['smog']);
    $arraySMUS[$num] = AQI_US($row['smog']);
    $arrayTimeM[$num] = date('H:i', $row['time']);
    $num++;
}
$jsonTM = json_encode(array_reverse($arrayTM));
$jsonSM = json_encode(array_reverse($arraySM));
$jsonSMCN = json_encode(array_reverse($arraySMCN));
$jsonSMUS = json_encode(array_reverse($arraySMUS));
$jsonTimeM = json_encode(array_reverse($arrayTimeM));

$strTM = str_replace("\"", "", $jsonTM);//json输出时用双引号包裹，但是highCharts.js无法解析，这里把双引号去掉
$strSM = str_replace("\"", "", $jsonSM);
$strSM_CN = str_replace("\"", "", $jsonSMCN);
$strSM_US = str_replace("\"", "", $jsonSMUS);

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

/**
 * @param $concentration
 * @return int
 */
function AQI_US($concentration)
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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>家庭环境监测系统 - 河南理工大学</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon"/>
    <script src="js/jquery-2.1.1.min.js"></script>
    <script src="js/highCharts.js"></script>
    <style>
        body {
            margin: 0;
            overflow-y: auto;
            font-family: Signika, 'Microsoft JhengHei Light';
            background-image: linear-gradient(to top, #453a94 0%, #c0a3dd 24%, #e2c9cc 30%, #e7627d 46%, #b8235a 59%, #801357 71%, #3d1635 84%, #1c1a27 100%);
        }

        .download {
            font-size: 16px;
            color: whitesmoke;
            margin-left: 1pc;
            background-color: #00A2D4;
            padding: 0 10px;
            border-radius: 3px;
            position: relative;
        }

        .download img{
            position: absolute;
            margin-top: -15pc;
            margin-left: -8.1pc;
            transition: all 0.5s 0s;
			z-index: 1;
        }

        .download:hover {
            cursor: pointer;
            background-color: #333;
        }
        .download:hover img{
            margin-top: 2.6pc;
        }
    </style>
</head>
<body>
<div style="width: 96%;margin: 0 auto">
    <h1 style="color: #ffad43">
        家庭环境监测系统
        <a class="download">APP下载<img src="app.png" style="width: 200px"></a>
        <span style="font-size: 13px;margin-left: 1pc">数据源：理工大1420监测中心</span>
        <a href="#" style="float: right;font-size: 14px;color: whitesmoke;line-height: 42px">登录我的家庭</a>
    </h1>
    <div id="containerSM" style="width: 100%"></div><br>
    <div id="containerTM" style="width: 100%"></div>
    <p style="font-size: 12px;color: #fff">&copy; Copyright lifanko 2017 December</p>
</div>
<script>
    $(function () {
        $('#containerSM').highcharts({
            chart: {
                type: 'line'
            },
            title: {
                text: "60分钟内空气质量指数（<a href='https://www.zhihu.com/question/22206538' style='color: #48a7ff;text-decoration: none'>AQI</a>）",
                useHTML: true,
                style: {
                    fontFamily: "Signika, 'Microsoft JhengHei Light'",
                    fontSize: '20px',
                    fontWeight: 'bold',
                    color: '#006cee'
                }
            },
            subtitle: {
                text: ''
            },
            xAxis: {
                categories: <?php print_r($jsonTimeM); ?>
            },
            yAxis: {
                floor: 0,
                ceiling: 500,
                title: {
                    text: 'PM2.5空气质量指数 / 1'
                }
            },
            plotOptions: {
                line: {
                    dataLabels: {
                        enabled: true
                    },
                    enableMouseTracking: false
                }
            },
            series: [{
                name: '国标AQI',
                data: <?php print_r($strSM_CN); ?>
            }, {
                name: '美标AQI',
                data: <?php print_r($strSM_US); ?>
            }, {
                name: 'PM2.5浓度值（ug/m³）',
                data: <?php print_r($strSM); ?>
            }],
            credits: {
                text: 'Innovative Developing Platform',             // 显示的文字
                href: 'http://eeec.hpu.edu.cn/IDP/',      // 链接地址
                style: {                            // 样式设置
                    cursor: 'pointer',
                    color: '#777'
                }
            }
        });
        $('#containerTM').highcharts({
            chart: {
                type: 'line'
            },
            title: {
                text: '60分钟内气温曲线',
                style: {
                    fontFamily: "Signika, 'Microsoft JhengHei Light'",
                    fontSize: '20px',
                    fontWeight: 'bold',
                    color: '#006cee'
                }
            },
            subtitle: {
                text: ''
            },
            xAxis: {
                categories: <?php print_r($jsonTimeM); ?>
            },
            yAxis: {
                floor: -10,
                ceiling: 50,
                title: {
                    text: '温度 / ℃'
                }
            },
            plotOptions: {
                line: {
                    dataLabels: {
                        enabled: true
                    },
                    enableMouseTracking: false
                }
            },
            series: [{
                name: '数字温度传感器（DS18B20）',
                data: <?php print_r($strTM); ?>
            }],
            credits: {
                text: 'Innovative Developing Platform',             // 显示的文字
                href: 'http://eeec.hpu.edu.cn/IDP/',      // 链接地址
                style: {                            // 样式设置
                    cursor: 'pointer',
                    color: '#777'
                }
            }
        });
    });
</script>
</body>
</html>
