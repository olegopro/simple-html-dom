<?php

use voku\helper\HtmlDomParser;
use Symfony\Component\Dotenv\Dotenv;

require_once './vendor/autoload.php';

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

// You will get the variable both in
// $_ENV['ENVIRONMENT_VAR'] and $_SERVER['ENVIRONMENT_VAR'].
$dsn = $_SERVER['DSN'] ?? '';
$username = $_SERVER['USERNAME'] ?? '';
$password = $_SERVER['PASSWORD'] ?? '';


$db = new PDO($dsn, $username, $password);
$url = 'https://dezkrd23.ru/news/';


if (isset($argv[1])) {
	$action = $argv[1];
} else {
	echo 'No action';
	exit;
}

if ($action == 'catalog') {
	getArticlesLinksFromCatalog($url);
} elseif ($action == 'articles') {
	while (true) {
		$tmp_unique = md5(uniqid() . time());
		$db->query("UPDATE articles SET tmp_unique = '$tmp_unique' WHERE tmp_unique IS NULL LIMIT 10")
		   ->fetch(PDO::FETCH_LAZY);

		$articles = $db->query("SELECT url FROM articles WHERE tmp_unique = '$tmp_unique'")
		               ->fetchAll(PDO::FETCH_ASSOC);

		if (!$articles) {
			echo 'All done!' . PHP_EOL;
			exit;
		}

		foreach ($articles as $article) {
			getArticleData($article['url']);
			echo $article['url'] . PHP_EOL;
		}
	}
}

function curlConnect($url)
{
	$curl = curl_init();

	curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:71.0) Gecko/20100101 Firefox/71.0");
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

	curl_close($curl);

	return curl_exec($curl);
}

function getArticleData($url)
{
	global $db;

	$article = HtmlDomParser::str_get_html(curlConnect($url));
	$h1 = $article->findOneOrFalse('h1')->innerHtml();


	$content = $article->findOneOrFalse('.text')->innerHtml();
	$data = compact('h1', 'content');

	$query = 'UPDATE `articles` SET h1 = :h1, content = :content, data_parse = :data_parse WHERE url = :url';
	$params = [
		':url'        => $url,
		':h1'         => $h1,
		':content'    => $content,
		':data_parse' => (new DateTime)->format('Y-m-d H:i:s')
	];

	$db->prepare($query)->execute($params);

	return $data;
}

function getArticlesLinksFromCatalog($url)
{
	global $db;

	$curl = curl_init();

	curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:71.0) Gecko/20100101 Firefox/71.0");
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

	$html = curl_exec($curl);

	if ($html === false) {
		echo curl_error($curl) . "\n";
	}

	$dom = HtmlDomParser::str_get_html($html);

	foreach ($dom->findMultiOrFalse('a.read-more') as $link_to_article) {
		$query = 'INSERT IGNORE INTO `articles` (`url`) VALUE (:url)';
		$params = [
			':url' => $link_to_article->href
		];

		$db->prepare($query)->execute($params);
		echo $link_to_article->href . PHP_EOL;
	}

	//recursion
	if ($next_link = $dom->findOneOrFalse('a.next.page-numbers')) {
		getArticlesLinksFromCatalog($next_link->getAttribute('href'));
	}
}


