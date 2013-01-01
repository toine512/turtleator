<?php
$time_start = microtime(true);
//Default Tutleator
$title = 'Mine Turtle';
$vid = 'DI5_sQ8O-7Y';
$loop = false;
$start = 0;
$end = 0;

//Does GET v exist ?
if(isset($_GET['v']))
{
	if(!empty($_GET['v']))
	{
		if($_GET['v'] != 'turtle' && $_GET['v'] != 'mineturtle' && $_GET['v'] != 'mine turtle') //You explicitly want to be turtle'd here! So we'll keep default Turtleator values.
		{
			require('bookmarks.array.php'); //Loads $bookmarks
			if(array_key_exists($_GET['v'], $bookmarks)) //Is it a bookmark ?
			{
				$title = $bookmarks[$_GET['v']]['title'];
				$vid = $bookmarks[$_GET['v']]['id'];
				if(array_key_exists('loop', $bookmarks[$_GET['v']]))
				{
					$loop = true;
				}
				if(array_key_exists('start', $bookmarks[$_GET['v']]))
				{
					$start = $bookmarks[$_GET['v']]['start'];
				}
				if(array_key_exists('start', $bookmarks[$_GET['v']]))
				{
					$end = $bookmarks[$_GET['v']]['end'];
				}
			}
			else if(strlen($_GET['v'] = trim($_GET['v'])) > 10) //No, then it's Youtube video id. (11 chars)
			{
				$title = 'ಠ_ಠ';
				$vid = htmlspecialchars(substr($_GET['v'], 0, 11)); //"turtle" is 6 chars long, so it is able to pass here. (see below)
			}
			//Else you're being turtle'd anyway.
		}
	}
}

//Loop forcing, does GET l exist ?
//Overrides bookmark settings
if(isset($_GET['l']))
{
	if($_GET['l'] == 'false' || $_GET['l'] == '0')
	{
		$loop = false;
	}
	else
	{
		$loop = true;
	}
}
?>
<!DOCTYPE html>

<html lang="en">
<head>
	<meta charset="utf-8" />
	<title><?php echo $title; ?></title>
<?php if($vid == 'DI5_sQ8O-7Y') {echo '	<link rel="icon" href="mineturtle.png" sizes="32x32" type="image/png" />' . "\n";} ?>
	<style>
html, body, iframe {
	position: absolute;
	overflow: hidden;
	margin: 0;
	padding: 0;
	width: 100%;
	height: 100%;
	border: none;
}
	</style>
</head>
<body>
	<iframe seamless src="https://www.youtube.com/embed/<?php echo $vid; ?>?modestbranding=1&amp;autoplay=1&amp;rel=0&amp;<?php echo /*Player controls forcing*/ (isset($_GET['c'])) ? 'controls=1&amp;showinfo=1' : 'controls=0&amp;showinfo=0'; /***/ /*Loop control*/ if($loop) {echo '&amp;loop=1&amp;playlist=,';} /***/ /*Video start time*/ if($start > 0) {echo '&amp;start=' . $start;} /***/ /*Video end time*/ if($end > 0) {echo '&amp;end=' . $end;} /***/?>"></iframe>
</body>
<!-- See discover.php for the list of bookmarks and some minimalistic instructions. -->
<!-- Powered by GLaDOS. -->
<?php
$time_end = microtime(true);
echo '<!-- Processing time : ' . round(($time_end - $time_start) * 1000, 4) . " ms -->\n";
?>
</html>