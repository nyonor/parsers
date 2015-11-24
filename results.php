<?php
/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 03.11.15
 * Time: 9:46
 */

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$files = scandir("files/");
$csvFiles = [];
foreach($files as $file) {
	if(strpos($file,".csv")) {
		$csvFiles[] = $file;
	}
}
arsort($csvFiles);
?>

<html>
	<head>
		<style>
			table {
				width : 400px;
				border-collapse: collapse;
				border: 1px solid black;
			}
			table td {
				border: 1px solid black;
				padding: 5px;
			}

			fieldset {
				max-width: 1000px;
			}
		</style>
	</head>
	<body>
	<fieldset>
		<legend>
			Результаты парсинга
		</legend>
		<table style="border:1px solid black;">
			<?php
			foreach($csvFiles as $file) {
				echo "<tr>
					<td>
						".$file."
					</td>
					<td>
						".gmdate("Y-m-d",filemtime("files/".$file))."
					</td>
					<td>
						<a href='files/".$file."'>
							скачать
						</a>
					</td>
				</tr>";
			}
			?>
		</table>
	</fieldset>
	</body>
</html>
