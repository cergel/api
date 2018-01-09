<form id="GewaraPay" method="<?php echo $httpMethod ?>" action="<?php echo $payUrl ?>">
    <?php foreach ($payParams as $key => $val) {
        ?>
        <input name='<?php echo $key ?>' type='hidden'
               value='<?php echo $val ?>'/>
    <?php } ?>
</form>
<script>
    document.getElementById("GewaraPay").submit();
</script>