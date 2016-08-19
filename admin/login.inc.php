<?php
if(!defined('MVMMALL')) exit('Access Denied');

if($m_check_id) move_page('admincp.php?module=index');
if($setp==1 && $_POST)
{
    require_once 'include/captcha.class.php';
    $Captcha= new Captcha();
    !$Captcha->CheckCode($code) && show_msg('code_wrong');
    
    $ori_pass=dhtmlchars($login_pass);
    $login_pass = md5($ori_pass);
    $login_id = dhtmlchars($login_id);
    require_once 'include/passport.inc.php';
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Login &ndash; MVMMALL</title>
<script type="text/javascript">
function check_main_login()
{
	var m=document.mvmmall_main_login;
	if(m.login_id.value.length=="")
	{
		alert("Please enter your username.");
		m.login_id.focus();
		return false;
	}
	if(m.login_pass.value.length=="")
	{
		alert("Please enter your password.");
		m.login_pass.focus();
		return false;
	}
}
</script>
<script type="text/javascript" src="include/javascript/jquery.js"></script>
<script type="text/javascript" src="include/javascript/mvm.js"></script>
<style type="text/css">
body{background:#333;font-family:Helvetica, Arial, sans-serif;font-size:12px;color:#555;margin:0}
a{text-decoration:none;color:#131f22;outline:none}
a:hover{color:#666}
img{border:none}
#login{background:#eee;width:360px;-moz-border-radius:7px;-webkit-border-radius:7px;overflow:auto;margin:100px auto 0;padding:20px}
#login h1{background:url(images/admincp/login-header.png);width:47px;height:21px;text-indent:-9999px;border:none;-moz-border-radius:0;-webkit-border-radius:0;margin:0 0 20px;padding:0; overflow:hidden;}
#login p{margin:0 0 15px;padding:0}
#login label{display:block;width:70px;float:left;font-weight:700;margin:12px 0 0}
#login input{width:278px;-moz-border-radius:3px;-webkit-border-radius:3px;color:#444;font-size:12px;background:#f9f9f9;border:1px solid #ccc;padding:8px 5px}
#login input.submit{background:#49727a;font-weight:700;color:#fff;width:120px;border:none;float:right;text-shadow:#1a80a7 0 1px 0;margin:2px 0 0}
#login a#lostpass{float:left;color:#999;display:block;margin:12px 0 0 140px}
#login a:hover#lostpass{color:#12617e}
.copy { text-align:center; color:#fff; font-size:11px; }
.copy a,.copy a:hover { color:#fff; }
.clear { clear:both; height:10px; }
</style>
</head>

<body>
<div id="wrapper">
  <div id="login">
    <h1>Login</h1>
		<form method="post" action="" name="mvmmall_main_login" onSubmit="javascript: return check_main_login()">
		<input type="hidden" name="action" value="login"/>
		<input type="hidden" name="referer" value="admincp.php?module=index"/>
		<input type="hidden" name="setp" value="1"/>
      <p>
        <label for="username">Username</label>
        <input type="text" name="login_id" class="text" value="<?php echo $m_check_id?>" <?php echo $disabled?>/>
      </p>
      <p>
        <label for="password">Password</label>
        <input type="password" name="login_pass" class="text"/>
      </p>
      <p>
        <label for="captcha">Captcha</label>
        <input name="code" type="text" class="inp" size="6" style="float:left; width:200px; margin-right:8px;" />
		<img align="absmiddle" src="ajax.php?action=code&rnd=$m_now_time" rel="code" onClick="this.src='ajax.php?action=code&rnd='+Math.random();" style="cursor:pointer; height:32px; float:left;" alt="验证码" />  
      </p>
      <div class="clear"></div>
      <p><a href="lostpass.php?action=lostpasswd" id="lostpass">Lost password</a>
        <input type="submit" value="Login" class="submit" />
      </p>
    </form>
  </div>
	<p class="copy"><a href="http://www.mvmmall.cn/" target="_blank">Powered by Mvmmall</a>  &copy; 2004-2014 mvmmall Inc.</p>
</div>
</body>
</html>