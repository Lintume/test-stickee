<?php

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DomCrawler\Crawler;

class ScrapeHelper
{
    /**
     * @param string $url
     * @return Crawler
     * @throws GuzzleException
     */
    public static function fetchDocument(string $url): Crawler
    {
        $client = new Client();

        $response = $client->get($url);

        return new Crawler($response->getBody()->getContents(), $url);
    }

    /**
     * @param Crawler $node
     * @return string
     */
    public static function getTitle(Crawler $node): string
    {
        return $node->filter('.product-name')->text() . ' ' . self::getCapacityString($node);
    }

    /**
     * @param Crawler $node
     * @return string
     */
    public static function getPrice(Crawler $node): string
    {
        return ltrim($node->filter('.block.text-center.text-lg')->text(), '£');
    }

    /**
     * @param Crawler $node
     * @return string
     */
    public static function getImageUrl(Crawler $node): string
    {
        return $node->filter('img')->image()->getUri();
    }

    /**
     * @param Crawler $node
     * @return string
     */
    public static function getCapacityMB(Crawler $node): string
    {
        $string = $node->filter('.product-capacity')->text();
        $number = (int)$string;
        if (str_contains($string, 'GB')) {
            $number *= 1000;
        }
        return (int)$number;
    }

    /**
     * @param Crawler $node
     * @return string
     */
    public static function getColour(Crawler $node): string
    {
        return $node->filter('.border.border-black.rounded-full.block')->attr('data-colour');
    }

    /**
     * @param Crawler $node
     * @return string
     */
    public static function getAvailabilityText(Crawler $node): string
    {
        return (string)self::getIsAvailable($node) ? 'In Stock' : 'Out Of Stock';
    }

    /**
     * @param Crawler $node
     * @return string
     */
    public static function getIsAvailable(Crawler $node): string
    {
        $isAvailableString = $node->filter('.my-4.text-sm.block.text-center')->first()->text();

        return (bool)stripos($isAvailableString, 'In Stock');
    }

    /**
     * @param Crawler $node
     * @return string
     */
    public static function getShippingText(Crawler $node): string
    {
        return self::getUnpreparedShippingString($node);
    }

    /**
     * @param Crawler $node
     * @return string
     * @throws \Exception
     */
    public static function getShippingDate(Crawler $node): string
    {
        $string = self::getUnpreparedShippingString($node);

        if (!$string) {

            return '';
        }

        if (preg_match("/\d{4}-\d{2}-\d{2}/", $string, $matches) //1970-03-02
            || preg_match("/\w+day\s\d{1,2}(st|th|rd)\s\w+ \d{4}/", $string, $matches) //Sunday 28th July 2021
            || preg_match("/(\d{1,2}) (\w+) (\d{4})/", $string, $matches) //25 Apr 2021
            || preg_match("/tomorrow/", $string, $matches)) { //tomorrow


            return self::formatDate("{$matches[0]}");

        }

        return '';
    }

    /**
     * @param Crawler $node
     * @return string
     */
    public static function getCapacityString(Crawler $node): string
    {
        return $node->filter('.product-capacity')->text();
    }

    /**
     * @param Crawler $node
     * @return string
     */
    public static function getUnpreparedShippingString(Crawler $node): string
    {
        return $node->filter('.my-4.text-sm.block.text-center')->eq(1)->text('');
    }

    /**
     * @param $date
     * @return string
     */
    public static function formatDate($date): string
    {
        return date_format(date_create($date), 'Y-m-d');
    }
}
