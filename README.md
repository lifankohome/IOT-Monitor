# monitor
物联网家庭环境监测：温度、湿度、空气质量可选

### Demo
http://hpu.lifanko.cn/monitor

### Android APP

http://7y9g1c.com1.z0.glb.clouddn.com/2fbd4ca10f428afcaa0c7426ce404271_d Version-0.1.9.5
http://cdn.lifanko.cn/1130290a1741a89f34e935dbf2a2f623.apk Version-0.1.9

### Screenshot
![Screenshot](https://github.com/lifankohome/IOT-Monitor/blob/master/screenshot.png?raw=true)

### Feature
 + Sensors Combine Freely
 + Upload One Data Every 2 Minutes
 + Wifi Module Pull/Push On Power
 + Android App Available
 + MicroUSB Power Plugin
 + Connect To Router Automatic

### Hardware
 > ##### WIFI Module
 > ESP8266
 > ##### MCU
 > STC12C5A60S2 32MHz
 > ##### Temperature Sensor
 > DS18B20
 > ##### Humility Sensor
 > DHT11
 > ##### PM2.5 Sensor
 > 益杉A4-CG

### Database
MYSQL database: lifanko

```
table monitor

CREATE TABLE `lifanko`.`monitor`
( `id` INT NOT NULL AUTO_INCREMENT ,
`uid` VARCHAR(32) NOT NULL DEFAULT '00-00-00-00-00-00' ,
`temp` DECIMAL(3,1) NOT NULL DEFAULT '0.0' ,
`humi` int(2) NOT NULL DEFAULT '0' ,
`smog` int(4) NOT NULL DEFAULT '0' ,
`time` VARCHAR(16) NOT NULL DEFAULT '0' ,
PRIMARY KEY (`id`))
ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT = '家庭环境监测分钟记录表';


table monitor_home

CREATE TABLE `lifanko`.`monitor_home`
( `id` INT NOT NULL AUTO_INCREMENT ,
`uid` VARCHAR(32) NOT NULL DEFAULT '00-00-00-00-00-00' ,
`name` VARCHAR(32) NOT NULL DEFAULT '家' ,
`time` VARCHAR(32) NOT NULL DEFAULT '0' ,
PRIMARY KEY (`id`，`uid`))
ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT = '家庭环境监测所有设备记录表';
```

### PCB & Components
![PCB](http://hpu.lifanko.cn/static/monitor/pcb.png)

### How To Setup
 + 使用稳定的USB供电设备向设备供电，如果供电后白色LED灯异常闪烁或继电器抖动，则说明供电设备质量差，需要更换。
 + 按下设备上的唯一按键并插入电源，然后蓝色LED灯开始闪烁，如果没有闪烁，则需进行重复操作。
 + 将手机连接到wifi，关注微信公众号：“安信可科技”，进入公众号点击右下角菜单：“wifi配置”，并按照说明进行操作（请勿距离路由器过远，信号质量在75%以上）；手机提示“配置完成”或设备上蓝色LED灯亮起则说明设备成功联网，如果提示失败则需要重复操作。
 + 设备联网后会自动在云端注册，此时需要激活设备，告诉云端开始对设备数据进行统计——用电脑访问：http://hpu.lifanko.cn/monitor/login ，在设备编号一栏输入设备上所粘贴的17位编号。
 + 第二栏“家的名字”随意填写（注意：名字确定后无法修改，用于对自己的设备进行区分，以后登录也需要用到家的名字，请一定要记在本子上），比如在家里客厅和卧室各安装了一个，则可以起名叫‘最帅的小明家的大客厅’和‘最温馨的小明家的卧室’。最终输入的名字会在查看数据的页面右上角显示：

　　![home-name](http://hpu.lifanko.cn/static/monitor/home-name.jpg)

 + 家的名字输入完毕后设备即安装完成！访问：http://hpu.lifanko.cn/monitor 即可查看数据，移动端查看数据推荐使用APP（在数据查看页面左上角下载）。
 + 如果不想安装APP也可以使用浏览器查看数据——在地址栏输入http://hpu.lifanko.cn/monitor/xx-xx-xx-xx-xx-xx ，把xx替换为设备编号即可。

#### 设备工作特性
 + 网络波动可能会造成设备掉线，表现为蓝色LED关闭，但设备会在3分钟内重新尝试连接
 + 为保障设备的稳定性，设备会在每天晚上12点强制进行重启
 + 重启耗费时间：约3.5秒
 
#### 设备固件更新
 + monitor-v1.0.hex 12.075Kb 2018/01/01 - http://cdn.lifanko.cn/monitor-v1.0.hex
##### 如何进行固件更新：
 + 移除PCB上的跳线帽和WiFi模块，使用USB转串口工具连接最第三纵排两个排针
 + 使用STC-ISP软件加载固件并点击‘立即下载’
 + 将USB插入PCB，STC-ISP提示下载完成
 + 连接跳线帽和WiFi模块

#### 更换WiFi模块
如果您的WiFi模块发生损坏，可以自行购买对应的型号并安装，但是新的WiFi对应不同的设备地址，您的旧设备的数据无法和新设备进行拼接。
##### 更换步骤：
 + 移除PCB上的跳线帽，使用USB转串口工具连接最左边两个排针
 + 打开电脑串口工具选择波特率为115200bps
 + 发送AT指令修改WiFi波特率为4800bps
 + 修改工作模式为STATION模式
 + 查询MAC地址并记录
 + 移除USB转串口，连接跳线帽
 + 配置联网
 + 用新的mac重新配置&登录设备
 
#### AT指令
每条AT指令后应追加换行（结束符），否则会返回发送内容。

```
AT+UART_DEF=4800,8,1,0,0
AT+CWMODE_DEF=1
AT+CIPSTAMAC?
```
