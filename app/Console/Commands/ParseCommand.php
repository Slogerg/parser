<?php
namespace App\Console\Commands;

use App\Models\Item;
use DOMDocument;
use DOMXPath;
use Sunra\PhpSimple\HtmlDomParser;
use Illuminate\Console\Command;
use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp;
class ParseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:start {url}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //test urls
//        $url = 'https://answear.ua/k/vin/odyag/dzhynsy';
//        $url = 'https://answear.ua/k/vin/vzuttya';
//        $url = 'https://stackoverflow.com/questions/26699705/guzzle-http-client-extract-plain-text-or-html-from-the-response';
        $url = $this->argument('url');

        //connect via cURL using Guzzle
        $client = new GuzzleHttp\Client();
        $response = $client->get($url);

        //get raw html
        $html = (string) $response->getBody();

        //loaded html via Xpath library
        libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        $doc->loadHTML($html);
        $xpath = new DOMXPath($doc);


        //set wanted fields to parse
        $titles = $xpath->evaluate(
            '//div[@class="grid-row"]//div[starts-with(@class, "m-4")]
                         //div[starts-with(@class, "ProductItem__productCard__1c448")]
                         //div[starts-with(@class, "ProductItem__productCardDescription__2OiTW")]
                         //div[@class = "ProductItem__productCardName__RGRRI"]//span');

        $prices = $xpath->evaluate(
            '//div[@class="grid-row"]//div[starts-with(@class, "m-4")]
                         //div[starts-with(@class, "ProductItem__productCard__1c448")]
                         //div[starts-with(@class, "ProductItem__productCardDescription__2OiTW")]
                         //div[contains(@class, "Price__wrapper__JRuWx")]
                        //div[starts-with(@class,"Price__price__3uiSQ")]');

        foreach ($titles as $key => $title) {
            Item::create([
               'title' => $title->textContent,
               'price' => preg_replace('/[^0-9]/', '', $prices[$key]->textContent)
            ]);
        }
    }
}
