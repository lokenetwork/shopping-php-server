/**
 * Created by loken_mac on 1/27/16.
 */

(function ($) {
  $.extend({
    //规格函数
    CheckGoodsModalFrom: {

      Info: {
        Author: "loken",
      },

      CheckHiddenRequired: function(){
        this.Info.CheckResult = $("#goodsModalForm").FormHiddenRequiredCheck().Check();
      },

      CheckAll: function () {
        this.CheckHiddenRequired();
        return this.Info.CheckResult;
      },

      //具有参数的函数对象
      FunctionWithParams: function (paramObj) {
        //使用参数，是否使用默认值
        var params = paramObj ? paramObj : {
          param1: "1",
          param2: "2"
        };
        return this.Info.Name + ".FunctionWithParamObect";
      },

      //具有参数的函数对象，这里参数是一个变量
      FunctionWithParam: function (varparam) {
        //使用参数，是否使用默认值
        var param = varparam ? varparam : null;
        return this.Info.Name + ".FunctionWithParam";
      },
      //不具有参数的函数对象
      FunctionWithOutParam: function () {
        return this.Info.Name + ".FunctionWithOutParam";
      }
    }
  });



  $.extend({
    //规格函数
    SpeciaAlttribute: {

      Author:'loken',
      Info: {
        special_attribute_num:0,
        1:'',
        2:'',
      },
      input_replace_string:"",
      input_replace_string_reg:'',
      blank_special_attribute_html :'',
      Init:function(){
        this.input_replace_string_reg = /special_attribute_index_value/g;
        this.input_replace_string = 'special_attribute_index_value';
        this.blank_special_attribute_html = '<td>'+
          '<input type="number" name="goods_sku['+this.input_replace_string+'][goods_sku_price]" class="form-control" placeholder="请输入商品价格">'+
          '</td>'+
          '<td>'+
          '<input type="text" name="goods_sku['+this.input_replace_string+'][goods_sku_weight]" class="form-control" placeholder="请输入商品重量">'+
          '</td>'+
          '<td>'+
          ' <input type="text" name="goods_sku['+this.input_replace_string+'][goods_sku_store]" class="form-control" placeholder="请输入商品库存">'+
          '</td>'+
          '<td>'+
          '<input type="text" name="goods_sku['+this.input_replace_string+'][sku_commodity_code]" class="form-control" placeholder="请输入商品编码">'+
          '</td>'+
          '<td>'+
          '<button class="btn btn-default " name="goods_sku['+this.input_replace_string+'][goods_sku_img]" type="button">选择图片</button>'+
          '</td>';
      },

      CheckElementChoose:function(em){
        var element_choose = false;
        switch (em.type) {
          case 'checkbox':
            if ($(em).prop('checked')) {
              element_choose = true;
            }
            break;
          case 'radio':

            break;
          default:
        }
        return element_choose;
      },
      Click: function(){

        var special_attribute_index_class = 'special_attribute_index_1';
        var special_attribute_index_html = '';
        var had_select_special_attribute = false;
        $("." + special_attribute_index_class + ":checked").each(function (i) {

          var special_attribute_index_1_value = $(this).val();
          var special_attribute_index_1_name = $(this).attr('special_attribute_name')

          //暂时只支持两种规格
          if($.SpeciaAlttribute.Info.special_attribute_num >= 2 ){
            var special_attribute_index_class = 'special_attribute_index_2';
            $("." + special_attribute_index_class + ":checked").each(function (i) {
              had_select_special_attribute = true;
              var special_attribute_index_2_value = $(this).val();
              var special_attribute_index_2_name = $(this).attr('special_attribute_name')

              special_attribute_index_html += '<tr class="hybird_spec_row">';
              special_attribute_index_html += '<td>' + special_attribute_index_1_value + '</td>';
              special_attribute_index_html += '<td>' + $(this).val() + '</td>';

              var special_attribute_input_name = special_attribute_index_1_name+':'+special_attribute_index_1_value+'+'+special_attribute_index_2_name+':'+special_attribute_index_2_value;

              var input_special_attribute_html =
                $.SpeciaAlttribute.blank_special_attribute_html.replace($.SpeciaAlttribute.input_replace_string_reg,special_attribute_input_name);
              special_attribute_index_html += input_special_attribute_html + '</tr>';

            });
          }else{
            had_select_special_attribute = true;
            special_attribute_index_html += '<tr class="hybird_spec_row">';
            special_attribute_index_html += '<td>' + special_attribute_index_1_value + '</td>';
            var special_attribute_input_name = special_attribute_index_1_name+':'+special_attribute_index_1_value;

            var input_special_attribute_html =
              $.SpeciaAlttribute.blank_special_attribute_html.replace($.SpeciaAlttribute.input_replace_string_reg,special_attribute_input_name);
            special_attribute_index_html += input_special_attribute_html + '</tr>';
          }

        });

        $(".hybird_spec_row").remove();
        if( had_select_special_attribute ){
          $("#special_attribute_select_tips").hide().after(special_attribute_index_html);
        }else{
          $("#special_attribute_select_tips").show();
        }


      },

      CheckAll: function () {
        this.CheckHiddenRequired();
        return this.Info.CheckResult;
      },

      //具有参数的函数对象
      FunctionWithParams: function (paramObj) {
        //使用参数，是否使用默认值
        var params = paramObj ? paramObj : {
          param1: "1",
          param2: "2"
        };
        return this.Info.Name + ".FunctionWithParamObect";
      },

      //具有参数的函数对象，这里参数是一个变量
      FunctionWithParam: function (varparam) {
        //使用参数，是否使用默认值
        var param = varparam ? varparam : null;
        return this.Info.Name + ".FunctionWithParam";
      },
      //不具有参数的函数对象
      FunctionWithOutParam: function () {
        return this.Info.Name + ".FunctionWithOutParam";
      }
    }
  });

  //$.CheckGoodsModalFrom.CheckGoodsSpec();
})(jQuery);


