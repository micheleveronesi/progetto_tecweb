<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . "escapeMarkdown.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . "repoArticle.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . "repoImage.php";
session_start();

if(!(isset($_SESSION['admin']) && $_SESSION['admin'] === true)) {
	header('Location: adminLogin.php');
}

if(isset($_GET["limit"])){
    $limit = $_GET["limit"];
}
else {
    $limit = 5;
}

$html = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR .".." . DIRECTORY_SEPARATOR . "admin" . DIRECTORY_SEPARATOR . "admin-lista-articoli.html");

$repoArticle = new RepoArticle();
if($repoArticle->getConnectionLastError() !== '')
{
	header('Location: ..' . DIRECTORY_SEPARATOR . '500.html');
}

$articles = $repoArticle->getArticles();
$repoArticle->disconnect();
$tot = count($articles);

$content = '<div class="flex-container">';

if(empty($articles)){
	$content .= "<p>Nessun articolo presente.</p>";
}
else {
	for($i=0; $i < $tot && $i < $limit; ++$i)
	{
		$last = false;
        if(($i === $limit - 1) || ($i === $tot - 1)){
            $last = true;
        }
		$article = $articles[$i];
		$article->title = MarkdownConverter::renderOnlyLanguage($article->title);
		$article->summary = MarkdownConverter::render($article->summary);
		$content .= "<article class=\"flex-article\">".
							($last === true ? "<h2 id=\"article-anchor\"" : "<h2") . " tabindex=\"0\">" . "{$article->title}</h2>".
						"<section tabindex=\"0\">{$article->summary}</section>".
							"<div class=\"btn-container three-btn\">".
								"<a href=\"articleDetailsAdmin.php?article_id={$article->id}\" class=\"button\" tabindex=\"0\">Leggi!</a>".
								"<a href=\"insertForm.php?article_id={$article->id}\" class=\"button\" tabindex=\"0\">Modifica</a>".
								"<a href=\"deleteArticle.php?article_id={$article->id}\" class=\"button\" tabindex=\"0\">Elimina</a>".
							"</div>".
						"</article>";
	}
}

$content .= "</div>";

if($tot > $limit){
	$limit += 5;
    $content .= "<div class=\"load-more\"><a href=\"editArticles.php?limit={$limit}#article-anchor\">Carica altro</a></div>";
}

$html = str_replace("<cs_main_content/>", $content, $html);
echo $html;

?>
