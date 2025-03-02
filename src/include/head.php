	<div id="top_left">
		<a href="<?php echo $GLOBALS['url_uni_tue']; ?>" target="_blank"><img src="images/logo-uni-tuebingen.png" /></a>
	</div>
	<div id="top_middle">
		<h1><a href="index.php" class="link_head"><strong style="color: #A51E38;"><?php echo $GLOBALS['cfg_website']; ?></strong></a></h1>
	</div>
	<div id="top_space" class="clear small_space"></div>
	<div id="image_bar">
		<img src="images/bar5.png" /><img src="images/bar3.png" /><img src="images/bar0.png" /><img src="images/bar1.png" /><img src="images/bar7.png" />
	</div>
<div class="line"></div>
<?php

if (!isset($_GET['id'])) $_GET['id'] = 0;

$navigation = array();

array_push($navigation, array('index.php', 'SHOW MAP'));
array_push($navigation, array('search.php', 'SEARCH FAULTS'));
array_push($navigation, array('downloads.php', 'DOWNLOADS'));
array_push($navigation, array('feedback.php', 'FEEDBACK'));
array_push($navigation, array('about.php', 'ABOUT'));
array_push($navigation, array('resources.php', 'RESOURCES'));

function gen_navbar($navigation) {
	foreach ($navigation as $value) {
			$url = basename($_SERVER['PHP_SELF']);
			if ($url == $value[0])
				echo '<li class="current">' . $value[1] . '</li>';
			else
				echo '<li><a href="' . $value[0] . '">' . $value[1] . '</a></li>';
		}
}

?>

<div id="navigation">
	<ul>
		<?php gen_navbar($navigation); ?>
	</ul>
</div>
<div class="line"></div><br />

<?php echo (!empty($page) ? '<h2>' . $page . '</h2>' : ''); ?>
