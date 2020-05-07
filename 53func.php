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

function editCustomer($conn)
{
	$query = "UPDATE asiakas 
	SET nimi = $nimi,
	osoite = $osoite, 
	postinro = $postinro,
	postitmp = $postitmp,
	tyyppi = $tyyppi
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
	$name = saveGet("nimi");
	$address = saveGet("osoite");
	$postal = saveGet("postinro");
	$type = saveGet("tyyppi");
	$postitmp = saveGet("postitmp");

	$muokkaus = editCustomer($name, $address, $postal, $type, $postitmp, $conn);

	$result = search(
		$_SESSION["haku"]["nimi"], 
		$_SESSION["haku"]["osoite"], 
		$_SESSION["haku"]["postinro"], 
		$_SESSION["haku"]["tyyppi"],
		$conn
	);
}
