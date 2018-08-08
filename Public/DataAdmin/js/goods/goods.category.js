/**
 * 商品分类
 * @type {Object}
 */
var category = {
    init:function(parentElId,cateElId,cateData){
        this.data = cateData;
        this.parentElId = parentElId;
        this.cateElId = cateElId;
        return this;
    },
    set:function(){
        var cateid = arguments[0] && this._find(arguments[0]) ? arguments[0] : 0;
        var pel = document.getElementById(this.parentElId);
        var pcateArr = new Array();
        var ccateArr = new Array();
        pcateArr = this._child(0);            
        if(cateid == 0){
            ccateArr = this._child(pcateArr[0].cat_id);
            this._setHtml(pcateArr,this.parentElId);
            this._setHtml(ccateArr,this.cateElId);
        }else{
            var cate = this._find(cateid);
            if(cate.parent_id != '0'){
                ccateArr = this._child(cate.parent_id);
                this._setHtml(pcateArr,this.parentElId,cate.parent_id);
                this._setHtml(ccateArr,this.cateElId,cateid);
            }else{
                this._setHtml(pcateArr,this.parentElId,cateid);
                this._setHtml(ccateArr,this.cateElId);
            }
            
        }
        var thi = this;
        //绑定切换事件 
        pel.onchange = function(){                
            thi._Change();
        }
    },
    _Change:function(){
        var pel = document.getElementById(this.parentElId);
        var cateArr = new Array();
        var index = pel.selectedIndex; // 选中索引
        var cateArr = this._child(pel.options[index].value);
        this._setHtml(cateArr,this.cateElId);
    },
    _setHtml:function(cateArr,elID,selectid){
        var el = document.getElementById(elID);
        var html = '<option value="">请选择分类</option>';
        for(var i = 0; i < cateArr.length; i++){
            var selected = '';
            if(cateArr[i].cat_id == selectid) selected = 'selected="selected"';
            html += "<option value='"+ cateArr[i].cat_id +"' "+ selected +">"+ cateArr[i].cat_name +"</option>";
        }
        el.innerHTML = html;
    },
    _child:function(parentid){
        var data = this.data;
        var tempArr = new Array();
        for(var i = 0; i < data.length; i++){
            if(parentid == data[i].parent_id){
                tempArr.push(data[i]); 
            }
        }
        return tempArr;
    },
    _find:function(cid){
        var cate = false;
        var data = this.data;
        for(var i = 0; i < data.length; i++){
            if(cid == data[i].cat_id){
                cate = data[i];
                break;
            }
        }
        return cate;
    }
}