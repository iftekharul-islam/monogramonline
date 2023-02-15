<?php namespace Monogram;


use Yangqi\Htmldom\Htmldom;

class DOMReader
{
	private $content = '';
	private $reader = null;

	public function __construct ($content = '')
	{
		$this->reader = new Htmldom();
		if ( !empty( $content ) ) {
			$this->setContent($content);
			$this->reader->load($content);
		}
	}

	public function setContent ($content)
	{
		$this->content = $content;
	}

	private function getContent ()
	{
		return $this->content;
	}

	public function read ()
	{
		if ( empty( $this->getContent() ) ) {
			throw new \Exception("DOMReader Exception: No content found.");
		}


	}

	public function readCrawledData ()
	{
		return $this->reader->find("body", 0)
							->innerText();
	}
}