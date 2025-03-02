<?php
require_once 'include/include.php';

$professions[0] = 'Academia';
$professions[1] = 'Business';
$professions[2] = 'Government';
$professions[3] = 'Industry';
$professions[4] = 'K-12 Teacher';
$professions[5] = 'NGO';
$professions[6] = 'Research Institution';
$professions[7] = 'Student';
$professions[8] = 'United Nations';
$professions[9] = 'Other';

$usage[0] = 'Disaster Risk Reduction';
$usage[1] = 'Education';
$usage[2] = 'Personal';
$usage[3] = 'Research';
$usage[4] = 'Other';

$error = false;
$message = '';
if (isset($_POST['submit'])) {
	$captcha_good = $_POST['g-recaptcha-response'];
	$email_good = $_POST['email'] == $_POST['email2'];
	if (!empty($_POST['message']) && $email_good && $captcha_good) {
		unset($_POST['submit']);
		unset($_POST['email2']);
		unset($_POST['g-recaptcha-response']);

		$_POST['profession'] = $professions[$_POST['profession']];
		$_POST['usage'] = $usage[$_POST['usage']];

		pdoInsert('feedback', $_POST);

		$_POST['email2'] = $_POST['email'];;

		if (!empty($_POST['name']))
			$message .= "Name: " . $_POST['name'] . "\n";

		if (!empty($_POST['email']))
			$message .= "e-mail: " . $_POST['email'] . "\n";

		$message .= "Profession: " . $_POST['profession'] . "\n";

		$message .= "Data usage: " . $_POST['usage'] . "\n";

		if (!empty($_POST['url']))
			$message .= "URL: " . $_POST['url'] . "\n";

		$message .= "Message: " . $_POST['message'] . "\n";

		mail('cafd@ifg.uni-tuebingen.de', 'New Feedback on ' . $GLOBALS['cfg_website'], $message);
	} else {
		$error = true;
	}
}

head('');

if (isset($_POST['submit']) && $error)
	echo '<span style="color: red;">You have a typo in your email or forgot the message or did not complete the captcha.</span><br /><br />';
elseif (isset($_POST['message'])) {
	echo '<span style="color: green;">Feedback received, thank you.</span><br /><br />';
}
?>

<form action="feedback.php" method="POST" autocomplete="false">
	<strong>Your Name</strong><br />
	<input name="name" style="width: 200px;"<?php if (isset($_POST['name'])) echo 'value="' . $_POST['name'] . '"'; ?> /><br /><br />

	<strong>Your e-mail</strong><br />
	<input name="email" style="width: 200px;"<?php if (isset($_POST['email'])) echo 'value="' . $_POST['email'] . '"'; ?> /><br /><br />

	<strong>Confirm e-mail</strong><br />
	<input name="email2" style="width: 200px;"<?php if (isset($_POST['email2'])) echo 'value="' . $_POST['email2'] . '"'; ?> /><br /><br />

	<strong>Your Profession *</strong><br />
	<select name="profession" id="profession" onChange="changed()">
		<?php
			foreach ($professions as $key => $value)
				echo '<option value="' . $key . '"' . ($_POST['profession'] == $key ? ' selected' : '') . '>' . $value . '</option>';
		?>
	</select><br /><br />

	<strong>What do you use the data for? *</strong><br />
	<select name="usage">
		<?php
			foreach ($usage as $key => $value)
				echo '<option value="' . $key . '"' . ($_POST['usage'] == $key ? ' selected' : '') . '>' . $value . '</option>';
		?>
	</select><br /><br />

	<strong>Do you have a site to link to the fault database?</strong><br />
	<input name="url" style="width: 200px;"<?php if (isset($_POST['url'])) echo 'value="' . $_POST['url'] . '"'; ?> /><br /><br />

	<strong>Message *</strong><br />
	<textarea name="message" rows="10"><?php if (isset($_POST['message'])) echo $_POST['message']; ?></textarea><br /><br />
	<div class="g-recaptcha" data-sitekey="<?php echo $cfg_captcha_sitekey; ?>"></div><br />
	<input type="submit" name="submit" value="Send Feedback" /><br /><br />
	* required
</form>

<?php
foot();
?>
