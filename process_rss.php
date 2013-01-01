<?php
error_reporting(0);

function debug($obj) {
	echo "<pre>";
	print_r($obj);
	echo "</pre>";
}

// define the namespaces that we are interested in
$ns = array
(
        'content' => 'http://purl.org/rss/1.0/modules/content/',
        'wfw' => 'http://wellformedweb.org/CommentAPI/',
        'dc' => 'http://purl.org/dc/elements/1.1/'
);
 
// obtain the articles in the feeds, and construct an array of articles

// step 1: get the feed
$feedUrl = 'http://www.webhostingtalk.com/external.php?forumids=104';
$rawFeed = file_get_contents($feedUrl);
$xml = new SimpleXmlElement($rawFeed);
$ns = $xml->getNamespaces(true);

// step 2: extract the channel metadata
$channel = array();
$channel['title']       = (string) $xml->channel->title;
$channel['link']        = (string) $xml->channel->link;
$channel['description'] = (string) $xml->channel->description;
$channel['pubDate']     = (string) $xml->channel->lastBuildDate;
$channel['pubDate2']   = date("M d Y, h:i:s a", strtotime((string) $xml->channel->lastBuildDate));
$channel['generator']   = (string) $xml->channel->generator;
$channel['language']    = (string) $xml->channel->language;

$count=0;
$article = array();
foreach ($xml->channel->item as $item)
{
    $article[$count]['title'] = (string) $item->title;
    $article[$count]['link'] = (string) $item->link;
	$article[$count]['guid'] = (string) $item->guid;
	$pubDate = (string)$item->pubDate;
	$article[$count]['pubDate'] = $pubDate;
	$article[$count]['pubDate2'] = date("M d Y, h:i:s a", strtotime($item->pubDate));

	// now we can get dublin core content with a lot less typing!
	// we also only have to update the code in one place if the namespace URI changes
	$dc = $item->children($ns['dc']);
	$article[$count]['creator'] = (string)$dc->creator;

	$content = $item->children($ns['content']);
	//$article[$count]['content'] = (string) trim($content->encoded);
	
$count++;
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

//add wht_posts table to database
$query = 'CREATE TABLE wht_posts' .
         '(Title TEXT, Link TEXT, guid TEXT, PostDate TEXT, Creator TEXT, created datetime)';
         
if(!$database->queryExec($query, $error)) {
  //echo $error."<br />";
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
    <th nowrap="nowrap">#</th>
    <th nowrap="nowrap">Title</th>
    <th nowrap="nowrap">Author</th>
    <th nowrap="nowrap">Date</th>
    <th nowrap="nowrap">&nbsp;</th>
  </tr>
<?php
$count = 1;
$new_entry = 0;
foreach ($article as $item) {
// #####################################################################
// EXPERIMENTS!!!
// #####################################################################
$query = "SELECT Creator, PostDate FROM wht_posts WHERE Creator = '".$item[creator]."' AND PostDate = '".$item[pubDate2]."'";
$result = $database->query($query, SQLITE_BOTH, $error);

if($result) {
  // result found, duplicate post. Do nothing
	if ($result->numRows()<1) {
		$new_entry = 1;
		$query = "INSERT INTO wht_posts (Title, Link, guid, PostDate, Creator, created)
					 VALUES ('".sqlite_escape_string($item[title])."', '".$item[link]."', '".$item[guid]."', '".$item[pubDate2]."', '".$item[creator]."', '".date("Y-m-d H:i:s")."') ";
		if(!$database->queryExec($query, $error)) {
			die($error);
		}
	} else {
		$new_entry = 0;
	}
} else {
  die($error);
}


// #####################################################################
// ENDS EXPERIMENTS!!!
// #####################################################################
?>
  <tr>
    <td><?php echo $count; ?></td>
    <td <?php echo $new_entry==1?" style='background-color:#FFCC00'":""; ?>><?php echo $new_entry==1?"<strong>":""; ?><?php echo $item[title]; ?><?php echo $new_entry==1?"</strong>":""; ?></td>
    <td><?php echo $item[creator]; ?></td>
    <td><?php echo $item[pubDate2]; ?></td>
    <td><a href="<?php echo $item[guid]; ?>" target="_blank">VIEW</a> <a href="<?php echo $item[link]; ?>" target="_blank">LATEST?</a></td>
  </tr>
<?php
$count++;
}
?>
  <tr>
    <td colspan="5" align="center" valign="middle"><a href="view_database.php">View SQlite database</a></td>
  </tr>
</table>
</body>
</html>