
$(function() {

  function formatDate(time_now) {
    var now = new Date(time_now);
    var year=now.getFullYear();
    var month=now.getMonth()+1;
    var date=now.getDate();
    var hour=now.getHours();
    var minute=now.getMinutes();
    var second=now.getSeconds();
    return year+"-"+month+"-"+date+" "+hour+":"+minute+":"+second;
  }
  var plot;
  var choiceContainer = $("#choices");

  function plotAccordingToChoices() {

    var data = [];

    choiceContainer.find("input:checked").each(function () {
      var key = $(this).attr("data_id");
      if (key && flot_data[key]) {
        data.push(flot_data[key]);
      }
    });


    plot = $.plot("#placeholder", data, {
      series: {
        lines: {
          show: true
        },
        points: {
          show: true
        }
      },
      grid: {
        hoverable: true,
        clickable: true
      },
      xaxis: {
        mode: "time",
        minTickSize: [1, type],
        min: start_time,
        max: end_time,
        twelveHourClock: false,
        timezone: "browser"

      }
    });
  }

  plotAccordingToChoices();

  choiceContainer.find("input").click(plotAccordingToChoices);

  console.log(flot_data);

  $("<div id='tooltip'></div>").css({
    position: "absolute",
    display: "none",
    border: "1px solid #fdd",
    padding: "2px",
    "background-color": "#fee",
    opacity: 0.80
  }).appendTo("body");

  $("#placeholder").bind("plothover", function (event, pos, item) {

    if (item) {
      var x = item.datapoint[0],
        y = item.datapoint[1];

      $("#tooltip").html(formatDate(x)+' '+item.series.goods_name + " 浏览量 " + y)
        .css({top: item.pageY+5, left: item.pageX+5})
        .fadeIn(200);
    } else {
      $("#tooltip").hide();
    }
  });

  $("#placeholder").bind("plotclick", function (event, pos, item) {
    if (item) {
      plot.highlight(item.series, item.datapoint);
    }
  });

  window.onresize=function(){
    plot.resize();
    plot.setupGrid();
    plot.draw();
  }
});
