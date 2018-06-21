# shell-cmd

> 收集了一些日常重复工作，写的 `shell` ，放在这里算是个记录。
>
> 如果你想拿去用的话，可能需要把目录变量修改成你自己的
>
> 🙅不要怪我洋里洋气的，在下面还写一些英文，我只是想锻炼一下自己的英语而已，相信这么简单的单词，你应该看的懂 😆

### [plogs](https://github.com/mh1988/shell-cmd/blob/master/cmd/plogs.sh) 

> 每次debug的时候需要监控日志，每次都要 `cmd;tail -f file.php `, 写了这个命令，可以帮我快速进入 `tail` 日志文件模式

##### usage

`plogs project logfile.php`

`project ` ： the name of the directory of the project

`logfile` ：the name of the log file