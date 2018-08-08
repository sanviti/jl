/**
 * 商品图片上传部分
 */

//图片拖拽
var el = document.getElementById('uploadImgWrap');
new Sortable(el, {
    group: "uploadImg",
    // store: null, // @see Store
    handle: ".uploadImgItem", // 点击目标元素约束开始
    draggable: ".uploadImgItem",   // 指定那些选项需要排序
    ghostClass: "sortable-ghost",  //被拖拽目标样式
});


//图片批量上传
$(document).ready(function(){
    //图片单个上传
    // var uploaderOne = WebUploader.create({
    //     auto : true,
    //     swf: '__PUBLIC__/Admin/js/plugins/webuploader/Uploader.swf',
    //     server: uploadUrl,
    //     pick: '#uploadOne',
    //     resize: true,
    //     accept: {
    //         title: 'Images',
    //         extensions: 'gif,jpg,jpeg,bmp,png',
    //         mimeTypes: 'image/*'
    //     }
    // });
    // uploaderOne.on( 'uploadSuccess', function( file , rsObj) {
    //     if(rsObj.status == 1){
    //         $( '#GD_IndexImg img' ).attr('src','/Uploads'+rsObj.path);
    //         $( '#goods_img' ).val('/Uploads'+rsObj.path);
    //     }else{
    //         msg(rsObj.msg);
    //     }
       
    // });
    var $list = $('#uploadImgWrap'),
        $btn  = $('#ctlBtn'),
        state = 'pending',
        uploader;
    //图片上传webuploader
    var uploader = WebUploader.create({
        auto : true,
        swf: '__PUBLIC__/Admin/js/plugins/webuploader/Uploader.swf',
        server: uploadUrl,
        pick: '#picker',
        resize: true,
        accept: {
            title: 'Images',
            extensions: 'gif,jpg,jpeg,bmp,png',
            mimeTypes: 'image/*'
        }
    });

    // 当有文件添加进来的时候
    uploader.on( 'fileQueued', function( file ) {     
        // var divid = 'WU_FILE_' + $('#uploadImgWrap .uploadImgItem').length;
        // console.log(divid);
        // file.id = divid;
        // console.log(file);
        
        var $li = $(
                '<div id="' + file.id + '" class="uploadImgItem">' +
                    '<img>' +
                    // '<div class="info">' + file.name + '</div>' +
                    '<p class="state"></p>'+
                    '<i class="glyphicon glyphicon-remove delBtn"></i>'+
                '</div>'
                ),
            $img = $li.find('img');
        $list.append( $li );
        // 创建缩略图
        // thumbnailWidth x thumbnailHeight 为 100 x 100
        uploader.makeThumb( file, function( error, src ) {
            if ( error ) {
                $img.replaceWith('<span>不能预览</span>');
                return;
            }
            $img.attr( 'src', src );
        }, 100, 100 );
    });

    // 文件上传过程中创建进度条实时显示。
    uploader.on( 'uploadProgress', function( file, percentage ) {
        console.log(file);
        var $li = $( '#'+file.id ),
            $percent = $li.find('.progress .progress-bar');

        // 避免重复创建
        if ( !$percent.length ) {
            $percent = $('<div class="progress progress-striped active">' +
              '<div class="progress-bar" role="progressbar" style="width: 0%">' +
              '</div>' +
            '</div>').appendTo( $li ).find('.progress-bar');
        }

        $li.find('p.state').text('上传中');
        $percent.css( 'width', percentage * 100 + '%' );
    });
    uploader.on( 'uploadSuccess', function( file , rsObj) {
        if(rsObj.status == 1){
            $( '#'+file.id+' img').attr('realpath','/Uploads'+rsObj.path);
            $( '#'+file.id ).find('p.state').text('已上传').addClass('succ');
        }else{
            msg(rsObj.msg);
        }
        
    });

    uploader.on( 'uploadError', function( file ) {
        $( '#'+file.id ).find('p.state').text('上传出错').addClass('err');
    });

    uploader.on( 'uploadComplete', function( file ) {
        $( '#'+file.id ).find('.progress').fadeOut();
    });

    $(document).on('click','.delBtn',function(){
        $(this).parents('.uploadImgItem').remove();
    });

    $('#ctlBtn').click(function(){

    });
});


/**
 * 其他
 */

 $(document).ready(function(){

    //表单验证
    var valid = $(".goodsForm").Validform({
        datatype:{
            "category":function(gets,obj,curform,regxp){
                var cate2=curform.find("#category2");
                if(gets == '' && cate2.val() == ''){
                    return false;
                }
                return true;
            },
            "price" : /^[0-9]+(\.[0-9]{1,2})?$/,
        },
        tiptype:function(msg){
            layer.msg(msg,{skin:"layui-layer-hui",time:1000});
        },
        tipSweep:true,
        postonce:true,
        beforeSubmit:function(form){
            var flag=true;
            //展示图片数据处理
            var goods_pics = '';
            $("#uploadImgWrap img").each(function(i,img){
                console.log(img);
                if(goods_pics == ''){
                    goods_pics = $(this).attr('realpath');
                }else{
                    goods_pics += ',' + $(this).attr('realpath');
                }
            });

            $('#goods_pics').val(goods_pics);
            if(form.find('input[name="extended_attributes"]:checked').val() == 1){
                if($('#attribute>tr').length == 0){
                    layer.msg('请添加商品规格',{skin:"layui-layer-hui",time:1000});
                    flag = false;
                    return false;
                }    
                $('#attribute input[type="text"]').each(function(){
                    if($(this).val() == ''){
                        layer.msg('请完善商品规格数据',{skin:"layui-layer-hui",time:1000});
                        $(this).focus();
                        flag = false;
                        return false;
                    }
                });

                if($('#stockTable>tbody>tr').length == 0 && flag == true){
                    layer.msg('请刷新库存表格',{skin:"layui-layer-hui",time:1000});
                    flag = false;
                    return false;
                }
                $('#stockTable input[type="text"]').each(function(){
                    if($(this).val() == ''){
                        layer.msg('请完善库存表格',{skin:"layui-layer-hui",time:1000});
                        $(this).focus();
                        flag = false;
                        return false;
                    }
                });
            }

            if(!flag) return false;  
        },
        ajaxPost:true,
        callback:function(data){
            layer.msg(data.returnMsg,{skin:"layui-layer-hui",time:1000,end:function(){
                if(data.returnCode == 'SUCCESS'){
                    window.location.reload();
                    window.opener.location.reload(); //重载父页面
                } 
            }});
        }
    });
    valid.addRule([
        {
            ele : '#category',
            datatype: 'category',
            nullmsg:'请选择分类',
        },
        {
            ele : '#goods_name',
            datatype: '*',
            nullmsg:'请输入商品名称',
        },
        {
            ele : '#price',
            datatype: 'price',
            nullmsg:'请输入价格',
            errormsg:'请输入整数保留两位小数',
        },
        {
            ele : '#express_fee',
            datatype: 'price',
            nullmsg:'请输入运费',
            errormsg:'请输入整数保留两位小数',
        },
       
    ]);
    
    //商品参数
    $('#add_goods_arg').click(function(){        
        var html = '';
        if($('#argTable tbody tr').length == 0){
            html += '<tr arg_id="0"><td>';
            html += '<input type="text" size="20" name="arguments[0][0]" value="" class="form-control input-sm"></td>';
            html += '<td><input type="text" size="40" name="arguments[0][1]" class="form-control input-sm"></td>';
            html += '<td><a href="javascript:;" class="del_arg">删除</a></td>';
            html += '</tr>';
        }else{
            console.log($('#add_goods_arg tbody tr:last'));
            var id = parseInt($('#argTable tbody tr:last').attr('arg_id')) + 1;
            html += '<tr arg_id="'+ id +'">';
            html += '<td>';
            html += '<input type="text" size="20" name="arguments['+ id +'][0]" class="form-control input-sm">';
            html += '</td>';
            html += '<td><input type="text" size="40" name="arguments['+ id +'][1]" class="form-control input-sm"></td>';
            html += '<td><a href="javascript:;" class="del_arg">删除</a></td>';
            html += '</tr>';
        }
        $('#argTable tbody').append(html);
    });
    //删除商品参数
    $(document).on('click','#argTable .del_arg',function(){
        $(this).parent().parent().remove();
    });

    //添加文档
    $('#add_doc').click(function(){
        var html = '';
        if($('#docTable tbody tr').length == 0){
            html = '<tr doc_id="0"><td class="text-center"><img src="/Public/images/upload_img.png" class="doc-pic" /><input type="hidden" size="40" name="docs[0][pic]" value="" class="form-control input-sm"></td><td><input type="text" size="40" name="docs[0][title]" value="" class="form-control input-sm"></td><td><input type="text" size="40" name="docs[0][des]" value="" class="form-control input-sm"></td><td><div class="form-inline margin-left-15"><div class="form-group w80"><input type="text" name="docs[0][file]" class="form-control input-sm" style="width: 100%;" ></div> <a href="javascript:;" class="btn btn-default btn-sm margin-left-15 uploadFile">点击上传</a></div></td><td><a href="javascript:;" class="del_doc">删除</a></td></tr>';
        }else{
            console.log($('#docTable tbody tr:last'));
            var id = parseInt($('#docTable tbody tr:last').attr('doc_id')) + 1;
            html = '<tr doc_id="'+id+'"><td class="text-center"><img src="/Public/images/upload_img.png" class="doc-pic" /><input type="hidden" size="40" name="docs['+id+'][pic]" value="" class="form-control input-sm"></td><td><input type="text" size="40" name="docs['+id+'][title]" value="" class="form-control input-sm"></td><td><input type="text" size="40" name="docs['+id+'][des]" value="" class="form-control input-sm"></td><td><div class="form-inline margin-left-15"><div class="form-group w80"><input type="text" name="docs['+id+'][file]" class="form-control input-sm" style="width: 100%;" ></div> <a href="javascript:;" class="btn btn-default btn-sm margin-left-15 uploadFile">点击上传</a></div></td><td><a href="javascript:;" class="del_doc">删除</a></td></tr>';
        }
        $('#docTable tbody').append(html);
        // $('.doc-pic,.JS_FILE_docpic').off('click change');
        //doc图片上传
        bind_uploadOne({
            uploadUrl : uploadOneUrl,
            bind : '.doc-pic',
            flag : 'docpic',
            dataType : 'JSON',
            callback : function(data,el){
                if(data.status == 1){
                    $(el).siblings('img').attr('src',data.url).addClass('zoomin');
                    $(el).siblings('input').val(data.url);
                }else{
                    msg(data.info);
                }
            }
        })
        //文档上传
        bind_uploadOne({
            uploadUrl : uploadFile,
            bind : '.uploadFile',
            flag : 'file',       
            dataType : 'JSON',
            callback : function(data,el){
                if(data.status == 1){
                    $(el).siblings('.form-group').children('input').val(data.path);                    
                }else{
                    msg(data.msg);
                }
            }
        })
        
    });

    //删除文档
    $(document).on('click','#docTable .del_doc',function(){
        $(this).parent().parent().remove();
    });

    //添加视频
    $('#add_video').click(function(){
        var html = '';
        if($('#videoTable tbody tr').length == 0){
            html = '<tr video_id="0"><td class="text-center"><img src="/Public/images/upload_img.png" class="video-pic" /><input type="hidden" size="40" name="video[0][pic]" value="" class="form-control input-sm"></td><td><input type="text" size="40" name="video[0][title]" value="" class="form-control input-sm"></td><td><input type="text" size="40" name="video[0][des]" value="" class="form-control input-sm"></td><td><div class="form-inline margin-left-15"><div class="form-group w80"><input type="text" name="video[0][file]" class="form-control input-sm" style="width: 100%;" ></div> <a href="javascript:;" class="btn btn-default btn-sm margin-left-15 uploadVideo">点击上传</a></div></td><td><a href="javascript:;" class="del_video">删除</a></td></tr>';
        }else{
            // console.log($('#add_video tbody tr:last'));
            var id = parseInt($('#videoTable tbody tr:last').attr('video_id')) + 1;
            html = '<tr video_id="'+id+'"><td class="text-center"><img src="/Public/images/upload_img.png" class="doc-pic" /><input type="hidden" size="40" name="video['+id+'][pic]" value="" class="form-control input-sm"></td><td><input type="text" size="40" name="video['+id+'][title]" value="" class="form-control input-sm"></td><td><input type="text" size="40" name="video['+id+'][des]" value="" class="form-control input-sm"></td><td><div class="form-inline margin-left-15"><div class="form-group w80"><input type="text" name="video['+id+'][file]" class="form-control input-sm" style="width: 100%;" ></div> <a href="javascript:;" class="btn btn-default btn-sm margin-left-15 uploadVideo">点击上传</a></div></td><td><a href="javascript:;" class="del_video">删除</a></td></tr>';
        }
        $('#videoTable tbody').append(html);
        // $('.doc-pic,.JS_FILE_docpic').off('click change');
        //视频上传
        bind_uploadOne({
            uploadUrl : uploadOneUrl,
            bind : '.video-pic',
            flag : 'videopic',
            dataType : 'JSON',
            callback : function(data,el){
                if(data.status == 1){
                    $(el).siblings('img').attr('src',data.url).addClass('zoomin');
                    $(el).siblings('input').val(data.url);
                }else{
                    msg(data.info);
                }
            }
        })
        //视频上传
        bind_uploadOne({
            uploadUrl : uploadVideo,
            bind : '.uploadVideo',
            flag : 'uploadVideo',       
            dataType : 'JSON',
            callback : function(data,el){
                if(data.status == 1){
                    $(el).siblings('.form-group').children('input').val(data.path);                    
                }else{
                    msg(data.msg);
                }
            }
        })
        
    });

    //删除视频
    $(document).on('click','#videoTable .del_video',function(){
        $(this).parent().parent().remove();
    });
    
    //配件选择
    $('#parts-list li').click(function(){
        if($(this).hasClass('parts-selected')){
            var id = $(this).data('id');
            $('#part-selected').children('li[data-id = "'+ id +'"]').fadeOut('slow',function(){
                $(this).remove();
            });
            $(this).removeClass('parts-selected');
        }else{
            $('#part-selected').append($(this).prop('outerHTML'));
            $(this).addClass('parts-selected');
        }
    })

});