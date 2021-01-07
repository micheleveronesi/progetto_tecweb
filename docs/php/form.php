<?php
require_once("backend/escapeMarkdown.php");
require_once("backend/article/repoArticle.php");
require_once("backend/image/repoImage.php");
header('Content-type: text/html; charset=utf-8');
session_start();
if(!(isset($_SESSION['admin']) && $_SESSION['admin'] === true)) {
	header('Location: adminLogin.php');
}


$html = file_get_contents("../admin/admin-nuovo-articolo.html");

$errorMessageTitolo = "Il titolo dell'articolo è: obbligatorio, al massimo 30 caratteri e va scritto senza markdown";
$errorMessageContenuto = "Il corpo dell'articolo deve essere lungo almeno 30 caratteri e scritto secondo le regole del markdown";
$errorMessageSommario = "Il sommario dell'articolo è: obbligatorio, al massimo 200 caratteri e scritto secondo le regole del markdown";
$errorMessageFile = "Il file va inserito obbligatoriamente e deve essere un immagine inferiore al megabyte";
$errorMessageFileDuplicate = "Un file con questo nome è gia stato inserito nella piattaforma";
$errorMessageAlt = "Il testo alternativo non può superare i 70 caratteri o contenere markup";

$repoImage = new RepoImage();
$repoArticle = new RepoArticle();

/**
 * @field: valore del campo
 * @validity: booleano che indica la validità del contenuto del campo
 * @error_substitution: valore da cercare in html da sostituire con il messaggio di errore
 * @error_message: messaggio di errore da visualizzare
 * @value_content: contenuto da inserire nel campo
 */
function handleField($validity, $error_substitution, $error_message, $value_substitution, $field){
	$html = substituteError($validity, $error_substitution, errorElement($error_message), $html);
	$html = str_replace($value_substitution, $field, $html);
}

function validateTextField($field, $minlen, $maxlen, $hasMarkdown, $isNotRequired){
	$field  = isset($field) ? $field : "";
	return ($hasMarkdown || validateNoMarkdown($field))
			&& validateLength($field, NULL, 30)
			&& ($isNotRequired || (validateRequired($field));
}


function validaContenuto($contenuto) {
	handleField(validateTextField($contenuto, 30, NULL, true, false),
				"%error-contenuto%", errorElement($errorMessageContenuto), "%value-contenuto%", $contenuto);
}

function validaTitolo($titolo) {
	// TODO: permettere di inserire markdown lingua
	handleField(validateTextField($titolo, NULL, 30, false, false),
				"%error-titolo%", errorElement($errorMessageTitolo), "%value-titolo%", $titolo);
}

function validaSommario($sommario) {
	handleField(validateTextField($sommario, NULL, 200, true, false),
				"%error-contenuto%", errorElement($errorMessageSommario), "%value-contenuto%", $sommario);
}

function validaAltImmagine($altImmagine) {
	handleField(validateTextField($altImmagine, NULL, 70, false, true),
				"%error-alt%", errorElement($errorMessageAlt), "%value-alt%", $altImmagine);
}

/*
if(isset($_POST["submit"])){
	// check titolo aritcolo (niente markdown, lunghezza massima e required)
	if(isset($_POST["titolo-articolo"])){
		$titolo = $_POST["titolo-articolo"];
		$isDuplicate = $repoArticle->checkDouble($titolo);
		$validTitolo = validateNoMarkdown($titolo)
				&& validateLength($titolo, NULL, 30)
				&& validateRequired($titolo)
				&& !$isDuplicate;
	} else {
		$validTitolo = false;
		$titolo = "";
	}
	handleField($validTitolo, "%error-titolo%", errorElement($errorMessageTitolo), "%value-titolo%", $titolo);
	
	// check contenuto articolo (lunghezza minima e required)
	
	$html = substituteError($validContenuto, "%error-contenuto%", errorElement($errorMessageContenuto), $html);
	$html = str_replace("%value-contenuto%", $contenuto, $html);
	
	// check summary articolo (lunghezza massima e required)
	if(isset($_POST["sommario-articolo"])){
		$sommario = $_POST["sommario-articolo"];
		print_r(utf8_strlen($sommario));
		$validSommario = validateLength($sommario, NULL, 200)
					&& validateRequired($sommario);
	} else {
		$sommario = "";
		$validSommario = false;
	}
	$html = substituteError($validSommario, "%error-sommario%", errorElement($errorMessageSommario), $html);
	$html = str_replace("%value-sommario%", $sommario, $html);
	*/
	// check file immagine caricato (dimensione e tipo file)
	if(isset($_FILES["file-immagine"])){
		$file = $_FILES["file-immagine"];
		$isDuplicate = $repoImage->checkDouble($file["name"]);
		$validFile = $file["size"] <= 1000000
				&& $file["error"] === 0
				&& substr_compare($file["type"], "image/", 0, strlen("image/")) === 0
				&& !$isDuplicate;

	} else {
		$validFile = false;
		$isDuplicate = false;
	}
	$html = substituteError($validFile, "%error-file%",errorElement($isDuplicate ? $errorMessageFileDuplicate : $errorMessageFile) , $html);
	/*
	// check alt immagine (lunghezza massima e niente markdown)
	if(isset($_POST["alt-immagine"])){
		$alt = $_POST["alt-immagine"];
		$validAlt = validateNoMarkdown($alt)
				&& validateLength($alt, NULL, 70);
	} else {
		$validAlt = true;
		$alt = "";
	}
	$html = substituteError($validAlt, "%error-alt%", $errorMessageAlt, $html);
	$html = str_replace("%value-alt%", $alt, $html);
	*/
	if($validTitolo && $validContenuto && $validFile && $validAlt && $validSommario){
		$repoImage->addImage($file, $alt);
		$insertedImage = $repoImage->findImageByName($file["name"]);
		$resultInsArticle = $repoArticle->addArticle($titolo, $contenuto, $sommario, $insertedImage->id);
		echo "Articolo inserito";
	}else{
		echo $html;
	}
	
} else {
	$substitutions = array("%error-alt%", "%error-file%", "%error-contenuto%", "%error-titolo%","%error-sommario%", 
	                       "%value-alt%", "%value-contenuto%", "%value-titolo%", "%value-sommario%");
	echo str_replace($substitutions, "",$html);
}

$repoImage->disconnect();
$repoArticle->disconnect();

function validateNoMarkdown($input){
	$valid = true;
	foreach (array_keys(MarkdownConverter::$standardRules) as $regex){
		 $valid = $valid && !preg_match($regex, $input);
	}
	return $valid;
}

function validateLength($input, $min, $max){
	return (($min === NULL) || ($min <= strlen($input)))
	&& (($max === NULL) || ($max >= strlen($input)));
}

function validateRequired($input){
	return isset($input) && strlen($input) > 0;
}

function substituteError($valid, $pattern, $error, $context){
	if($valid===true){
		return str_replace($pattern, "", $context);
	} else {
		return str_replace($pattern, $error, $context);
	}
}

function errorElement($message){
	return '<strong class="error"> - ' . $message . '</strong>';
}
