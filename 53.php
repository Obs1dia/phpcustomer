<?php

session_start();

require_once("db.inc");


function saveGet($var)
{
	$data = "";

	if (isset($_GET[$var]))
	{
		$data = $_GET[$var];
	}

	return $data;
}

function search($nimi = "", $osoite = "", $postinro = "", $tyyppi = "", $conn)
{
	$query = "SELECT 
			avain, 
			nimi, 
			osoite, 
			postinro, 
			postitmp, 
			asty_avain 
			FROM asiakas
			WHERE nimi LIKE '%$nimi%' 
			AND osoite LIKE '%$osoite%' 
			AND postinro LIKE '%$postinro%'
			AND asty_avain LIKE '%$tyyppi%'";

	$tulos = mysqli_query($conn, $query);

	if (!$tulos)
	{
		return ["err" => mysqli_error($conn)];
	}
	else
	{		
		$rows = array();

		while ($rivi = mysqli_fetch_array($tulos, MYSQL_ASSOC)) {
			$rows[] = $rivi;
		}

		return $rows;
	}
}

function delFromDb($key, $conn)
{
	$query = "DELETE FROM asiakas WHERE avain = $key";

	$tulos = mysqli_query($conn, $query);

	if (!$tulos)
	{
		return ["err" => mysqli_error($conn)];
	}
	else
	{
		$affected = mysqli_affected_rows($conn);

		if ($affected > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}

function saveToDb($nimi = "", $osoite = "", $postinro = "", $tyyppi = "", $postitmp = "", $conn)
{
	$nimi = strtoupper($nimi);
	$osoite = strtoupper($osoite);
	$postitmp = strtoupper($postitmp);

	$query = "INSERT INTO 
			asiakas (nimi, osoite, postinro, postitmp, asty_avain)
			VALUES ('$nimi', '$osoite', '$postinro', '$postitmp', '$tyyppi')";
	$tulos = mysqli_query($conn, $query);
	if (!$tulos)
	{
		return ["err" => mysqli_error($conn)];
	}
	else
	{
		$affected = mysqli_affected_rows($conn);

		if ($affected > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}

function getCustomerTypes($conn)
{
	$query = "SELECT avain,
			lyhenne,
			selite
			FROM asiakastyyppi";

	$tulos = mysqli_query($conn, $query);

	if (!$tulos)
	{
		return ["err" => mysqli_error($conn)];
	}
	else
	{		
		$rows = array();

		while ($rivi = mysqli_fetch_array($tulos, MYSQL_ASSOC)) {
			$rows[] = $rivi;
		}

		return $rows;
	}
}

function editCustomer($nimi, $osoite, $postinro, $postitmp, $tyyppi, $key, $conn)
{
	$query = "UPDATE asiakas 
	SET nimi = '$nimi',
	osoite = '$osoite', 
	postinro = '$postinro',
	postitmp = '$postitmp',
	tyyppi = '$tyyppi'
	WHERE avain = $key";

	$tulos = mysqli_query($conn, $query);

	if (!$tulos)
	{
		return ["err" => mysqli_error($conn)];
	}
	else
	{
		$affected = mysqli_affected_rows($conn);

		if ($affected > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}
$types = array();

$types = getCustomerTypes($conn);

$result = array();

if (isset($_GET["submit"]))
{
	$name = saveGet("nimi");
	$address = saveGet("osoite");
	$postal = saveGet("postinro");
	$type = saveGet("tyyppi");

	$_SESSION["haku"]["nimi"] = $name;
	$_SESSION["haku"]["osoite"] = $address;
	$_SESSION["haku"]["postinro"] = $postal;
	$_SESSION["haku"]["tyyppi"] = $type;

	$result = search($name, $address, $postal, $type, $conn);
}
else if (isset($_GET["del"]))
{
	$poistoKey = saveGet("del");
	if ($poistoKey != "" && isset($_SESSION["haku"]))
	{
		$poisto = delFromDb($poistoKey, $conn);

		$result = search(
			$_SESSION["haku"]["nimi"], 
			$_SESSION["haku"]["osoite"], 
			$_SESSION["haku"]["postinro"], 
			$_SESSION["haku"]["tyyppi"],
			$conn
		);
	}
	else
	{
		$result["err"] = "Hakuparametrit hukkuivat matkalla";
	}
}
else if (isset($_GET["add"]))
{
	$name = saveGet("nimi");
	$address = saveGet("osoite");
	$postal = saveGet("postinro");
	$type = saveGet("tyyppi");
	$postitmp = saveGet("postitmp");

	$lisays = saveToDb($name, $address, $postal, $type, $postitmp, $conn);

	$result = search(
		$_SESSION["haku"]["nimi"], 
		$_SESSION["haku"]["osoite"], 
		$_SESSION["haku"]["postinro"], 
		$_SESSION["haku"]["tyyppi"],
		$conn
	);
}
else if (isset($_GET["edit"]))
{
	$muokkausKey = saveGet("edit");
	if($muokkausKey != "" && isset($_SESSION["haku"]))
	{
		$name = saveGet("nimi");
		$address = saveGet("osoite");
		$postal = saveGet("postinro");
		$type = saveGet("tyyppi");
		$postitmp = saveGet("postitmp");

		$muokkaus = editCustomer($name, $address, $postal, $type, $postitmp, $muokkausKey, $conn);

	$result = search(
		$_SESSION["haku"]["nimi"], 
		$_SESSION["haku"]["osoite"], 
		$_SESSION["haku"]["postinro"], 
		$_SESSION["haku"]["tyyppi"],
		$conn
	);
	}
	else
	{
		$result["err"] = "vituiks meni";
	}
	
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
	<style>
		.success {
			display: block;
			color: green;
		}

		.error {
			display: block;
			color: red;
		}
	</style>
</head>
<body>
	<form action="53.php" method="get">
		Nimi: <input type="text" name="nimi"><br>
		Osoite: <input type="text" name="osoite"><br>
		Postinumero: <input type="text" name="postinro"><br>
		<?php if (!isset($types["err"])) : ?>
			<select name="tyyppi">
				<option value="">Ei valittua asiakastyyppia</option>
				<?php foreach($types as $type) : ?>
					<option value="<?= $type["avain"] ?>"><?= $type["selite"] ?></option>
				<?php endforeach; ?>
			</select>
		<?php endif; ?>
		<input type="submit" name="submit" value="Hae">
	</form>
	<a href="53.php?add_intent=true">Lisää uusi asiakas</a>
	<?php if (isset($lisays) && $lisays) : ?>
		<span class="success">Asiakkaan luominen onnistui!</span>
	<?php elseif (isset($lisays)) : ?>
		<span class="error">Asiakkaan luomisessa tapahtui virhe!</span>
	<?php endif; ?>
	<?php if ((isset($_GET["submit"]) && !isset($result["err"])) ||	(isset($_GET["del"]) && !isset($result["err"])) ||	(isset($_GET["add"]) && !isset($result["err"]))) : ?>
		<?php if(isset($poisto) && $poisto) : ?>
			<span class="success">Asiakkaan poistaminen onnistui!</span>
		<?php elseif (isset($_GET["del"])) : ?>
			<span class="error">Poistettavaa asiakasta ei löytynyt</span>
		<?php endif; ?>
		<table>
			<tr>
				<th>Nimi</th>
				<th>Osoite</th>
				<th>Postinumero</th>
				<th>Paikkakunta</th>
				<th>Poista</th>
				<th>Muokkaa</th>
			</tr>
			<?php foreach($result as $res) : ?>
				<tr>
					<td><?= $res["nimi"] ?></td>
					<td><?= $res["osoite"] ?></td>
					<td><?= $res["postinro"] ?></td>
					<td><?= $res["postitmp"] ?></td>
					<td><a href="53.php?del=<?= $res["avain"] ?>">Poista</a></td>
					<td><a href="53.php?edit=<?= $res["avain"] ?>">Muokkaa</a></td>
				</tr>
			<?php endforeach; ?>
		</table>
	<?php elseif (isset($result["err"])) : ?>
		<span class="error"><?= $result["err"] ?></span>
	<?php elseif (isset($_GET["add_intent"])) : ?>
		<h2>Uuden asiakkaan luominen</h2>
		<form action="53.php" method="get">
			Nimi: <input type="text" name="nimi"><br>
			Osoite: <input type="text" name="osoite"><br>
			Postinumero: <input type="text" name="postinro"><br>
			Postitoimipaikka: <input type="text" name="postitmp"><br>
			<?php if (!isset($types["err"])) : ?>
				<select name="tyyppi">
					<?php foreach($types as $type) : ?>
						<option value="<?= $type["avain"] ?>"><?= $type["selite"] ?></option>
					<?php endforeach; ?>
				</select>
			<?php endif; ?>
			<input type="submit" name="add" value="OK">
			<input type="submit" name="cancel" value="Peruuta">
		</form>
	<?php elseif (isset($_GET["edit"])) : ?>
		<h2>Asiakkaan muokkaus</h2>
		<form action="53.php" method="get">
			Nimi: <input type="text" name="nimi" value="<?=$name;?>"><br>
			Osoite: <input type="text" name="osoite"><br>
			Postinumero: <input type="text" name="postinro"><br>
			Postitoimipaikka: <input type="text" name="postitmp"><br>
			<?php if (!isset($types["err"])) : ?>
				<select name="tyyppi">
					<?php foreach($types as $type) : ?>
						<option value="<?= $type["avain"] ?>"><?= $type["selite"] ?></option>
					<?php endforeach; ?>
				</select>
			<?php endif; ?>
			<input type="submit" name="edit" value="OK">
			<input type="submit" name="cancel" value="Peruuta">
		</form>
	<?php endif; ?>
</body>
</html>