<?php

/*
 * This file is part of the Content2Pdf package.
 *
 * (c) Mark Fluehmann mark.fluehmann@gmail.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace timatanga\Content2Pdf;

use timatanga\Content2Pdf\Renderer;
use timatanga\Content2Pdf\Exceptions\ContentException;

class Html2Pdf extends Renderer
{
	
    /**
     * Create instance 
     *
     * @param string $template 	template sets layout settings, default: none
     * @param array  $config 	custom configs
     */
   	public function __construct( ?string $template, array $config = []  )
   	{
   		parent::__construct($template, $config);
   	}


    /**
     * Include HTML as Content
     *
     * @param  array   $attributes
     * @return $this           
     */
    public function include( array $attributes = [] )
	{
		if (! isset($attributes['body']) || empty($attributes['body']) )
			throw new ContentException('Invalid content, an HTML body is required');

		$this->includeMeta($attributes);

		$this->includeHeader($attributes);

		$this->includeFooter($attributes);

		$this->includeStylesheet($attributes);

		$this->includeHtml($attributes);

		return $this;
	}


	/**
	 * Finalise the document and return the document as a string
	 *
	 * @return string	 
	 */
	public function stream()
	{
		return parent::stream();
	}


	/**
	 * Finalise the document and send the file inline to the browser
	 *
	 * @param string  $filename 	$filename is used when one selects the “Save as”
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function inline($filename = 'document.pdf')
	{
		return parent::inline($filename);
	}


	/**
	 * Finalise the document and send to the browser and force a file download
	 *
	 * @param {string} $filename 	the name given by $filename.
	 * @return void
	 */
	public function download($filename = 'document.pdf')
	{
		return parent::download($filename);
	}


	/**
	 * Finalise the document and save to a local file with the name given by $filename
	 *
	 * @param {string} $filename
	 * @return static
	 */
	public function save($filename = 'document.pdf')
	{
		return parent::save($filename);
	}


    /**
     * Include Meta
     *
     * @param  array   $attributes
     * @return $this           
     */
	private function includeMeta( array $attributes )
	{
		if (! isset($attributes['meta']) || empty($attributes['meta']) )
			return;

		// do some meta formating

		$this->setMeta( $attributes['meta'] );
	}


    /**
     * Include Header
     *
     * @param  array   $attributes
     * @return $this           
     */
	private function includeHeader( array $attributes )
	{
		if (! isset($attributes['header']) || empty($attributes['header']) )
			return;

		// do some header formating

		$this->setHeader( $attributes['header'] );

	}


    /**
     * Include Footer
     *
     * @param  array   $attributes
     * @return $this           
     */
	private function includeFooter( array $attributes )
	{
		if (! isset($attributes['footer']) || empty($attributes['footer']) )
			return;

		// do some footer formating

		$this->setFooter( $attributes['footer'] );
	}


    /**
     * Include Stylesheet
     *
     * @param  array   $attributes
     * @return $this           
     */
	private function includeStylesheet( array $attributes )
	{
		if (! isset($attributes['stylesheet']) || empty($attributes['stylesheet']) )
			return;

		// do some stylesheet formating

		$this->setStylesheet( $attributes['stylesheet'] );
	}


    /**
     * Include Body
     *
     * @param  array   $attributes
     * @return $this           
     */
	private function includeHtml( array $attributes )
	{
		if (! isset($attributes['body']) || empty($attributes['body']) )
			throw new ContentException('Invalid content, HTML body is required');

		// do some body formating

		$this->insertHtml( $attributes['body'] );
	}


}