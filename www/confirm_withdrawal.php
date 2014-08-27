<?php include("includes/header.php"); ?>

<div class="box box-main">
<div id="out">
Loading...
</div>
</div>

<script>

window.onload = function(){
	checkWithdrawal();
}

function checkWithdrawal(){
    var w_hash = getUrlVars()['verification_code'];
    $.post( "api/api", { method: "confirmWithdrawal", hash: w_hash }, function( data ) {
        $("#out").text(data.result);
    }, "json");
}
</script>

<?php include("includes/footer.php"); ?>