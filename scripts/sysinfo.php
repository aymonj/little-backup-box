<?php
	$WORKING_DIR=dirname(__FILE__);
	$config = parse_ini_file($WORKING_DIR . "/config.cfg", false);

	$theme = $config["conf_THEME"];
	$background = $config["conf_BACKGROUND_IMAGE"] == ""?"":"background='/img/backgrounds/" . $config["conf_BACKGROUND_IMAGE"] . "'";
?>

<html lang="en" data-theme="<?php echo $theme; ?>">
<!-- Author: Dmitri Popov, dmpop@linux.com
         License: GPLv3 https://www.gnu.org/licenses/gpl-3.0.txt -->

<head>
	<title>Little Backup Box</title>
	<meta charset="utf-8">
	<link rel="shortcut icon" href="favicon.png" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="css/classless.css">

	<script src="js/refresh_iframe.js"></script>

</head>

<body onload="refreshIFrame()" <?php echo $background; ?>>

	<!-- Suppress form re-submit prompt on refresh -->
	<script>
		if (window.history.replaceState) {
			window.history.replaceState(null, null, window.location.href);
		}
	</script>

	<?php
	// include i18n class and initialize it
	require_once 'i18n.class.php';
	$i18n = new i18n('lang/{LANGUAGE}.json', 'cache/', 'en');
	if ($config["conf_LANGUAGE"] !== "") {$i18n->setForcedLang($config["conf_LANGUAGE"]);}
	$i18n->init();
	?>
	<nav>
		<ul>
			<?php include "${WORKING_DIR}/sub-menu.php"; ?>
		</ul>
	</nav>
	<h1 class="text-center" style="margin-bottom: 1em; letter-spacing: 3px;"><?php echo L::sysinfo_sysinfo; ?></h1>
	<div class="card" style="margin-top: 3em;">
		<?php
		$temp = shell_exec('cat /sys/class/thermal/thermal_zone*/temp');
		$temp = round($temp / 1000, 1);
		$cpuusage = 100 - shell_exec("vmstat | tail -1 | awk '{print $15}'");
		$mem = shell_exec("free | grep Mem | awk '{print $3/$2 * 100.0}'");
		$mem = round($mem, 1);
		$abnormal_conditions = shell_exec("${WORKING_DIR}/system_conditions.sh 'abnormal_conditions'");

		if (isset($temp) && is_numeric($temp)) {
			echo "<p>" . L::sysinfo_temp . ": <strong>" . $temp . "°C</strong></p>";
		}
		if (isset($cpuusage) && is_numeric($cpuusage)) {
			echo "<p>" . L::sysinfo_cpuload . ": <strong>" . $cpuusage . "%</strong></p>";
		}
		if (isset($mem) && is_numeric($mem)) {
			echo L::sysinfo_memory . ": <strong>" . $mem . "%</strong>";
		}
		echo ("<p>Conditions: <strong>" . $abnormal_conditions . "</strong></p>");

		?>
		<h3><?php echo L::sysinfo_devices; ?></h3>
		<?php
		echo '<pre>';
		passthru("lsblk");
		echo '</pre>';
		?>
		<h3><?php echo L::sysinfo_diskspace; ?></h3>
		<?php
			echo '<pre>';
			passthru("df -H");
			echo '</pre>';
		?>
		<div class="text-center"><button onClick="history.go(0)" role="button"><?php echo (L::log_refresh_button); ?></button></div>

	</div>

	<?php include "sub-logmonitor.php"; ?>
		
</body>

</html>
