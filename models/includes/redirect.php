<!DOCTYPE html>
<html>
  <head>
    <title>Redirect</title>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
    <meta http-equiv="Location" content="<?php echo $sSafeRedirectURL; ?>">
    <meta http-equiv="Refresh" content="0;url=<?php echo $sSafeRedirectURL; ?>">
  </head>
  <body>
    <a href="<?php echo $sSafeRedirectURL; ?>"><?php echo $sRedirectURL; ?></a>
  </body>
</html>