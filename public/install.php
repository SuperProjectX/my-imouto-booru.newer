<?php
require dirname(__FILE__) . '/../boot.php';
MoeBooru::boot();

in_array($_SERVER['REMOTE_ADDR'], MoeBooru::application()->config('app', 'rails_admin_ips')) || die('ACCESS DENIED FOR '.$_SERVER['REMOTE_ADDR']);

function encrypt_password($password)
{
    return sha1(CONFIG()->user_password_salt . '--' . $password . '--');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['upgrade'])) {
        $queries = array(
            "ALTER TABLE posts ADD approver_id INT, ADD CONSTRAINT posts__approver_id FOREIGN KEY (approver_id) REFERENCES users(id) ON DELETE SET NULL;"
        );
        
        foreach ($queries as $query)
            ActiveRecord::execute_sql($query);
        
        if (empty($notice))
            $notice = 'Upgrade completed';
        $redirect = '/post';
    } else {
        $queries = include 'db_queries.php';

        foreach ($queries as $query)
            ActiveRecord::execute_sql($query);

        extract($_POST);
        $password_hash = encrypt_password($password);

        ActiveRecord::execute_sql('INSERT INTO users (created_at, name, password_hash, level, show_advanced_editing) VALUES (?, ?, ?, ?, ?)', gmd(), $name, $password_hash, 50, 1);
        $user_id = ActiveRecord::connection()->lastInsertId();
        ActiveRecord::execute_sql('INSERT INTO user_blacklisted_tags VALUES (?, ?)', $user_id, implode("\r\n", CONFIG()->default_blacklists));
        ActiveRecord::execute_sql("UPDATE table_data set row_count = row_count + 1 where name = 'users'");
        
        setcookie('login', $name, time() + 31556926, '/');
        setcookie('pass_hash', $password_hash, time() + 31556926, '/');
        
        $dp = RAILS_ROOT . '/public/data';
        foreach (array($dp.'/', "$dp/avatars/", "$dp/export/", "$dp/image/", "$dp/import/", "$dp/jpeg/", "$dp/preview/", "$dp/sample/") as $dir)
            @mkdir($dir);
        $notice = 'Installation completed';
        $redirect = '/post';
    }
    
    unlink(RAILS_ROOT . '/public/index.php');
    unlink(RAILS_ROOT . '/public/db_queries.php');
    rename(RAILS_ROOT . '/public/index', RAILS_ROOT . '/public/index.php');
    
    setcookie('notice', $notice);
    header('Location: '.$redirect);
    exit;
}

if (function_exists('imagecreatetruecolor')) {
    $gd2 =    "Enabled";
    $gd2_class = "good";
} else {
    $gd2 =    "Not enabled";
    $gd2_class = "bad important";
}

if (function_exists('finfo_open')) {
    $finfo =    "Enabled";
    $finfo_class = "good";
} else {
    $finfo =    "Not enabled";
    $finfo_class = "bad";
}

if (class_exists('PDO')) {
    $pdo =    "Enabled";
    $pdo_class = "good";
} else {
    $pdo =    "Not enabled";
    $pdo_class = "bad important";
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
  <title><?php echo CONFIG()->app_name ?></title>
  <meta name="description" content=" ">
  <link rel="top" title="<?php echo CONFIG()->app_name ?>" href="/">
  <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
  <link href="/stylesheets/application.css" media="screen" rel="stylesheet" type="text/css">
  <script src="/javascripts/application.js" type="text/javascript"></script>
  <script src="/javascripts/legacy.js" type="text/javascript"></script>
  <style type="text/css">
    .center_box{
      width:550px;
      margin-left:auto;
      margin-right:auto;
      margin-bottom:10px;
    }
    
    .good{ color:#0f0;}
    .okay{ color:orange;}
    .bad{ color:red;}
  </style>
</head>
<body>

  <div class="overlay-notice-container" id="notice-container" style="display: none;">
    <table cellspacing="0" cellpadding="0"> <tbody>
      <tr> <td>
        <div id="notice">
        </div>
      </td> </tr>
    </tbody> </table>
  </div>

  <div id="content">

    <h1 id="static-index-header" style="margin-bottom:50px;"><a href="/"><?php echo CONFIG()->app_name ?></a></h1>
    
    <?php if (is_dir(RAILS_ROOT.'/public/data')) : ?>
    <div class="center_box" style="text-align:center;">
      <div class="center_box">
        <h5 style="">Upgrade to version 0.1.9</h5>
        <br />
      </div>
      <form action="" method="post" name="install_form">
        <input type="hidden" name="upgrade" value="true" />
        <p style="margin:50px 0px;"><input type="submit" value="Upgrade" style="font-size:2em;" /></p>
        <p style="margin:50px 0px;">(To show the installation form, the /public/data folder must not exist)</p>
      </form>
      <p style="text-align:left;"></p>
    </div>
    <?php ;else: ?>
    
    <div class="center_box"><h5>PHP.ini directives</h5></div>
    <table class="form" style="margin-left:auto; margin-right:auto; width:550px; text-align:center;">

      <tr>
        <th style="text-align:center; background-color:#555;">Name</th>
        <th style="text-align:center; background-color:#555;">Current value</th>
        <th style="text-align:center; background-color:#555;">Recommended min. value</th>
      </tr>
      
      <tr>
        <th>memory_limit</th>
        <td><?php echo ini_get('memory_limit') ?></td>
        <td>128M+</td>
      </tr>
      
      <tr>
        <th>post_max_size</th>
        <td><?php echo ini_get('post_max_size') ?></td>
        <td>6M</td>
      </tr>
      
      <tr>
        <th>upload_max_filesize</th>
        <td><?php echo ini_get('upload_max_filesize') ?></td>
        <td>5M</td>
      </tr>
      
      <tr>
        <th>GD2 extension</th>
        <td class="<?php echo $gd2_class ?>" id="finfo_info"><?php echo $gd2 ?></td>
        <td>Must be enabled</td>
      </tr>
      
      <tr>
        <th>Fileinfo extension</th>
        <td class="<?php echo $finfo_class ?>" id="finfo_info"><?php echo $finfo ?></td>
        <td>Recommended</td>
      </tr>
      
      <tr>
        <th>PDO extension</th>
        <td class="<?php echo $pdo_class ?>" id="finfo_info"><?php echo $pdo ?></td>
        <td>Must be enabled</td>
      </tr>
    
    </table>
    
    <br />
    <br />
    
    <div class="center_box"><h5>Admin account</h5></div>
    <form action="" method="post" name="install_form">
      <table class="form" style="margin-left:auto; margin-right:auto; width:550px;">
        <tr>
          <th>Name</th>
          <td><input type="text" name="name" id="name" /></td>
        </tr>
        
        <tr>
          <th>Password</th>
          <td><input type="password" name="password" id="pw" /></td>
        </tr>
        
        <tr>
          <th>Confirm password</th>
          <td><input type="password" name="confirm_pw" id="pwc" /></td>
        </tr>
        <tr>
          <td><input type="submit" value="Install" onclick="validate_form(); return false;" /></td>
        </tr>
      </table>
    </form>
  </div>
  <?php endif ?>
  
  <script type="text/javascript">
  function validate_form() {
    if ($$('.bad.important').length) {
      notice('System requirements not met');
      return false;
    }
    
    var pw = $('pw').value;
    var pwc = $('pwc').value;
    var name = $('name').value;
    
    if ( name == '' ) {
      notice("Enter a name");
      $('name').focus();
      return false;
    } else if ( name.length < 2 ) {
      notice("Name must be at least 2 characters long");
      return false;
    } else if ( pw == '' ) {
      notice("Enter a password");
      $('pw').focus();
      return false;
    } else if ( pw.length < 5 ) {
      notice("Password must be at least 5 characters long");
      $('pw').focus();
      return false;
    } else if ( pw != pwc ) {
      notice("Passwords don't match");
      $('pwc').focus();
      return false;
    } else
      document.install_form.submit()
  }

  var text = Cookie.get("notice");
  if (text) {
    notice(text, true);
    Cookie.remove("notice");
  }
  </script>
</body>
</html>