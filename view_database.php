<?php
error_reporting(0);

function debug($obj) {
	echo "<pre>";
	print_r($obj);
	echo "</pre>";
}

// #####################################################################
// EXPERIMENTS!!!
// #####################################################################

try {
  //create or open the database
  $database = new SQLiteDatabase('wht_vps_forum.sqlite', 0666, $error);
} catch(Exception $e) {
  die($error);
}

// #####################################################################
// ENDS EXPERIMENTS!!!
// #####################################################################
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body>
<table width="100%" border="0" cellspacing="0" cellpadding="3">
  <tr>
    <th nowrap="nowrap">Title</th>
    <th nowrap="nowrap">Author</th>
    <th nowrap="nowrap">Date</th>
    <th nowrap="nowrap">&nbsp;</th>
  </tr>
<?php
$query = "SELECT * FROM wht_posts ORDER BY created DESC";
if($result = $database->query($query, SQLITE_BOTH, $error)) {
  while($row = $result->fetch()) {
?>
  <tr>
    <td><?php echo $row['Title']; ?></td>
    <td><?php echo $row['Creator']; ?></td>
    <td><?php echo $row['created']; ?></td>
    <td><a href="<?php echo $row[guid]; ?>" target="_blank">VIEW</a> <a href="<?php echo $row[link]; ?>" target="_blank">LATEST?</a></td>
  </tr>
<?php
  }
} else {
  die($error);
}
?>
</table>
</body>
</html>