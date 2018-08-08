/**
 * 通用方法
 * 
 * 依赖   jQuery|__PUBLIC__/js/jquery-1.11.1.min.js
 *        layer |__PUBLIC__/Admin/js/plugins/layer/layer.min.js
 *        Validform|__PUBLIC__/js/plugins/Validform/Validform_v5.3.1_min.js 
 *                 |__PUBLIC__/css/plugins/Validform/validform.css
 */

//返回按钮
function goBack(){
    window.history.back();
}
//跳转
function winOpen(url){
    //console.log(url);
    //return;
    window.location.href = url;
}
//提示
function msg(txt,callback,icon,second,shade){
    var options ={
        skin:"layui-layer-hui",
        // shade: [0.2,'#000']
    };

    if(shade){
        options.shade = [0.2,'#000'];
    }
    
    if(!second) 
        second = 1.5;
    if(icon) 
        options.icon = icon;
    options.time = second*1000;  
    layer.msg(txt, options,callback);  
}

//确认框
function confirm(msg,confirm,cancel){
    var index = layer.confirm(msg, {
      btn: ['确定','取消']
    }, function(){
        if(typeof(confirm) == 'function')
            confirm();
        layer.close(index);
    }, function(){
        if(typeof(cancel) == 'function')
            cancel();
    });
}

//绑定删除操作
function bind_delete_action(selector,urlAttrName,message){
    var attrName = urlAttrName ? urlAttrName : 'url';
    $(selector).click(function(){
        var url = $(this).attr(attrName);
        message = message == undefined  ? '确定删除？' : message;
        confirm(message, function(){
            var params = {
                url : url,
                callback : function(data){
                    var msgCallBack;
                    if(data.status == 1){
                        msgCallBack = function(){
                            window.location.reload();
                        }
                    }
                    msg(data.info,msgCallBack);
                }
            }
            ajax(params);
        });
    })
}

//异步请求
function ajax(params){
    var index = layer.load(0, {
      shade: [0.2,'#000'] //0.1透明度的白色背景
    });
    $.post(params.url,params.params,function(data){
        layer.close(index);
        if(typeof(params.callback) == 'function'){
            params.callback(data);
        }
    },'json');
}

//表单验证
function checkForm(selector,params){
    var opt = {
        ajaxPost : true,
        beforeSubmit : function(form){},
        callback : function(data){
            var callback = undefined;
            if(data.status == 1 && typeof(params.msgCallback) == 'function'){
                callback = params.msgCallback;
                params.msgCallback = null;
            }
            msg(data.info,callback);
        },
        tiptype:function(txt,o){
            if(o.type > 2)
                msg(txt);
        },
        tipSweep:true,
        postonce:true,
    };
    opt = obj_merge(opt,params);
    return $(selector).Validform(opt);
}

/**
 * 合并对象 把obj2属性并入obj1
 * @date   2016-06-28
 * @param  {object}   obj1 默认参数对象
 * @param  {object}   obj2 新参数对象
 * @return {object}        合并后的参数对象
 */
function obj_merge(o1,o2){
    for(key in o2){
        o1[key] = o2[key];
    }
    return o1;
}

//图片上传
var settings = {}; //多个绑定时使用 总设置文件
function bind_uploadOne(options){
    var _this     = this,
        defaults  = {
            uploadUrl : '/Home/Upload/upload.html', //图片上传地址
            bind : '',
            flag : 'IMG', //一个文件中多个绑定时使用
            before : undefined,
            callback : undefined,
            dataType : 'text',
            filemaxsize : 0, //单位M 0为不限制
        };
    var currset = obj_merge(defaults,options);
    var el = $(currset.bind);
    settings[currset.flag] = currset;
    // console.log(settings);
    //为元素添加一个上传控件 关联点击事件
    $(el).each(function(){
        if($(this).siblings('input[type="file"]').length == 0){
            $(this).after('<input class="JS_FILE_'+currset.flag+'" name="JS_FILE" flag="'+currset.flag+'" type="file" multiple="true" value="" style="display:none;">');
        }
        // $(this).unbind().click(function(){
        //     alert('a');
        //     $(this).siblings('input[type="file"]').click();
        // })
    })
    //添加动态事件
    $(document).off('click',currset.bind);
    $(document).on('click',currset.bind,function(event){
        $(this).siblings('input[type="file"]').click();
    });

    //绑定上传事件
    $(document).on('change','.JS_FILE_'+currset.flag,function(event){
        if($(this).val() == '') return;
        _this.upload(event,$(this));
    });

    //上传方法
    _this.upload = function(ev,thisel){
        var formData = new FormData(),flag = $(thisel).attr('flag'),
            target = ev.target || ev.srcElement;
        if(settings[flag].before != undefined) settings[flag].before(thisel);
        if(target.files.length == 0) return;
        //文件大小
        if(settings[flag].filemaxsize > 0){
            var size = fileSize(target);
            if(size>settings[flag].filemaxsize){   
                msg("文件不能大于"+settings[flag].filemaxsize+"M"); 
                return;
            }
        }

        formData.append('file',target.files[0]);
        // console.log(settings);
        $.ajax({
            url         :  settings[flag].uploadUrl,
            type        :  "POST",
            data        :  formData,
            dataType    :  settings[flag].dataType,
            processData :  false,
            contentType :  false,
            cache       :  false,
            success     :  function(data,status,jXhr){
                if(settings[flag].callback != undefined)
                    settings[flag].callback(data,thisel);
            }
        });
    }

}

//获取文件大小
function fileSize(target) {  
    var isIE = /msie/i.test(navigator.userAgent) && !window.opera;
    var fileSize = 0;          
    if (isIE && !target.files) {      
        var filePath = target.value;      
        var fileSystem = new ActiveXObject("Scripting.FileSystemObject");         
        var file = fileSystem.GetFile (filePath);      
        fileSize = file.Size;     
    } else {     
        fileSize = target.files[0].size;      
    }    
    var size = fileSize / 1024 / 1024;     
    return size;
 }   
