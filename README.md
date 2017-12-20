# monitor
物联网家庭环境监测

### WIFI Module
ESP8266

### MCU
STC12C5A60S2 32MHz

### Temperature Sensor
DS18B20

### PM2.5 Sensor
益杉A4-CG

### Database
MYSQL lifanko

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


table monitor_h

CREATE TABLE `lifanko`.`monitor_h`
( `id` INT NOT NULL AUTO_INCREMENT ,
`uid` VARCHAR(32) NOT NULL DEFAULT '00-00-00-00-00-00' ,
`temp` VARCHAR(8) NOT NULL DEFAULT '0.0' ,
`humi` VARCHAR(8) NOT NULL DEFAULT '0' ,
`smog` VARCHAR(8) NOT NULL DEFAULT '0' ,
`time` VARCHAR(32) NOT NULL DEFAULT '0' ,
PRIMARY KEY (`id`))
ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT = '家庭环境监测小时平均值记录表';


table monitor_d

CREATE TABLE `lifanko`.`monitor_d`
( `id` INT NOT NULL AUTO_INCREMENT ,
`uid` VARCHAR(32) NOT NULL DEFAULT '00-00-00-00-00-00' ,
`temp` VARCHAR(8) NOT NULL DEFAULT '0.0' ,
`humi` VARCHAR(8) NOT NULL DEFAULT '0' ,
`smog` VARCHAR(8) NOT NULL DEFAULT '0' ,
`time` VARCHAR(32) NOT NULL DEFAULT '0' ,
PRIMARY KEY (`id`))
ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT = '家庭环境监测全天平均值记录表';
```

### Server
Windows Server 2008 IIS