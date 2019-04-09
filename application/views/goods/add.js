/**
 * Created by loken_mac on 1/27/16.
 */


function upload_main_pic(obj){
  var file_obj = document.getElementById('main_pic').files[0];

  var fd = new FormData();
  fd.append('main_pic', file_obj);

  $.ajax({
    url:img_upload_domain+'/Index/uploadMultipleGoodsPicture',
    type:'POST',
    data:fd,
    processData:false,  //tell jQuery not to process the data
    contentType: false,  //tell jQuery not to set contentType
    //这儿的三个参数其实就是XMLHttpRequest里面带的信息。
    success:function (arg,a1,a2) {
      console.log(arg.image_info[0].image_name);
      console.log(a1);
      console.log(a2);
      //清空值,让用户可以再次上传同样的文件.
      $("#main_pic").val("");
      $("#main_pic_display_dic").show();
      $("#main_pic_display").attr("src",img_diaplay_domain+arg.image_info[0].image_name);
      $("input[name='main_pic_url']").val(arg.image_info[0].image_name);
      $("input[name='main_pic_id']").val(arg.upload_ids[0]);
    }

  })
}

function upload_pic(obj){
  var file_obj = document.getElementById('goods_pic').files[0];

  var fd = new FormData();
  fd.append('goods_pic', file_obj);

  $.ajax({
    url:img_upload_domain+'/Index/uploadMultipleGoodsPicture',
    type:'POST',
    data:fd,
    processData:false,  //tell jQuery not to process the data
    contentType: false,  //tell jQuery not to set contentType
    //这儿的三个参数其实就是XMLHttpRequest里面带的信息。
    success:function (arg,a1,a2) {

      var insert_html = $("#demo_html_pic").html();
      insert_html = sprintf(insert_html,img_diaplay_domain+arg.image_info[0].image_name,
        arg.upload_ids[0],arg.upload_ids[0],arg.upload_ids[0],arg.image_info[0].image_name);

      $(".can_remove_list").append(insert_html);

      //清空值,让用户可以再次上传同样的文件.
      $("#goods_pic").val("");
    }

  })
}

function del_pic(obj) {
  var sure = confirm("确认删除图片吗?");
  if(sure){
    $(obj).parent().remove();
  }
}

function upload_sku_pic(obj) {
  var file_obj = obj.files[0];

  var fd = new FormData();
  fd.append('goods_sku_pic', file_obj);

  $.ajax({
    url:img_upload_domain+'/Index/uploadMultipleGoodsPicture',
    type:'POST',
    data:fd,
    processData:false,  //tell jQuery not to process the data
    contentType: false,  //tell jQuery not to set contentType
    //这儿的三个参数其实就是XMLHttpRequest里面带的信息。
    success:function (arg,a1,a2) {

      var thumbnail = $(obj).parent().parent().prev();
      thumbnail.children("img").attr('src',img_diaplay_domain+arg.image_info[0].image_name);
      thumbnail.show();
      thumbnail.children(".sku_pic_id").val(arg.upload_ids[0]);
      thumbnail.children(".sku_pic_url").val(arg.image_info[0].image_name);

      //清空值,让用户可以再次上传同样的文件.
      $(obj).val("");
    }

  })
}

function add_sku() {
  var insert_html = $("#demo_html_hybird_spec_row").html();
  var sku_index = $(".hybird_spec_row").length-2+1;
  insert_html = insert_html.replace(/sku_index/g,sku_index)
  $("#add_sku").before(insert_html);

  console.log(insert_html);
}

function del_sku(obj,sku_id) {
  var sure = confirm("确认删除sku吗?");
  if(sure){
    $(obj).parent().parent().remove();
    if( sku_id ){
      var old_v = $("input[name='del_sku_id']").val();
      if( '' == old_v ){
        var new_v = sku_id
      }else{
        var new_v = old_v+','+sku_id
      }
      $("input[name='del_sku_id']").val(new_v);
    }
  }
}


(function ($) {
  $.extend({
    //检测商品模型表单
    CheckGoodsFrom: {
      //该拓展函数的基本信息
      Info: {
        Author: "loken",
        CheckResult:false
      },

      CheckHiddenRequired: function(){
        this.Info.CheckResult = $("#goodsForm").FormHiddenRequiredCheck().Check();
      },

      CheckAll: function () {
        this.CheckHiddenRequired();
        return this.Info.CheckResult;
      },

    }
  });
  //$.CheckGoodsModalFrom.CheckGoodsSpec();
})(jQuery);
