#include "STC12C5A60S2.H"

#define uchar unsigned char
#define S2REN 0x10
#define S2TI  0x02
#define S2RI  0x01

sbit relay = P2^5;
sbit linkled = P0^3;
sbit key = P3^2;

#define PIN_18B20 P27  //DS18B20通信引脚
#define DHT P26		//湿度传感器引脚

code uchar cmdIP[] = "AT+CIFSR\r\n";
code uchar cmdAiLink[] = "AT+CWSMARTSTART=2\r\n";	//AiLink
code uchar cmdRelease[] = "AT+CWSTOPSMART\r\n";	//释放内存
code uchar cmdMac[] = "AT+CIPSTAMAC?\r\n";	//获取MAC
code uchar cmdRunMode[] = "AT+CWMODE_DEF=1\r\n";	//Station模式
code uchar cmdConMode[] = "AT+CIPMUX=0\r\n";	//单连接模式
code uchar cmdTCP[] = "AT+CIPSTART=\"TCP\",\"hpu.lifanko.cn\",81\r\n";	//建立TCP连接
code uchar cmdTCPLen[] = "AT+CIPSEND=100\r\n";	//TCP请求长度
uchar cmdTCPURL[] = "GET /bingo.php?opt=mon&mac=00-00-00-00-00-00&T=00.0&S=0000&H=00 HTTP/1.1\r\n Host: eeec.hpu.edu.cn\r\n\r\n";	//发起请求

uchar A4[16] = {0};
uchar A4D0 = 0,A4D1 = 0;

uchar String[245] = "";
uchar posString = 0, lenString = 0, A4Pos = 0;
bit sendFlag = 0, connected = 0;  //发送完毕标识符，已连接标识符
uchar msgT = 0, failT = 0;

unsigned int total = 0;	   //颗粒物浓度

uchar today = 0;

void UartInit(void);
void Uart2Init(void);
void DelayCharUsart();
void numUsart(uchar num);
void checkStr();

void sendChar(uchar Value);
void sendAll(uchar *pValue);
void Uart2Init(void);
void Uart2Send(unsigned char *Rec);

void Timer0Init(void);
void delayMs(uchar ms);
void delay_us(unsigned int n);
void getMac();
void getData();
void smartLink();

bit Start18B20(void);
bit Get18B20Temp(unsigned int *temp);

uchar dht11();

void A4Data();

void main(){
	UartInit();
	Uart2Init();
	Timer0Init();
	EA = 1;

	delayMs(20);

	if(key){
		linkled = 0;
	}else{
		smartLink();

		while(!connected){
			linkled = 0;
			delayMs(5);//延时0.5S
			linkled = 1;
			delayMs(5);//延时0.5S
			linkled = 0;
			delayMs(5);//延时0.5S
			linkled = 1;
			delayMs(5);//延时0.5S
			linkled = 0;
			delayMs(5);//延时0.5S
			linkled = 1;
			delayMs(5);//延时0.5S
		}

		sendAll(cmdRelease);//发送命令
		delayMs(10);//延时1S
	}

	Start18B20();

	while(!connected){
		sendAll(cmdIP);
		delayMs(20);delayMs(20);delayMs(20);delayMs(20);delayMs(20);//延时10S，开机获取IP
	}
	linkled = 1;

	getMac();	//获取MAC
	sendAll(cmdRunMode);//发送命令
	delayMs(10);//延时1S
	sendAll(cmdConMode);//发送命令
	delayMs(5);//延时0.5S
	Uart2Send("Ready!");
	delayMs(5);//延时0.5S
	while(1){
		getData();
		delayMs(20);delayMs(20);delayMs(20);delayMs(20);delayMs(20); //延时10s
        delayMs(20);delayMs(20);delayMs(20);delayMs(20);delayMs(20); //延时10s
        delayMs(20);delayMs(20);delayMs(20);delayMs(20);delayMs(20); //延时10s
        delayMs(20);delayMs(20);delayMs(20);delayMs(20);delayMs(20); //延时10s
        delayMs(20);delayMs(20);delayMs(20);delayMs(20);delayMs(20); //延时10s
        delayMs(20);delayMs(20);delayMs(20);delayMs(20);             //延时7s

		if(connected){
			linkled = 1;
			failT++;

			if(failT > 3){	//3次查询失败则重启
				connected = 0;	//改变连接状态
				failT = 0;

				relay = 0;
				delayMs(20);
				relay = 1;
			}
		}else{
			linkled = 0;
		}
		while(!connected){
			delayMs(20);//延时2S，开机获取IP
		}
	}
}
void A4Data(){
	total = A4D1 * 256 + A4D0;
	if(total>1000){
		;	  //BUG，用空操作覆盖
	}else if(total>999){
//		cmdTCPURL[54] = total/1000+'0';
//		cmdTCPURL[55] = total/100%10+'0';
//		cmdTCPURL[56] = total/10%10+'0';
//		cmdTCPURL[57] = total%10+'0';
		;	  //BUG，用空操作覆盖
	}else if(total>99){
		cmdTCPURL[54] = '0';
		cmdTCPURL[55] = total/100%10+'0';
		cmdTCPURL[56] = total/10%10+'0';
		cmdTCPURL[57] = total%10+'0';
	}else if(total>9){
		cmdTCPURL[54] = '0';
		cmdTCPURL[55] = '0';
		cmdTCPURL[56] = total/10%10+'0';
		cmdTCPURL[57] = total%10+'0';
	}else{
		cmdTCPURL[54] = '0';
		cmdTCPURL[55] = '0';
		cmdTCPURL[56] = '0';
		cmdTCPURL[57] = total%10+'0';
	}
}
uchar dht11(){
	uchar dhtTemp = 0;
	uchar dhtData = 0;
	uchar i = 0;
	uchar expire = 2;

	DHT = 0;
	delay_us(20000);
	DHT = 1;
	delay_us(40);

	if(!DHT){
		while(!DHT && expire++);
		expire = 2;
		while(DHT && expire++);
		expire = 2;

		for(i=0;i<8;i++) {
			while(!DHT && expire++);
			expire = 2;

			delay_us(50);

			if(DHT){
				dhtTemp = 1;
			}else{
				dhtTemp = 0;
			}

			while(DHT && expire++);
			if(expire==1) break;	//error happen

			dhtData<<=1;
			dhtData |= dhtTemp;
		}
	}

	return dhtData;
}
void smartLink(){	//启动智能连接
	delayMs(5);
	sendAll(cmdAiLink);//发送命令，此时会断开已有连接
	delayMs(5);
}
void getData(){		//模块查询数据
	uchar n = 0;
	unsigned int intT, decT, temp;
	uchar Temp[4];
	uchar dhtBuffer;

	Start18B20();

	if(Get18B20Temp(&temp)){
		if(temp>0xF000){
			temp = ~temp + 1;

			intT = temp >> 4;		//乘0.0625,得到温度
			decT = temp & 0xF;		//获取低四位（小数部分）
			decT = ((decT * 100) / 16) + 0.5;

			if(intT>9){
				Temp[0] = '-';
				Temp[1] = '0' + intT/10;
				Temp[2] = '0' + intT%10;
				Temp[3] = '.';
			}else{
				Temp[0] = '-';
				Temp[1] = '0'+intT;
				if(decT>9){
					Temp[2] = '.';
					Temp[3] = '0' + decT/10;
				}else{
					Temp[2] = '.';
					Temp[3] = '0';
				}
			}
		}else{
			intT = temp >> 4;		//乘0.0625,得到温度
			decT = temp & 0xF;		//获取低四位（小数部分）
			decT = ((decT * 100) / 16) + 0.5;

			if(intT>9){
				Temp[0] = '0'+intT/10;
				Temp[1] = '0'+intT%10;
				if(decT>9){
					Temp[2] = '.';
					Temp[3] = '0'+decT/10;
				}else{
					Temp[2] = '.';
					Temp[3] = '0';
				}
			}else{
				Temp[0] = '0' + intT%10;
				if(decT>9){
					Temp[1] = '.';
					Temp[2] = '0'+decT/10;
					Temp[3] = '0'+decT%10;
				}else{
					Temp[1] = '.';
					Temp[2] = '0';
					Temp[3] = '0'+decT;
				}
			}
		}
	}

	cmdTCPURL[47] = Temp[0];	//填入温度
	cmdTCPURL[48] = Temp[1];
	cmdTCPURL[49] = Temp[2];
	cmdTCPURL[50] = Temp[3];

	dhtBuffer = dht11();

	if(dhtBuffer>9){
		cmdTCPURL[61] = dhtBuffer/10 + '0';
		cmdTCPURL[62] = dhtBuffer%10 + '0';
	}else{
		cmdTCPURL[61] = '0';
		cmdTCPURL[62] = dhtBuffer%10 + '0';
	}

	A4Data(); 					//填入空气质量

	delayMs(5);//延时0.5S
	sendAll(cmdTCP);//发送命令
	delayMs(5);//延时0.5S
	sendAll(cmdTCPLen);//发送命令
	delayMs(5);//延时0.5S
	sendAll(cmdTCPURL);//发送命令
	delayMs(10);//延时1S

	if(lenString>15){
		failT = 0;		//收到超过15个字符的信息就将失败次数置零
		lenString = 0;
	}

}
void getMac(){ 	//获取模块Mac地址
	uchar i = 0;
	sendAll(cmdMac);//发送命令
	delayMs(10);//延时1S
	for(i=0;i<lenString;i++){
		if(String[i]=='M'){
			if(String[i+1]=='A'){
				if(String[i+2]=='C'){
					if(String[i+3]==':'){
						cmdTCPURL[27] = String[5+i];
						cmdTCPURL[28] = String[6+i];

						cmdTCPURL[30] = String[8+i];
						cmdTCPURL[31] = String[9+i];

						cmdTCPURL[33] = String[11+i];
						cmdTCPURL[34] = String[12+i];

						cmdTCPURL[36] = String[14+i];
						cmdTCPURL[37] = String[15+i];

						cmdTCPURL[39] = String[17+i];
						cmdTCPURL[40] = String[18+i];

						cmdTCPURL[42] = String[20+i];
						cmdTCPURL[43] = String[21+i];

						break;
					}
				}
			}
		}
	}
	lenString = 0;	//数据读取后复位长度
}
void delayMs(uchar ms){	 //延时，时长： ms*0.1s
	uchar i = 0, j = 0, k = 0;
	for(i=0;i<ms*10;i++){
		for(j=0;j<200;j++){
			for(k=0;k<200;k++);
		}
	}
}
void delay_us(unsigned int n)
{
    extern void _nop_(void);
    register unsigned char i = n, j = (n>>8);
    _nop_(); _nop_(); _nop_();
    if ((--i) | j)
    {
        do
        {
            _nop_(); _nop_(); _nop_(); _nop_(); _nop_(); _nop_(); _nop_(); _nop_();
            if (0xFF == (i--)) j--; else {_nop_(); _nop_(); _nop_(); _nop_(); _nop_(); _nop_();};
        } while (i | j);
    }
}
void checkStr(){	//检查是否连接网络成功，检查收到的字符数目
	uchar i = 0;
	numUsart(posString);	//显示输入的字符数目
	lenString = posString;
	posString = 0;

	if(!connected){
		if(lenString==13){
			for(i=0;i<lenString;i++){
				if(String[i]=='G'){
					if(String[i+1]=='O'){
						if(String[i+2]=='T'){
							if(String[i+4]=='I'){
								if(String[i+5]=='P'){
									connected = 1;	//连接网络成功

									break;
								}
							}
						}
					}
				}
			}
		}else{		//应对单片机重启后状况
			if(lenString==76){
				connected = 0;	//+CIFSR:STAIP,"0.0.0.0"
			}else if(lenString>80){
				connected = 1;	//+CIFSR:STAIP,"192.168.0.110"
			}
		}
	}else if(lenString>180){
		for(i=100;i<lenString;i++){
			if(String[i]=='D'){
				if(String[i+1]=='a'){
					if(String[i+2]=='t'){
						if(String[i+3]=='e'){
							if(String[i+4]==':'){
								if(today == 0){
									today = String[i+12];	//开机后同步今天的日期
								}else if(today != String[i+12]){	//如果服务器日期和已有的日期不同则说明跨天，也就是晚上12点，此时进行系统重启
									today = String[i+12];	//保存当前日期

									connected = 0;	//改变连接状态
									failT = 0;		//重置失败次数（无必要）

									relay = 0;
									delayMs(20);
									relay = 1;
								}

								break;
							}
						}
					}
				}
			}
		}
	}
}
void numUsart(uchar num){	//将串口1收到的字符数目通过串口2发出来
	uchar ge = 0, shi = 0, bai = 0;
	ge = num % 10;
	shi = num / 10 % 10;
	bai = num / 100 % 10;
	if(bai>0){
		S2BUF = bai + '0';DelayCharUsart();
		S2BUF = shi + '0';DelayCharUsart();
		S2BUF = ge + '0';DelayCharUsart();
	}else if(shi>0){
		S2BUF = shi + '0';DelayCharUsart();
		S2BUF = ge + '0';DelayCharUsart();
	}else{
		S2BUF = ge + '0';DelayCharUsart();
	}
	S2BUF = '\n';
}
void DelayCharUsart(){ 	//串口发送一个字符专用延时
	uchar i = 0, j = 0;
	for(i=0;i<100;i++){
		for(j=0;j<50;j++);
	}
}
void UartInit(void){		//4800bps@32.0000MHz
	PCON &= 0x7F;		//波特率不倍速
	SCON = 0x50;		//8位数据,可变波特率
	AUXR |= 0x40;		//定时器1时钟为Fosc,即1T
	AUXR &= 0xFE;		//串口1选择定时器1为波特率发生器
	TMOD &= 0x0F;		//清除定时器1模式位
	TMOD |= 0x20;		//设定定时器1为8位自动重装方式
	TL1 = 0x30;		//设定定时初值
	TH1 = 0x30;		//设定定时器重装值
	ET1 = 0;		//禁止定时器1中断
	TR1 = 1;		//启动定时器1
	ES = 1;
}
void USART() interrupt 4{
	if(TI){
		TI = 0;
		sendFlag = 0;        //清标志位
	}
	if(RI){
		RI = 0;
		String[posString++] = SBUF;
		msgT = 0;
		TR0 = 1;	//开始计时（确定是否接收完成）
	}
}
/*串口2配置*/
void Uart2Init(void){		//9600bps@32.0000MHz
	AUXR &= 0xF7;		//波特率不倍速
	S2CON = 0x50;		//8位数据,可变波特率
	AUXR |= 0x04;		//独立波特率发生器时钟为Fosc,即1T
	BRT = 0x98;			//设定独立波特率发生器重装值
	AUXR |= 0x10;		//启动独立波特率发生器
	S2CON |= S2REN;		//允许接收
	IE2 |= 0x01;		//允许中断
}
/*串口2发送字符串*/
void Uart2Send(unsigned char *Rec){
	while(*Rec){
		S2BUF = *Rec++;
		DelayCharUsart();
	}
}
/*串口2中断*/
void Uart2_Interrupt() interrupt 8 using 1{
	if(S2CON & S2RI)											//接收到字节
		if(S2BUF == 0x3D){
			A4Pos = 0;
			A4D1 = A4[5];
			A4D0 = A4[6];
		}
		A4[A4Pos++] = S2BUF;
		S2CON &= ~S2RI;
	if(S2CON & S2TI)											//字节发送完毕
		S2CON &= ~S2TI;										//手动清零发送中断标志位
}
void sendChar(uchar Value){  //发送一个字节数据  
	SBUF = Value;       
	sendFlag = 1;       //设置发送标志位,发一字节就置位  
	while(sendFlag);    //直到发完数据,将sendFlag清零后,才退出sendChar函数  
}   
void sendAll(uchar *pValue){ 	//发送一组数据   
	while((*pValue) != '\0'){   //如果没有发送完毕就继续发    
		sendChar(*pValue);      //发送1字节数据  
		pValue++;               //向下1个字节  
	}  
} 
void Timer0Init(void){		//1毫秒@32.0000MHz
	AUXR |= 0x80;		//定时器时钟1T模式
	TMOD &= 0xF0;		//设置定时器模式
	TMOD |= 0x01;		//设置定时器模式
	TL0 = 0x00;		//设置定时初值
	TH0 = 0x83;		//设置定时初值
	TF0 = 0;		//清除TF0标志
	TR0 = 1;		//定时器0开始计时
	ET0 = 1;		//开中断
}
void Timer0() interrupt 1{
	TL0 = 0x00;		//设置定时初值
	TH0 = 0x83;		//设置定时初值
	if(msgT++ > 10){ //串口停止10ms认为当前接收完毕
		TR0 = 0 ;	//停止计数
		checkStr();
	}
}

bit Get18B20Ack(void){
    bit ack;
    
    PIN_18B20 = 0;     /* 产生500us复位脉冲 */
    delay_us(500);
    PIN_18B20 = 1;
    delay_us(60);      /* 延时60us */
    ack = PIN_18B20;   
    while(!PIN_18B20); /* 等待存在脉冲结束 */
    
    return ack;
}

void Write18B20(uchar dat){
    uchar i;
      
    for (i = 0x01;i != 0;i <<= 1){  /* 低位在先，依次移出8个bit */
        PIN_18B20 = 0;             /* 产生2us低电平脉冲 */
        delay_us(2);
        if ((i & dat) == 0)        /* 输出该bit值 */
            PIN_18B20 = 0;
        else
            PIN_18B20 = 1;
        delay_us(60);             
        PIN_18B20 = 1;               /* 释放通信引脚 */
    } 
}

uchar Read18B20(void){
    uchar dat, i;
      
    for (i = 0x01;i != 0;i <<= 1){  /* 低位在先，依次采集8个bit */
        PIN_18B20 = 0;             /* 产生2us低电平脉冲 */
        delay_us(2);
        PIN_18B20 = 1;             /* 结束低电平脉冲，等待18B20输出数据 */
        delay_us(2);
        if (!PIN_18B20)            /* 读取通信引脚上的值 */
            dat &= ~i;
        else
            dat |= i;
        delay_us(60);        
    }  
    return dat;
}
bit Start18B20(void){
    bit ack;
    
    ack = Get18B20Ack();   /* 执行总线复位，并获取18B20应答 */
    if (ack == 0){          /* 如18B20正确应答，则启动一次转换 */
        Write18B20(0xCC);  /* 跳过ROM操作 */
        Write18B20(0x44);  /* 启动一次温度转换 */
    }
    return ~ack;           /* ack==0表示操作成功，所以返回值对其取反 */
}
bit Get18B20Temp(unsigned int *temp){
    bit ack;
    uchar LSB, MSB;         /* 16bit温度值 -- 12 位的整数部分和4位的小数部分 
	                          * 整数部分的5位又代表符号            *********/   
    ack = Get18B20Ack();    /* 执行总线复位，并获取18B20应答 */
    if (ack == 0){
        Write18B20(0xCC);   /* 跳过ROM操作 */
        Write18B20(0xBE);   /* 发送读命令  */
        LSB = Read18B20();  /* 读温度值的低字节 */
        MSB = Read18B20();  /* 读温度值的高字节 */
        *temp = ((unsigned char)MSB << 8) + LSB;  /* 合成为16bit整型数 */
    }
	return ~ack;             /* ack==0表示操作应答，所以返回值为其取反值 */
}