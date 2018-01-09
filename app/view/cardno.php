<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title></title>
    <script>var _headTime = (new Date()).getTime();</script>
    <meta name="description" content="微信电影票是微信电影票官方手机版，提供超过3000家影院的在线购票服务，是目前全国覆盖影院最多的选座购票平台。">
    <meta name="keywords" content="微信电影票,电影,最新电影,电影票,即将上映电影,好看的电影,在线买电影票,电影票预订,选座购票,电影票团购,兑换券">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="format-detection" content="telephone=no">
    <link rel="stylesheet" href="//baymax-cos.wepiao.com/uploads/active/yuancheng/20170625/cardbin.57ee0f95d3358.css"
          type="text/css"/>
    <script type="text/javascript" src="//appnfs.wepiao.com/uploads/Assets/jquery.57ee0a4b27eef.js"></script>
</head>
<body>
<script>
    var _bodyTime = (new Date()).getTime();
    var token = "<?php echo $token?>";

</script>
<div class="binCover" id="binCover">
    <div class="popup">
        <figure>
            <img src="//appnfs.wepiao.com/uploads/Assets/card.57ee0aac9b59d.png" alt="银行卡"/>
            <figcaption>该卡号不在优惠范围内，请输入其他卡号</figcaption>
        </figure>
        <a class="enterBtn" id="enterBtn">确 认</a>
    </div>
</div>
<div class="binBox">
    <figure><img src="//appnfs.wepiao.com/uploads/Assets/Page3x.57ee0b46ed396.png" alt="银行卡"/></figure>
    <fieldset>
        <input type="tel" maxlength="22" placeholder="输入您的银行卡号" id="cardNum" class=""/><!--class="inputTrue" -->
        <input type="button" value="确认无误，下一步" id="subBtn" class=""/><!--class="buttonBlock" -->
        <!--class="buttonTrue" 输入数值正确class -->
        <p><em>＊</em>如果符合规则，您可以享受对应优惠。为了您的账户安全，娱票儿不会保存您的卡号信息</p>
    </fieldset>
</div>

<form id="GewaraPay" method="post" action="#">

</form>

<script>
    $(document).ready(function () {
        var bankNo = "<?php if (!empty($cardNo)) {
            echo $cardNo;
        } ?>";
        if (bankNo !== null && bankNo !== undefined && bankNo !== '') {
            $.ajax({
                "url": "/v1/payments/bin/<?php echo $token . '?channelId=' . $channelId ?>",
                "data": {'token': token, "bankCardNo": bankNo},
                "type": "POST",
                "dataType": "json",
                "success": function (ajaxRet) {
                    if (ajaxRet.ret == 0 && ajaxRet.sub == 0) {
                        var str = ""
                        str += "<input name=" + ajaxRet['data']['paymentInfo']['payParameter']['payParams'][0]['paramName'] + " type='hidden'  value='" + ajaxRet['data']['paymentInfo']['payParameter']['payParams'][0]['paramValue'] + "' />";
                        document.getElementById("GewaraPay").action = ajaxRet['data']['paymentInfo']['payParameter']['payUrl']
                        document.getElementById("GewaraPay").innerHTML = str;
                        document.getElementById("GewaraPay").submit();
                    } else {
                        $("#binCover").fadeIn();
                        $("#cardNum").val("");
                    }
                }
            });
        }
    });
    $(function () {

        $("#cardNum").blur(function () {
            if ($("#cardNum").val() != "") {
                $("#subBtn").addClass("buttonTrue");
            }
            if ($("#cardNum").val() == "") {
                $("#subBtn").removeAttr("class");
            }
        });

        $("#subBtn").click(function () {
            var cardNum = $("#cardNum").val();
            if (cardNum == "") {
                return false;
            }
            $.ajax({
                "url": "/v1/payments/bin/<?php echo $token . '?channelId=' . $channelId ?>",
                "data": {'token': token, "bankCardNo": cardNum},
                "type": "POST",
                "dataType": "json",
                "success": function (ajaxRet) {
                    if (ajaxRet.ret == 0 && ajaxRet.sub == 0) {
                        var str = ""
                        str += "<input name=" + ajaxRet['data']['paymentInfo']['payParameter']['payParams'][0]['paramName'] + " type='hidden'  value='" + ajaxRet['data']['paymentInfo']['payParameter']['payParams'][0]['paramValue'] + "' />";
                        document.getElementById("GewaraPay").action = ajaxRet['data']['paymentInfo']['payParameter']['payUrl']
                        document.getElementById("GewaraPay").innerHTML = str;
                        document.getElementById("GewaraPay").submit();
                    } else {
                        $("#binCover").fadeIn();
                        $("#cardNum").val("");
                    }
                }
            });


        })
        $("#enterBtn").click(function () {
            $("#binCover").fadeOut();
        })
    })
</script>
</body>
</html>
