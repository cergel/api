<form id="GewaraPay" method="<?php echo $httpMethod ?>" action="<?php echo $payUrl ?>">
    <?php foreach ($payParams as $payParam) {
            if ($payParam['paramName'] == '_WMEMBER_ENCODE_') {
                    $payParam['paramValue'] = $memberEncode;
            }
            ?>
        <input name='<?php echo $payParam['paramName'] ?>' type='hidden' value='<?php echo $payParam['paramValue'] ?>'/>
    <?php } ?>
</form>
<script>
    document.getElementById("GewaraPay").submit();
</script>