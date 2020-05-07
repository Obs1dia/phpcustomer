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