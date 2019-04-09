/**
 * Created by loken_mac on 1/26/16.
 */

//这两个变量用于选择分类,修改的时候
var selectCate_info = [];

//ajax 读取后端顶级分类信息
$.get("/Category/echoSubCategory/parent_id/0",
  function (sub_category) {
    if (sub_category != 'no_sub_category') {
      var html = "<option value='0'>顶级分类</option>";
      for (var o in sub_category) {
        var selected;
        if (category_level_0 == sub_category[o].category_id) {
          selected = 'selected';
          selectCate_info[0] = [];
          selectCate_info[0]['level'] = 0;
          selectCate_info[0]['obj'] = document.getElementById('category_level_0');
        } else {
          selected = '';
        }
        html += ' <option ' + selected + ' value="' + sub_category[o].category_id + '">' + sub_category[o].category_name + '</option>'
      }
      $("#category_level_0").html(html);
    }
  });
for (var _i = 1; _i < 3; _i++) {
  var parent_category_level = eval('category_level_' + (_i - 1));
  var category_level = eval('category_level_' + _i);
  if (category_level > 0) {
    //ajax 读取后端顶级分类信息
    $.ajax({
      type: "GET",
      url: "/Category/echoSubCategory/parent_id/" + parent_category_level,
      data: "",
      async: false,
      success: function (sub_category) {
        if (sub_category != 'no_sub_category') {
          $("#category_level_" + _i).addClass('in');
          var html = "<option value='-1'>--请选择--</option>";
          for (var o in sub_category) {
            var selected;
            if (category_level == sub_category[o].category_id) {
              selected = 'selected';
              selectCate_info[_i] = [];
              selectCate_info[_i]['level'] = _i;
              selectCate_info[_i]['obj'] = document.getElementById("category_level_" + _i);
            } else {
              selected = '';
            }
            html += ' <option ' + selected + ' value="' + sub_category[o].category_id + '">' + sub_category[o].category_name + '</option>'
          }
          $("#category_level_" + _i).html(html);
        }
      }
    });
  }
}

function selectCateDelay() {
  console.log(selectCate_info);
  console.log(selectCate_info.length);
  if( selectCate_info.length > 0 ){
    selectCate(selectCate_info[selectCate_info.length-1]['obj'],selectCate_info[selectCate_info.length-1]['level']);
  }
}

setTimeout("selectCateDelay()", 500);

/*
 *  选择分类触发事件,适用于3级分类
 * */
function selectCate(obj, level) {
  var next_level = level + 1;
  var prev_level = level - 1;


  function hide_other() {
    function level_2_hide(){
      $("#category_level_2").html('').hide();
    }
    //沒有子分类,其他全部隐藏
    if (level == 0) {
      $("#category_level_1").html('').hide();
      level_2_hide();
    } else if (level == 1) {
      level_2_hide();
    }
  }
  var category_id = $(obj).val();
  //如果小于0,就选上一级的
  if( category_id >= 0 ){
    $("#parent_category_id").val(category_id);
  }else{
    $("#parent_category_id").val($("#category_level_" + prev_level).val());
  }
  hide_other();
  if (category_id > 0) {
    $("#category_level").val(next_level);
    if (level != 2) {
      $.get("/Category/echoSubCategory/parent_id/" + category_id, {},
        function (sub_category) {
          if (sub_category != 'no_sub_category') {
            var html = "<option value='0'>---请选择---</option>";
            for (var o in sub_category) {
              html += ' <option value="' + sub_category[o].category_id + '">' + sub_category[o].category_name + '</option>'
            }
            $("#category_level_" + next_level).html(html).show();
          } else {
          }
        });
    }
  } else {
    $("#category_level").val(0);
  }


  return;
}