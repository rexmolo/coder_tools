#!/bin/bash

#php_v=$1
SLASH="/"



WORK_DIR="/Users/huangrong/www/"   #工作目录
PROJECT_DIR="$WORK_DIR$1"          #某个项目目录
CTIME=$(date "+%Y%m%d")            #生成日志时间目录
LOGS_DIR="$PROJECT_DIR/data/logs/$CTIME"  #拼接处日志目录

log_file="$LOGS_DIR$SLASH$2"  #需要监控的日志文件名称


function check() {
	if [[ ! -n $1 ]]; then
		echo "项目名称不能为空"
		exit 1
	fi

	if [[ ! -n $2 ]]; then
		echo "日志文件名字不能为空"
		exit 1
	fi
}

# 检查参数
check $1 $2

if [[ -d $PROJECT_DIR ]]; then
	if [[ -e  $log_file ]]; then
		cd $LOGS_DIR; tail -f $2;
	else
		echo "在 [$LOGS_DIR] 下未找到[$2] 日志文件, 有如下文件供您选择:"
		ls -al $LOGS_DIR
		exit 1
	fi
else
	echo "在 [$WORK_DIR] 下未找到[$1] 项目目录" 
	ls -al $WORK_DIR
	exit 1
fi


