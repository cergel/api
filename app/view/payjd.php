<script>
    payParameter = <?php echo $JdPayParams?>;
</script>
<form id="JdPayment" method="post" action="https://m.jdpay.com/wepay/web/pay">
</form>
<script>
    var str = ""
    for (var i in payParameter) {
        if (i != "url") {
            str += "<input name='" + i + "' type='hidden'  value='" + payParameter[i] + "' />";
        }
    }
    document.getElementById("JdPayment").innerHTML = str;
    document.getElementById("JdPayment").submit();
</script>