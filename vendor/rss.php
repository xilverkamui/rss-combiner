<?php 
require_once ('../vendor/autoload.php');

$debug = false;
$name = "";
if (isset($_GET['name']))   $output = $_GET['name'];

$config = json_decode(file_get_contents('config.json'),false);
if ($config === null) die ('Ada kesalahan pada config');
//($name != "") ? $feed = $config -> feeds -> $name; : $feed = $config -> feeds[0];
$feed = $config -> feeds -> infosurabayaterkini;

if ($feed -> debug)  $debug = $feed -> debug ;
if (isset($_GET['debug']))   $debug = $_GET['debug'];


$rss = generateRss($feed);
if (!$debug) {
    header('Content-type: application/xml');
    header('Cache-Control: public, max-age=0');
    //header($config -> cache_control);
    echo $rss;
}
else {
    var_dump($feed);
    echo PHP_EOL . PHP_EOL . PHP_EOL;
}


// Help from: http://www.webmaster-source.com/2007/08/06/merging-rss-feeds-with-simplepie/ and https://digitalfreelancing.eu/?p=87313
function generateRss ($feedConfig) {
    $debug = $feedConfig -> debug;
    $result = "";
    date_default_timezone_set('Asia/Jakarta');
    $feedLink = $feedConfig -> feed_link;
    if (is_null($feedLink ))
        $feedLink = htmlspecialchars( 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8' );
    $feedHome = $feedConfig -> feed_home;
    if (is_null($feedHome))
        $feedHome = htmlspecialchars( 'https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']), ENT_QUOTES, 'UTF-8' );
    $feedTitle = $feedConfig -> feed_title;
    $feedDesc = $feedConfig -> feed_desc;
    $feeds = $feedConfig -> feeds;
    //$allowedTags = $config -> allowed_tags;
    //$replacedStrings = $config -> replaced_strings;
    //$replacementStrings = $config -> replacement_strings;
    $linkInContent = ($feedConfig -> link_in_content === 1) ? 1 : 0;
    
    if ($debug) {
        echo "DEBUG MODE IS ENABLED" . PHP_EOL;
        echo "FeedLink: " . $feedLink . PHP_EOL;
        echo "FeedHome: " . $feedHome . PHP_EOL;
        echo "FeedTitle: " . $feedTitle . PHP_EOL;
        echo "FeedDesc: " . $feedDesc . PHP_EOL;
        echo "Feeds: " . PHP_EOL;
        print_r ($feeds);
        //echo "AllowedTags: " . $allowedTags . PHP_EOL;
        //echo "ReplacedStrings: " . PHP_EOL;
        //print_r ($replacedStrings);
        //echo "ReplacementStrings: " . PHP_EOL;
        //print_r ($replacementStrings);
        echo "LinkInContent: " . $linkInContent . PHP_EOL;
        if (!$linkInContent) echo "LinkInContent salah" . PHP_EOL;
        print_r($feedConfig);
    }
    
    //header
    $result = $result . 
'<?xml version="1.0" encoding="UTF-8"?> 
<rss version="2.0" 
    xmlns:atom="http://www.w3.org/2005/Atom"
    xmlns:content="http://purl.org/rss/1.0/modules/content/" 
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:creativeCommons="http://backend.userland.com/creativeCommonsRssModule" 
    xmlns:media="http://search.yahoo.com/mrss/"
>

<channel>
<title>' . $feedTitle . '</title>
<atom:link href="' . $feedLink . '" rel="self" type="application/rss+xml" />
<link>' . $feedHome . '</link>
<description>' . $feedDesc . '</description>
<language>id-ID</language>
<copyright>Copyright ' . date("Y") . '</copyright>
<creativeCommons:license>http://creativecommons.org/licenses/by-nc-sa/3.0/</creativeCommons:license>

';
//<lastBuildDate>' . date("r") . '</lastBuildDate>
foreach ($feeds as $singleFeed) {
    if (!$singleFeed -> active) continue;   //skip inactive feeds
    $allowedTags = $config -> allowed_tags . $singleFeed -> allowed_tags;
    $replacedStrings = empty($singleFeed -> replaced_strings) ? $config -> replaced_strings : $singleFeed -> replaced_strings;
    $replacementStrings = empty($singleFeed -> replacement_strings) ? $config -> replacement_strings : $singleFeed -> replacement_strings;
    $noTitle = $singleFeed -> noTitle;
    $noDescription = $singleFeed -> noDescription;
    $noContent = $singleFeed -> noContent;
    $item_limit  = 50;
    
    $feed = new SimplePie(); // Create a new instance of SimplePie
    $feed->set_feed_url($singleFeed -> url);
    //$feed->enable_cache(false);
    $feed->set_cache_location('../cache/rss');
    $feed->set_cache_duration (3600); // Set the cache time in ms
    $feed->get_raw_data();
    $success = $feed->init(); // Initialize SimplePie
    //$feed->handle_content_type(); // Take care of the character encoding

    if ($debug) {
        print_r ($singleFeed);
        echo "URL: " . $singleFeed -> url . PHP_EOL;
        echo "AllowedTags: " . $allowedTags . PHP_EOL;
        echo "ReplacedStrings: " . PHP_EOL;
        print_r ($replacedStrings);
        echo "ReplacementStrings: " . PHP_EOL;
        print_r ($replacementStrings);
        echo "Success: " . $success . PHP_EOL;
        continue;
    }
    
    if ($success) {
    	$itemlimit=0;
    	foreach($feed->get_items() as $item) {
    		if ($itemlimit == $item_limit) { break; }     //maximum 100 items
    		$title = htmlspecialchars(strip_tags(str_replace($replacedStrings,$replacementStrings,strip_tags($item->get_title()))));
    		$title = substr($title,0,100);
    		if ($noTitle)   $title = "";
    		$pubDate = $item->get_date('D, d M Y H:i:s O');
    		$creator = $item->get_author()->get_name();
    		$media = htmlspecialchars($item->get_item_tags('http://search.yahoo.com/mrss/','content')[0]['attribs']['']['url']);
    		if (!$media) $media='';
    		$link = ($linkInContent) ? $media : $item->get_permalink();
    		$content = str_replace($replacedStrings,$replacementStrings,strip_tags($item->get_description(),$allowedTags));
    		$description = htmlspecialchars($content); // . ' ' . ($linkInContent == 1) ? $media : '';
    		if ($noDescription) $description = "";
    		$content2 = $content; // . '<br>' . ($linkInContent == 0) ? 'masuk' : '';
    		if ($noContent) $content2 = "";

    	    $result = $result . 
    	    '<item>
                <title>' . $title . ' </title>
                <link>' . $link . '</link>
                <guid>' . $link . '</guid>
                <pubDate>' . $pubDate . '</pubDate>
                <dc:creator>' . $creator . '</dc:creator>
                <description>
                    ' . $description . '
                </description>
                <content:encoded>
                    <![CDATA[' . $content2 . ' ]]>
                </content:encoded>
                <media:content medium="image" url="' . $media . '" />
            </item>
            ';
    
    		$itemlimit = $itemlimit + 1;
    	}
    }

}
    $result = $result . 
'</channel>
</rss>';
    return $result;
}

?>