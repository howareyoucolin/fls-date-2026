<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(isset($_POST['content'])){
	$postContent = substr($_POST['content'],0,600);
	file_put_contents(dirname(__FILE__).'/intro.txt', $postContent);
}
$content = file_get_contents(dirname(__FILE__).'/intro.txt');
$content = substr($content,0,600);
?>Write here:<br/>
<form method="post" action="#">
<textarea name="content" style="height: 500px;width: 500px;"><?php echo $content;?></textarea><br/>
<input style="height: 50px;width: 500px;" type="submit" value="save" />
</form>