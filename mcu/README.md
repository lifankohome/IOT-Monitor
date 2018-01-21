# monitor
物联网家庭环境监测：温度、湿度、空气质量可选

### Demo
http://hpu.lifanko.cn/monitor

### Build Output

#### Tool Versions:
```
IDE-Version: μVision V5.14.2
Copyright (C) 2015 ARM Ltd and ARM Germany GmbH. All rights reserved.
License Information: lee lifanko, lifanko, LIC=ZBDAR-KRVDZ-F6EQ4-FH2S2-AINF4-V35SD

Tool Versions:
Toolchain:       PK51 Prof. Developers Kit  Version: 9.54
Toolchain Path:  D:\Keil_v5\C51\BIN
C Compiler:      C51.exe V9.54
Assembler:       A51.exe V8.02c
Linker/Locator:  BL51.exe V6.22
Library Manager: LIB51.exe V4.30.1.0
Hex Converter:   OH51.exe V2.7.0.0
CPU DLL:         S8051.DLL V3.100.0.0
Dialog DLL:      DP51.DLL V2.62.0.1
```

#### Output:
```
Rebuild target 'Target 1'
assembling STARTUP.A51...
compiling 1008.c...
linking...
*** WARNING L15: MULTIPLE CALL TO SEGMENT
    SEGMENT: ?PR?_DELAYMS?1008
    CALLER1: ?C_C51STARTUP
    CALLER2: ?PR?TIMER0?1008
Program Size: data=17.4 xdata=390 code=4213
creating hex file from "1008"...
"1008" - 0 Error(s), 1 Warning(s).
```
