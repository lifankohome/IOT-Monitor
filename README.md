# monitor
物联网家庭环境监测：温度、湿度、空气质量可选

### Demo
http://hpu.lifanko.cn/monitor

### Screenshot
![Screenshot](https://github.com/lifankohome/IOT-Monitor/blob/master/screenshot.png?raw=true)

### Feature
 + Sensors Combine Freely
 + Upload One Data Every 2 Minutes
 + Wifi Module Pull/Push On Power
 + Android App Available
 + MicroUSB Power Plugin
 + Connect To Router Automatic

### WIFI Module
ESP8266

### MCU
STC12C5A60S2 32MHz

### Temperature Sensor
DS18B20

### humility Sensor
DHT11

### PM2.5 Sensor
益杉A4-CG

### Database
MYSQL database: lifanko

```
table monitor

CREATE TABLE `lifanko`.`monitor`
( `id` INT NOT NULL AUTO_INCREMENT ,
`uid` VARCHAR(32) NOT NULL DEFAULT '00-00-00-00-00-00' ,
`temp` VARCHAR(8) NOT NULL DEFAULT '0.0' ,
`humi` VARCHAR(8) NOT NULL DEFAULT '0' ,
`smog` VARCHAR(8) NOT NULL DEFAULT '0' ,
`time` VARCHAR(16) NOT NULL DEFAULT '0' ,
PRIMARY KEY (`id`))
ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT = '家庭环境监测分钟记录表';


table monitor_home

CREATE TABLE `lifanko`.`monitor_home`
( `id` INT NOT NULL AUTO_INCREMENT ,
`uid` VARCHAR(32) NOT NULL DEFAULT '00-00-00-00-00-00' ,
`name` VARCHAR(32) NOT NULL DEFAULT '家' ,
`time` VARCHAR(32) NOT NULL DEFAULT '0' ,
PRIMARY KEY (`id`))
ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT = '家庭环境监测所有设备记录表';
```

### Server
Windows Server 2008 IIS