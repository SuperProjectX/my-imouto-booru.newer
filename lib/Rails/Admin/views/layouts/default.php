<!doctype html>
<html>
<head>
  <title>System</title>
  <style type="text/css">
    body { background-color: #fff; color: #333; }

    body, p, ol, ul, td {
      font-family: helvetica, verdana, arial, sans-serif;
      font-size:   13px;
      line-height: 18px;
    }

    pre {
      background-color: #eee;
      padding: 10px;
      font-size: 11px;
      overflow: auto;
    }
    
    pre.error_info {
        max-height:400px;
    }

    a { color: #000; }
    a:visited { color: #000; }
    a:hover { color: #fff; background-color:#000; }
  </style>
</head>
<body>
<?php echo $this->yield() ?>
</body>
</html>