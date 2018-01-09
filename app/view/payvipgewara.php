<script>
    payParameter = <?php echo $PayParams?>;
</script>
<form id="GewaraPay" method="post" action="#">
</form>
<script>
    var str = ""
    str += "<input name='PAY_DATA_' type='hidden'  value='" + payParameter['submitParams'][0]['PAY_DATA_'] + "' />";
    document.getElementById("GewaraPay").action = payParameter['payurl']
    document.getElementById("GewaraPay").innerHTML = str;
    document.getElementById("GewaraPay").submit();
</script>