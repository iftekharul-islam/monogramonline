<?php

namespace Ups\Entity;

use DOMDocument;
use DOMElement;
use Ups\NodeInterface;

class CN22Form implements NodeInterface
{
    /**
     * @var string
     */
    private $LabelSize;
	
    /**
     * @var string
     */
    private $PrintsPerPage;

    /**
     * @var string
     */
    private $LabelPrintType;

    /**
     * @var string
     */
    private $CN22Type;

    /**
     * @var CN22Content
     */
    private $CN22Content;


    /**
     * @param null|object $attributes
     */
    public function __construct($attributes = null)
    {
        if (null !== $attributes) {
            if (isset($attributes->LabelSize)) {
                $this->setLabelSize($attributes->LabelSize);
            }
            if (isset($attributes->PrintsPerPage)) {
                $this->setPrintsPerPage($attributes->PrintsPerPage);
            }
            if (isset($attributes->LabelPrintType)) {
                $this->setLabelPrintType(new DateTime($attributes->LabelPrintType));
            }
            if (isset($attributes->CN22Type)) {
                $this->setCN22Type($attributes->CN22Type);
            }
        }
    }

    /**
     * @param $LabelSize string
     *
     * @return $this
     */
    public function setLabelSize($LabelSize)
    {
        $this->LabelSize = $LabelSize;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabelSize()
    {
        return $this->LabelSize;
    }

    /**
     * @param $PrintsPerPage string
     *
     * @return $this
     */
    public function setPrintsPerPage($PrintsPerPage)
    {
        $this->PrintsPerPage = $PrintsPerPage;

        return $this;
    }

    /**
     * @return PrintsPerPage
     */
    public function getPrintsPerPage()
    {
        return $this->PrintsPerPage;
    }

    /**
     * @param $LabelPrintType string
     *
     * @return $this
     */
    public function setLabelPrintType($LabelPrintType)
    {
        $this->LabelPrintType = $LabelPrintType;

        return $this;
    }

    /**
     * @return LabelPrintType
     */
    public function getLabelPrintType()
    {
        return $this->LabelPrintType;
    }

    /**
     * @param $CN22Type string
     *
     * @return $this
     */
    public function setCN22Type($CN22Type)
    {
        $this->CN22Type = $CN22Type;

        return $this;
    }

    /**
     * @return CN22Type
     */
    public function getCN22Type()
    {
        return $this->CN22Type;
    }

    /**
     * @return CN22Content
     */
    public function getCN22Content()
    {
        return $this->CN22Content;
    }

    /**
     * @param $CN22Content CN22Content
     *
     * @return $this
     */
    public function setCN22Content(CN22Content $CN22Content)
    {
        $this->CN22Content = $CN22Content;

        return $this;
    }

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

        $node = $document->createElement('CN22Form');

        if ($this->getLabelSize()) {
            $node->appendChild($document->createElement('LabelSize', $this->getLabelSize()));
        }
        if ($this->getPrintsPerPage() !== null) {
            $node->appendChild($document->createElement('PrintsPerPage', $this->getPrintsPerPage()));
        }
        if ($this->getLabelPrintType() !== null) {
            $node->appendChild($document->createElement('LabelPrintType', $this->getLabelPrintType()));
        }
        if ($this->getCN22Type() !== null) {
            $node->appendChild($document->createElement('CN22Type', $this->getCN22Type()));
        }
       	if ($this->getCN22Content() !== null) {
	    $node->appendChild($this->getCN22Content()->toNode($document));
            //$node->appendChild($document->createElement('CN22Content', $this->->toNode($document)));
        }       
        return $node;
    }
}
