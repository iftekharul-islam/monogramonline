<?php

namespace Ups\Entity;

use DOMDocument;
use DOMElement;
use Ups\NodeInterface;

class CN22Content implements NodeInterface
{
    /**
     * @var string
     */
    private $ContentQuantity;
	
    /**
     * @var string
     */
    private $CN22ContentDescription;

    /**
     * @var string
     */
    private $CN22ContentTotalValue;

    /**
     * @var string
     */
    private $CN22ContentCurrencyCode;

    /**
     * @var string
     */
    private $CN22ContentCountryOfOrigin;


    /**
     * @var CN22ContentWeight
     */
    private $CN22ContentWeight;


    /**
     * @param null|object $attributes
     */
    public function __construct($attributes = null)
    {
        if (null !== $attributes) {
            if (isset($attributes->ContentQuantity)) {
                $this->setContentQuantity($attributes->ContentQuantity);
            }
            if (isset($attributes->CN22ContentDescription)) {
                $this->setCN22ContentDescription($attributes->CN22ContentDescription);
            }
            if (isset($attributes->CN22ContentTotalValue)) {
                $this->setCN22ContentTotalValue(new DateTime($attributes->CN22ContentTotalValue));
            }
            if (isset($attributes->CN22ContentCurrencyCode)) {
                $this->setCN22ContentCurrencyCode($attributes->CN22ContentCurrencyCode);
            }
	    if (isset($attributes->CN22ContentCountryOfOrigin)) {
                $this->setCN22ContentCountryOfOrigin($attributes->CN22ContentCountryOfOrigin);
            }
        }
    }

    /**
     * @param $ContentQuantity string
     *
     * @return $this
     */
    public function setContentQuantity($ContentQuantity)
    {
        $this->ContentQuantity = $ContentQuantity;

        return $this;
    }

    /**
     * @return string
     */
    public function getContentQuantity()
    {
        return $this->ContentQuantity;
    }

    /**
     * @param $CN22ContentDescription string
     *
     * @return $this
     */
    public function setCN22ContentDescription($CN22ContentDescription)
    {
        $this->CN22ContentDescription = $CN22ContentDescription;

        return $this;
    }

    /**
     * @return CN22ContentDescription
     */
    public function getCN22ContentDescription()
    {
        return $this->CN22ContentDescription;
    }

    /**
     * @param $CN22ContentTotalValue string
     *
     * @return $this
     */
    public function setCN22ContentTotalValue($CN22ContentTotalValue)
    {
        $this->CN22ContentTotalValue = $CN22ContentTotalValue;

        return $this;
    }

    /**
     * @return CN22ContentTotalValue
     */
    public function getCN22ContentTotalValue()
    {
        return $this->CN22ContentTotalValue;
    }

    /**
     * @param $CN22ContentCurrencyCode string
     *
     * @return $this
     */
    public function setCN22ContentCurrencyCode($CN22ContentCurrencyCode)
    {
        $this->CN22ContentCurrencyCode = $CN22ContentCurrencyCode;

        return $this;
    }

    /**
     * @return CN22ContentCurrencyCode
     */
    public function getCN22ContentCurrencyCode()
    {
        return $this->CN22ContentCurrencyCode;
    }

    /**
     * @param $CN22ContentCountryOfOrigin string
     *
     * @return $this
     */
    public function setCN22ContentCountryOfOrigin($CN22ContentCountryOfOrigin)
    {
        $this->CN22ContentCountryOfOrigin = $CN22ContentCountryOfOrigin;

        return $this;
    }

    /**
     * @return CN22ContentCountryOfOrigin
     */
    public function getCN22ContentCountryOfOrigin()
    {
        return $this->CN22ContentCountryOfOrigin;
    }

    /**
     * @param $CN22ContentWeight CN22ContentWeight
     *
     * @return $this
     */
    //public function setCN22ContentWeight(CN22ContentWeight $CN22ContentWeight)
    //{
    //    $this->CN22ContentWeight = $CN22ContentWeight;
    //
    //    return $this;
    //}

    /**
     * @return CN22ContentWeight
     */
    //public function getCN22ContentWeight()
    //{
    //    return $this->CN22ContentWeight;
    //}

    /**
     * @param null|DOMDocument $document
     *
     * @return DOMElement
     */
    public function toNode(DOMDocument $document = null)
    {
        if (null === $document) {
            $document = new DOMDocument();
        }

        $node = $document->createElement('CN22Content');

        if ($this->getContentQuantity()) {
            $node->appendChild($document->createElement('ContentQuantity', $this->getContentQuantity()));
        }
        if ($this->getCN22ContentDescription() !== null) {
            $node->appendChild($document->createElement('CN22ContentDescription', $this->getCN22ContentDescription()));
        }
        if ($this->getCN22ContentTotalValue() !== null) {
            $node->appendChild($document->createElement('CN22ContentTotalValue', $this->getCN22ContentTotalValue()));
        }
        if ($this->getCN22ContentCurrencyCode() !== null) {
            $node->appendChild($document->createElement('CN22ContentCurrencyCode', $this->getCN22ContentCurrencyCode()));
        }
        if ($this->getCN22ContentCountryOfOrigin() !== null) {
            $node->appendChild($document->createElement('CN22ContentCountryOfOrigin', $this->getCN22ContentCountryOfOrigin()));
        }
       	//if ($this->CN22ContentWeight() !== null) {
        //    $node->appendChild($document->createElement('CN22ContentWeight', $this->getCN22ContentWeight()->toNode($document)));
        //}
        return $node;
    }
}
