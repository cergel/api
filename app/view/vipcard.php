<!doctype html>
<html class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">
    <title>会员卡购买测试</title>
    <link rel="stylesheet" href="//cdn.bootcss.com/amazeui/2.7.2/css/amazeui.min.css">
    <link rel="stylesheet" href="//cdn.bootcss.com/amazeui/2.7.2/css/amazeui.flat.min.css">
</head>
<body>
<header data-am-widget="header"
        class="am-header am-header-default">
    <h1 class="am-header-title">
        <a href="#title-link" class="">
            影院折扣卡订购测试页面
        </a>
    </h1>

</header>


<form class="am-form">
    <fieldset>
        <legend>影院会员卡开通，影院ID: <span id="cinemaId"></span></legend>


        <div class="am-form-group">
            <label for="doc-select-1">选择要开卡的类型</label>
            <select id="doc-select-1" disabled>
                <option id="option_tips" value="0">暂无可用会员卡类型</option>
            </select>
            <span class="am-form-caret"></span>
        </div>


        <p>
            <button type="button" id="submit_btn" class="am-btn am-btn-block am-btn-xl am-btn-warning"
                    disabled="disabled">开通会员卡
            </button>
        </p>
    </fieldset>
</form>

<script src="//cdn.bootcss.com/jquery/3.1.1/jquery.min.js"></script>
<script src="//cdn.bootcss.com/amazeui/2.7.2/js/amazeui.min.js"></script>
<script type="text/javascript">
    //获取该影院拥有的会员卡类型
    <?php if($env == "pre"):?>
    var apihost = "https://commoncgi-pre.wepiao.com";
    <?php else:?>
    var apihost = "https://commoncgi.wepiao.com";
    <?php endif?>

    Request = {
        QueryString: function (item) {
            var svalue = location.search.match(new RegExp("[\?\&]" + item + "=([^\&]*)(\&?)", "i"));
            return svalue ? svalue[1] : svalue;
        }
    }
    var cinemaId = Request.QueryString("cinemaId") ? Request.QueryString("cinemaId") : null;
    var channelId = Request.QueryString("channelId") ? Request.QueryString("channelId") : 8;
    var cityId = Request.QueryString("cityId") ? Request.QueryString("cityId") : 10;
    var typeId = null;
    var cardName = null;
    getcardDetail = function (card) {
        cardName = card.typeName;
        typeId = card.typeId;
        $.ajax({
            "url": apihost + "/v1/cinema-vip/list/" + card.typeId,
            "data": {"channelId": channelId},
            "dataType": "json",
            "success": function (msg) {
                if (msg.ret == 0 && msg.sub == 0 && msg.data) {
                    //下拉框设置为可选
                    $("#option_tips").text("请选择你需要开通的会员卡");
                    $("#doc-select-1").removeAttr("disabled");
                    $.each(msg.data.cardSubTypeDtoList, function (k, v) {
                        item = "<option class='carditem' value='" + JSON.stringify(v) + "'>" + v.subName + " - 面值 " + v.settlementPrice + "分 -价格" + v.sellPrice + "分 - 有效期" + v.validMonth + " 月 - 日限额 " + v.dayLimit + " 次 - 总限额" + v.totalLimit + " 次</option>";
                        $("#doc-select-1").append(item);
                    });
                }
            }
        })


    }

    if (!isNaN(cinemaId)) {
        $("#cinemaId").text(cinemaId);
        $.ajax({
            "url": apihost + "/v1/cinema-vip/cards",
            "data": {"channelId": channelId, "cityId": cityId, "cinemaId": cinemaId},
            "dataType": "json",
            "success": function (msg) {
                if (msg.ret == 0 && msg.sub == 0 && msg.data) {
                    getcardDetail(msg.data[0]);
                }
            }

        });
    }
    //判断是否选择会员卡如果选择则允许提交

    $(document).on("change", "#doc-select-1", function () {
        if (this.value != "0") {
            $("#submit_btn").removeAttr("disabled");
        } else {
            $("#submit_btn").attr("disabled", "disabled");
        }
        return false;
    });

    $(document).on("click", "#submit_btn", function () {
        Date.prototype.Format = function (fmt) { //author: meizz
            var o = {
                "M+": this.getMonth() + 1, //月份
                "d+": this.getDate(), //日
                "h+": this.getHours(), //小时
                "m+": this.getMinutes(), //分
                "s+": this.getSeconds(), //秒
                "q+": Math.floor((this.getMonth() + 3) / 3), //季度
                "S": this.getMilliseconds() //毫秒
            };
            if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
            for (var k in o)
                if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
            return fmt;
        }


        obj = JSON.parse($("#doc-select-1").val());
        console.log($("#doc-select-1").val());
        var date = new Date();
        date.setMonth(date.getMonth() + obj.validMonth);
        vipcard = {};
        vipcard.imageUrl = "http://card.png";
        vipcard.title = cardName + " - " + obj.subName;
        vipcard.price = obj.sellPrice;
        vipcard.typeId = typeId;
        vipcard.subTypeId = obj.subTypeId;
        vipcard.validPeriod = "有效期至：" + date.Format("yyyy-MM-dd");

        data = [];
        data[0] = vipcard;
        redirect_url = "wxmovie://discountCardOrder?orderInfo=" + JSON.stringify(data);
        window.location.href = redirect_url;
        console.log(vipcard);
    });
</script>
</body>
</html>