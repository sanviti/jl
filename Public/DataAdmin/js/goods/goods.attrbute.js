$(function(){
    //规格图片上传
    $(document).on('change','.uploadfile',function(event){
        var thi = $(this);
        var formData = new FormData(),
            target = event.target || event.srcElement;
        if(target.files.length == 0) return;
        formData.append('file',target.files[0]);
        $.ajax({
         url             :    uploadUrl,
         type            :    "POST",
         data            :    formData,
         dataType        :    'text',
         processData     :    false,
         contentType     :    false,
         cache           :    false,
         success         :    function(data,status,jXhr){
            thi.siblings('img.uploadOne').attr('src','/Uploads'+data);
            thi.siblings('input.optImg').val('/Uploads'+data);
         }
        });
    });
    $(document).on('click','.uploadOne',function(){
        $(this).siblings('.uploadfile').click();
    });

    //添加属性
    $("#addAttr,#addImgAttr").click(function(){
            var id = $("#attribute tr:last").attr("attrid") !== undefined ? parseInt($("#attribute tr:last").attr("attrid")) + 1 : 0;
            var imgAttrStr = '';
            if($(this).hasClass("addImgAttr")) {
                imgAttrStr = ' class="imgAttr"'; //图片属性
                imgAttr = $(".imgAttr").length;
                if(imgAttr > 0){
                    alert('图片属性只能添加一个');
                    return;
                }
            }else{
                imgAttrStr = ''; //文字属性
            }
            var html = '';
            html += '<tr attrid="'+id+'" '+ imgAttrStr +' >';
            html += '<td style="text-align: left;padding: 5px;">';
            html += '<div class="attname_wrap">';
            html += '<input type="text" size="10" name="attribute['+id+'][attname]" placeholder="属性名称">';
            html += '<a class="btn btn-xs btn-danger pull-right delAttr">删除属性</a>';
            html += '<a class="btn btn-xs btn-primary pull-right addAttrVal">添加值</a>';
            html += '</div>';
            html += '<div class="attrs_wrap">';
            if($(this).hasClass("addImgAttr")){
                html += '<input type="hidden" size="10" name="attribute['+id+'][imgAttr]" value="1">';

                html += '<div class="attrs_item" index=0>';
                html += '<img src="/Public/Admin/img/default.jpg" class="uploadOne">';
                html += '<input name="attribute['+ id +'][attvalue][0][img]" value="/Public/Admin/img/default.jpg" class="optImg"/>';
                html += '<input type="file" name="" class="uploadfile">';
                html += '<p>';
                html += '<input type="text" size="10" name="attribute['+ id +'][attvalue][0][optval]" value="" placeholder="属性值">';
                html += '</p>';
                html += '<i class="fa fa-close delAttrVal"></i>';
                html += '</div>';
            }else{
                html += '<div class="attrs_item" index=0>';
                html += '<p>';
                html += '<input type="text" size="10" name="attribute['+ id +'][attvalue][0][optval]" value="" placeholder="属性值">';
                html += '</p>';
                html += '<i class="fa fa-close delAttrVal"></i>';
                html += '</div>';
            }
            html += '</div>';
            html += '</td>';
            html += '</tr>';

            $("#attribute").append(html);
    });

    
    //添加属性值
    $(document).on("click",".addAttrVal",function(){
        var id = parseInt($(this).parents("tr").attr("attrid"));
        var optIndex = parseInt($(this).parent().siblings(".attrs_wrap").find(".attrs_item:last").attr("index")) + 1;
        var html = '';
        if($(this).parents('tr').hasClass("imgAttr")){
            html += '<div class="attrs_item" index='+ optIndex +'>';
            html += '<img src="/Public/Admin/img/default.jpg" class="uploadOne">';
            html += '<input type="file" name="" class="uploadfile">';
            html += '<input type="hidden" name="attribute['+ id +'][attvalue]['+ optIndex +'][img]" value="" class="optImg"/>';
            html += '<p>';
            html += '<input type="text" size="10" name="attribute['+ id +'][attvalue]['+ optIndex +'][optval]" value="" placeholder="属性值">';
            html += '</p>';
            html += '<i class="fa fa-close delAttrVal"></i>';
            html += '</div>';
        }else{
            html += '<div class="attrs_item" index='+ optIndex +'>';
            html += '<p>';
            html += '<input type="text" size="10" name="attribute['+ id +'][attvalue]['+ optIndex +'][optval]" value="" placeholder="属性值">';
            html += '</p>';
            html += '<i class="fa fa-close delAttrVal"></i>';
            html += '</div>';
        }
        // html += ' <input type="text" size="10" name="attribute['+id+'][attvalue][]" value="" placeholder="属性值"/>';
        // html += ' <a href="javascript:;" class="delAttrVal"> 删除 </a> ';
        $(this).parent().siblings('.attrs_wrap').append(html);
    });

    //删除属性值
    $(document).on("click",".delAttrVal",function(){
        $(this).parent("div").remove();
        $(this).remove();
    });

    //删除属性
    $(document).on("click",".delAttr",function(){
        $(this).parent().parent().parent("tr").remove();
    });

    //刷新库存表格
    $("#create_stock_table").click(function(){
        var errBreak = function(){
            return false;
        }

        var RowNum = 1, //行数
            AttrsData = new Array(); //表格数据
        if($("tbody#attribute tr").length == 0){
            alert('请添加规格');
            return false;
        }
        var i=0;
        var attrNum = $("tbody#attribute tr").length;
        //生成规格数据
        $("tbody#attribute tr").each(function(v){
            i++;
            var temp = Array();
            var attrOpt = new Array();
            var CurrAttrName = $.trim($(this).find('.attname_wrap input').val());
            if(CurrAttrName == ''){
                $(this).find('.attname_wrap input').focus();
                alert('请填写属性名称');
                return false;
            }
            $(this).find('.attrs_item').each(function(v){ 
                var TempVal = new Array();
                var CurrVal = $.trim($(this).find('p>input[type="text"]').val());
                if(CurrVal == ''){
                    $(this).find('p>input[type="text"]').focus();
                    alert('请填写属性值');                    
                    return false;
                }
                TempVal.push(CurrVal);
                //有图片
                if($(this).find('img').length > 0){
                    TempVal.push($(this).find('img').attr('src'));
                }
                attrOpt.push(TempVal);
            });
            temp.push(CurrAttrName); //规格名称
            temp.push(attrOpt);//规格值列表
            temp.push(0); //指针
            AttrsData.push(temp);
            RowNum *= attrOpt.length; //计算行高
            if(i == attrNum){
                createTable(AttrsData,RowNum);
            }
        });
        // console.log(RowNum);
        // console.log(window.JSON.stringify(AttrsData));
        // createTable(AttrsData,RowNum);
    });

    //生成库存表格
    function createTable(AttrsData,RowNum){
        if(AttrsData.length == 0){
            alert('请添加商品规格');
            return ;
        }
        //计算Rowspan
        for(var i = 0; i < AttrsData.length; i++ ){
            var Rowspan = 1;
            for(var i2 = i + 1; i2 < AttrsData.length; i2++ ){
                console.log(AttrsData[i2][1].length);
                Rowspan *= AttrsData[i2][1].length;
            }
            AttrsData[i].push(Rowspan);
        }
/*
[
    [
        "颜色",  // 0 规格名称
        [        // 1 规格选项数组
            [
                "黄色",  // 0 规格值
                "/Uploads/images/2016-05-18/573c2cb041285.jpg"  //规格图片
            ],
            [
                "红色",
                "/Uploads/images/2016-05-18/573c2cb4d07e2.jpg"
            ],
            [
                "蓝色",
                "/Uploads/images/2016-05-18/573c2cb70b9d1.jpg"
            ]
        ],
        0,  //2 指针位置
        4   //3 Rowspan
    ],
    [
        "尺码",  
        [
            [
                "S"
            ],
            [
                "M"
            ],
            [
                "X"
            ],
            [
                "L"
            ]
        ],
        0,
        1
    ]
]
*/

        var Table = '';
        //生成表头
        Table += '<thead><tr>';
        for(var i in AttrsData){
            // console.log(AttrsData[i]);
            Table += '<th>' + AttrsData[i][0] + '</th>';
        }
        // Table += '<th>stockFlag</th><th>价格</th><th>库存</th></tr></thead><tbody>';
        Table += '<th>价格</th><th>库存</th></tr></thead><tbody>';
        // console.log(Table);
        for(line_num = 1; line_num <= RowNum; line_num++){
            var index = line_num - 1; //数组索引
            var stockFlag = ''; //库存唯一标示
            var stockImg = '';
            Table += '<tr>';
            for(var k in AttrsData){
                if(AttrsData[k][2] == AttrsData[k][1].length)AttrsData[k][2] = 0; //指针重置
                var curr = AttrsData[k][2];

                Table += '<td>' + AttrsData[k][1][curr][0] + '</td>';
                if(undefined !== AttrsData[k][1][curr][1]) stockImg = AttrsData[k][1][curr][1]; //库存图片
                stockFlag += stockFlag == '' ? AttrsData[k][1][curr][0] : '|' +AttrsData[k][1][curr][0];
                if(line_num % AttrsData[k][3] == 0){
                    AttrsData[k][2] ++;
                }
            }
            // Table += '<td>' + stockFlag + '</td>';
            Table += '<td><input type="hidden" size="32" name="stock[' + index + '][flag]" value="'+ stockFlag +'"/><input type="text"  size="6" name="stock[' + index + '][price]" value=""/></td>';
            Table += '<td><input type="text" size="6" name="stock[' + index + '][stock]" value="0"/>';
            //库存图片
            if(stockImg !== ''){
                Table += '<input type="hidden" size="6" name="stock[' + index + '][img]" value="'+ stockImg +'"/>';
            }
            
            Table += ' </td>';
            Table += '</tr>';
        }
        Table += '</tbody>';
        $("#stockTable").html(Table);
    }
});