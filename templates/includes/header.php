<?php echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>"; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html lang="en-US" xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title><?php if (isset($sPageTitle) && !empty($sPageTitle)) echo $sPageTitle . ' - '; ?>BnetDocs: Phoenix</title>
<?php if (isset($sPageAdditionalStyle) && !empty($sPageAdditionalStyle)) {
?>    <link rel="stylesheet" href="<?php echo $sPageAdditionalStyle; ?>" type="text/css" media="all" />
<?php }
?>  </head>
  <body>
