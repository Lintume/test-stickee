<?php

namespace App;

use Symfony\Component\DomCrawler\Crawler;

class Product implements \JsonSerializable
{
    private ?string $title = null;
    private ?float $price = null;
    private ?string $imageUrl = null;
    private ?int $capacityMB = null;
    private ?string $colour = null;
    private ?string $availabilityText = null;
    private ?bool $isAvailable = null;
    private ?string $shippingText = null;
    private ?string $shippingDate = null;

    /**
     * magic setter for all private props
     * @param $name
     * @param $value
     */
    function __set($name, $value): void
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        }
    }

    /**
     * for proper working json_encode
     *
     * @return array|mixed
     */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    /**
     * for proper working array_unique
     *
     * @return string
     */
    public function __toString(): string
    {
        return implode(',', get_object_vars($this));
    }

    /**
     * set all parameters of product from node
     *
     * @param Crawler $node
     */
    public function setAllPropsFrom(Crawler $node): void
    {
        $classProps = array_keys(get_object_vars($this));
        foreach ($classProps as $prop) {
            $nameOfHelper = 'get' . ucfirst($prop);
            if (method_exists(new ScrapeHelper(), $nameOfHelper)) {
                $this->$prop = ScrapeHelper::$nameOfHelper($node);
            }
        }
    }
}
