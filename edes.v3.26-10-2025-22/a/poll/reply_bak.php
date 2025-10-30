<?PHP
if( S::$_User!=$_POST["cd_gs_reply_user"] ){
eEnd();
}
DB::query("select * from {$_ENV['SYSDB']}gs_question where cd_gs_poll=".($_POST["cd_gs_poll"]*1)." and cd_gs_question=".($_POST["cd_gs_question"]*1));
$r = DB::get();
if( $_POST["cd_gs_question"]!=$r["cd_gs_question"] ){
eEnd();
}
if( DB::count("gs_reply", "cd_gs_poll='{$_POST['cd_gs_poll']}' and cd_gs_campaign='{$_POST['cd_gs_campaign']}' and cd_gs_reply_user='{$_POST['cd_gs_reply_user']}' and cd_gs_question='{$_POST['cd_gs_question']}'") ){
DB::query("delete from {$_ENV['SYSDB']}gs_reply where cd_gs_poll='{$_POST['cd_gs_poll']}' and cd_gs_campaign='{$_POST['cd_gs_campaign']}' and cd_gs_reply_user='{$_POST['cd_gs_reply_user']}' and cd_gs_question='{$_POST['cd_gs_question']}'");
}
eEnd();
?>