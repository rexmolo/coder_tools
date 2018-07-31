/*
仅适用于BBC项目，用来创建custome目录下面对应的文件

phpStorm配置参数 $FileName$ $FileDirRelativeToProjectRoot$ $ContentRoot$
一定要按照以上变量传输，否则 go 接收的参数会有误
*/
package main

import (
	"errors"
	"flag"
	"fmt"
	"io"
	"os"
	"strings"
)

var args map[string]string

//main 启动会先执行init
func init() {
	flag.Parse()
	if flag.NArg() == 0 {
		panic("没有合适的参数")
	}
	args = make(map[string]string)

	args["fName"] = flag.Arg(0)
	args["fParentDir"] = flag.Arg(1)
	args["fRoot"] = flag.Arg(2)
}

func main() {

	var p Paths
	newPaths := p.newPaths()
	//fmt.Println("初始化的newPaths", p.newPaths())
	//os.Exit(1)
	newPaths.CreateDestDir()
	newPaths.CopyFile()
	fmt.Println("拷贝完毕")
}

type Paths struct {
	projectRoot, srcDir, destDir, fileName string
}

func (p *Paths) newPaths() *Paths {

	destDir := "custom/" + strings.Replace(args["fParentDir"], "app/", "", 1) + "/"
	return &Paths{srcDir: args["fParentDir"], destDir: destDir, fileName: args["fName"], projectRoot: args["fRoot"]}
}

//创建目标文件夹目录层
func (p Paths) CreateDestDir() {
	//目标文件是否存在
	destFile := p.projectRoot + "/" + p.destDir + "/" + p.fileName
	if checkFileExist(destFile) {
		fmt.Println()
		err := errors.New("[" + p.fileName + "]文件已经存在:")
		CheckError("", err)
	}

	pt := p.projectRoot
	i := 0
	for _, v := range strings.Split(p.destDir, "/") {
		pt = pt + "/" + v
		if !checkFileExist(pt) {
			err := os.Mkdir(pt, 0766)
			if err != nil {
				CheckError("创建文件夹错误:", err)
			}
			fmt.Printf("成功创建文件夹：%s \n", v)

			i++
		} //end checkFileExist

	}
	if i > 0 {
		fmt.Printf("----合计创建[%d]个文件夹---- \n", i)
	} else {
		fmt.Println("----无可创建的文件夹----")
	}
}

//拷贝源文件到目标处
func (p Paths) CopyFile() error {
	srcFile := p.projectRoot + "/" + p.srcDir + "/" + p.fileName
	destFile := p.projectRoot + "/" + p.destDir + "/" + p.fileName

	src, err := os.Open(srcFile)
	if err != nil {
		CheckError("打开源文件失败：", err)
	}
	defer src.Close()

	dst, err := os.OpenFile(destFile, os.O_WRONLY|os.O_CREATE, 0644)
	if err != nil {
		CheckError("打开写入文件失败：", err)
	}
	defer dst.Close()

	io.Copy(dst, src)

	return nil
}

//检查文件或者文件夹是否存在
func checkFileExist(file string) bool {
	_, err := os.Stat(file)
	if err != nil {
		return false
	}
	return true
}

func CheckError(message string, err error) {
	if err != nil {
		fmt.Println(message, err)
		os.Exit(1)
	}
}
