;(function (global, undefined) {
	"use strict"
	var _global;
	var plugin = {
		add: function (n1, n2){}
	}

	//将插件对象暴露给全局对象
	//(0, eval)('this')，实际上(0,eval)是一个表达式，这个表达式执行之后的结果就是eval这一句相当于执行eval('this')的意思
	_global = (function() {return this || (0, eval)('this');}());
	!('plugin' in _global) && (_global.plugin = plugin);
}());