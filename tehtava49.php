<?php
session_start();

require_once("db.inc");

function tallenaGetData($index)
{
	$data = "";
	
	if(isset($_GET[$index]))
	{
		$data = $_GET[$index];
	}
	return $index;
}

function haku($nimi = "", $osoite = "", $postinro = "", $tyyppi = "", $conn)
{
	$query = "Select avain, nimi, osoite, postinro, asty_avain 
			from asiakas 
			where 1=1
			and nimi like '%$nimi%' 
			and osoite like '%$osoite%' 
			and postinro like '%$postinro%' 
			and asty_avain like '%$tyyppi%' ";
			
	$tulos = mysqli_query($conn, $query);
	
	if(!$tulos)
	{
		return ["virhe" =>  mysqli_error($conn)];
	}
	else
	{
		$rivit = array();
		
		while ($rivi = mysqli_fetch_array($tulos, MYSQL_ASSOC)) {
			$rivit[] = $rivi;
		}
		
		return $rivit;
	}
}

function delData($arvo, $conn)
{
	$query = "delete from asiakas where avain = $arvo";
	
	$tulos = mysqli_query($conn, $query);
	
	if(!$tulos)
	{
		return ["virhe" =>  mysqli_error($conn)];
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

function tallennaDB ($nimi = "", $osoite = "", $postinro = "", $tyyppi = "", $postitmp = "", $conn)
{
	$nimi = strtoupper($nimi);
	$osoite = strtoupper($osoite);
	$postinro = strtoupper($postinro);
	
	$query = "insert into asiakas (nimi, osoite, postinro, postitmp, asty_avain)
			values ('$nimi', '$osoite', '$postinro', '$postitmp', '$tyyppi')";
			
	$tulos = mysqli_query($conn, $query);
	
	if(!$tulos)
	{
		return ["virhe" =>  mysqli_error($conn)];
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

function getAsType($conn)
{
	$query = "select avain, lyhenne, selite from asiakastyyppi";
	
	$tulos = mysqli_query($conn, $query);
	
	if(!$tulos)
	{
		return ["virhe" =>  mysqli_error($conn)];
	}
	else
	{
		$rivit = array();
		
		while ($rivi = mysqli_fetch_array($tulos, MYSQL_ASSOC)) {
			$rivit[] = $rivi;
		}
		
		return $rivit;
	}

}
$custTypes = array();

$custTypes = getAsType($conn);

$tulokset= array();
if (isset($_GET["submit"]))
{
	$name = tallenaGetData("nimi");
	$address = tallenaGetData("osoite");
	$postal = tallenaGetData("postinro");
	$type = tallenaGetData("tyyppi");
	
	$_SESSION["haku"]["nimi"]=$name;
	$_SESSION["haku"]["osoite"]=$address;
	$_SESSION["haku"]["postinro"]=$postal;
	$_SESSION["haku"]["tyyppi"]=$type;
	
	$tulokset=haku($name, $address, $postal, $type, $conn);
}
else if (isset($_GET["delete"]))
{
	$poistoavain = tallenaGetData("delete");
	if ($poistoavain != "" && isset($_SESSION["haku"]))
	{
		$poisto = delData($poistoavain. $conn);
		
		$tulokset = haku(
		$_SESSION["haku"]["nimi"],
		$_SESSION["haku"]["osoite"],
		$_SESSION["haku"]["postinro"],
		$_SESSION["haku"]["tyyppi"],
		$conn);
	}
	else 
	{
		return ["virhe" =>  mysqli_error($conn)];
	}
	
}
else if (isset ($_GET["lisaa"]))
{
	$name = tallenaGetData("nimi");
	$address = tallenaGetData("osoite");
	$postal = tallenaGetData("postinro");
	$type = tallenaGetData("tyyppi");
	$postitmp = tallenaGetData("postitmp");
	
	$lisays = tallennaDB($name, $address, $postal, $type, $postitmp, $conn);
	
	$tulokset = haku(
		$_SESSION["haku"]["nimi"],
		$_SESSION["haku"]["osoite"],
		$_SESSION["haku"]["postinro"],
		$_SESSION["haku"]["tyyppi"],
		$conn);
}

?>


<!DOCTYPE html>
<html>
	<head>
		<title>Tehtävä 49</title>
		<meta charset= "utf-8">
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
		<h1>Hakuehdot voit syöttää alla</h1>
		
			<form action="tehtava49.php"  method="get">
				Nimi</td><td><input type="text" name="nimi"><br>
				Osoite</td><td><input type="text" name="osoite"><br>
				Postinumero</td><td><input type="text" name="postinro"><br>
				AsiakasTyyppi<br>
				<?php if (!isset($custTypes["virhe"])) : ?>
				<select name= "tyyppi">
					<option value = "">Vlitse AsiakasTyyppi</option>
				<?php foreach($custTypes as $type) : ?>
					<option value="<?= $type["avain"] ?>"><?= $type["selite"] ?></option>
					<?php endforeach; ?>
					</select><br>
				<?php endif; ?>
				
				<input type="submit" name="hae" value="Hae"><br>
			</form>
			<a href="tehtava49.php?add_intent=true">Lisää asiakas</a><br>
			<?php if (isset($lisays) && $lisays) : ?>
		<span class="success">Asiakkaan luominen onnistui!</span>
	<?php elseif (isset($lisays)) : ?>
		<span class="error">Asiakkaan luomisessa tapahtui virhe!</span>
	<?php endif; ?>
		<?php if ((isset($_GET["submit"]) && !isset($tulokset["virhe"])) ||	(isset($_GET["delete"]) && !isset($tulokset["virhe"])) ||	(isset($_GET["lisaa"]) && !isset($tulokset["virhe"]))) : ?>
		<?php if(isset($poisto) && $poisto) : ?>
			<span class="success">Asiakkaan poistaminen onnistui!</span>
		<?php elseif (isset($_GET["delete"])) : ?>
			<span class="error">Poistettavaa asiakasta ei löytynyt</span>
		<?php endif; ?>
		<table>
			<tr>
				<th>Nimi</th>
				<th>Osoite</th>
				<th>Postinumero</th>
				<th>Paikkakunta</th>
				<th></th>
			</tr>
			<?php foreach($tulos as $res) : ?>
				<tr>
					<td><?= $res["nimi"] ?></td>
					<td><?= $res["osoite"] ?></td>
					<td><?= $res["postinro"] ?></td>
					<td><?= $res["postitmp"] ?></td>
					<td><a href="53.php?del=<?= $res["avain"] ?>">Poista</a></td>
				</tr>
			<?php endforeach; ?>
		</table>
			<?php elseif (isset($result["virhe"])) : ?>
		<span class="error"><?= $result["virhe"] ?></span>
	<?php elseif (isset($_GET["add_intent"])) : ?>
		<h2>Uuden asiakkaan luominen</h2>
		<form action="tehtava49.php" method="get">
			Nimi: <input type="text" name="nimi"><br>
			Osoite: <input type="text" name="osoite"><br>
			Postinumero: <input type="text" name="postinro"><br>
			Postitoimipaikka: <input type="text" name="postitmp"><br>
			<?php if (!isset($custTypes["virhe"])) : ?>
				<select name="tyyppi">
					<?php foreach($types as $type) : ?>
						<option value="<?= $type["avain"] ?>"><?= $type["selite"] ?></option>
					<?php endforeach; ?>
				</select>
			<?php endif; ?>
			<input type="submit" name="add" value="OK">
			<input type="submit" name="cancel" value="Peruuta">
		</form>
	<?php endif; ?>
		
		
		
	</body>
</html>