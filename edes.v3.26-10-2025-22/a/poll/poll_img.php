<?PHP
$IMG = $_GET["IMG"]*1;
DB::query("select * from {$_ENV['SYSDB']}gs_poll_img where cd_gs_poll_img=".$IMG);
$r = DB::get();
?>
<script>
top.eCallSrv(window,"edes.php?D:/http/poll/<?=$IMG?>.<?=$r['extension']?>");
</script>