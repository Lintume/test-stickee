<?php

namespace App;

use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DomCrawler\Crawler;

require 'vendor/autoload.php';

class Scrape
{
    private array $products = [];

    private string $url = 'https://www.magpiehq.com/developer-challenge/smartphones';

    private string $outputFile = 'output.json';

    /**
     * run crawler, point of entry
     */
    public function run(): void
    {
        $pages = $this->getPagesForCrawling();
        $this->crawling($pages);

        $this->products = array_unique($this->products);

        file_put_contents($this->outputFile, json_encode($this->products));
    }

    /**
     * @return array
     * @throws GuzzleException
     */
    public function getPagesForCrawling(): array
    {
        $document = ScrapeHelper::fetchDocument($this->url);

        $pages = $document->filter('#pages a');
        return $pages->each(function (Crawler $node, $i) {
            return $node->text();
        });
    }

    /**
     * @param $pages
     * @throws GuzzleException
     */
    public function crawling($pages): void
    {
        foreach ($pages as $page) {
            $document = ScrapeHelper::fetchDocument($this->url . '/?page=' . $page);
            $productContainers = $document->filter('.product');

            $productContainers->each(function (Crawler $node, $i) {
                $this->createProduct($node);
            });
        }
    }

    /**
     * @param Crawler $node
     */
    public function createProduct(Crawler $node): void
    {
        $colourNodes = $node->filter('.border.border-black.rounded-full.block');
        $colours = $colourNodes->each(function (Crawler $node, $i) {
            return $node->attr('data-colour');
        });
        foreach ($colours as $colour) {
            $product = new Product();
            $product->setAllPropsFrom($node);
            $product->colour = $colour;
            $this->products[] = $product;
        }
    }
}

$scrape = new Scrape();
$scrape->run();
