<?php

require_once('repoArticle.php');
require_once('escapeMarkdown.php');

if(isset($_GET["limit"])){
    $limit = $_GET["limit"];
}
else {
    $limit = 5;
}

$DOM = file_get_contents('../listaArticoli.html');

$repoArticle = new RepoArticle();
$articles = $repoArticle->getArticles();
$repoArticle->disconnect();

$content = '<section>
                <h1>Sei un musicista interessato alla liuteria?</h1>
                <p>Sei nel posto giusto. Qui puoi trovare articoli che troverai sicuramente interessanti.</p>
            </section>
            <div class="flex-container">';

$tot = count($articles);
if(empty($articles)){
    $content .= "<p>Nessun articolo presente.</p>";
}
else {
    for($i=0; $i < $limit && $i < $tot; ++$i)
    {
        $article = $articles[$i];
        $article->title = MarkdownConverter::renderOnlyLanguage($article->title);
        $article->summary = MarkdownConverter::render($article->summary);
        $content .= "<div class='flex-container'>
                        <article class='column'>
                            <h2>{$article->title}</h2>
                            <p>{$article->summary}</p>
                            <div class='btn-container'>
                                <a href='articleDetails.php?article_id={$article->id}' class='button'>Leggi!</a>
                            </div>
                        </article>
                    </div>";
    }
}

$content .= "</div>";
if($tot > $limit){
    $limit += 5;
    $content .= "<div><a href=\"articleList.php?limit={$limit}\">Carica altro</a></div>";
}

$DOM = str_replace('<cs_main_content/>', $content, $DOM);

echo $DOM;

?>
