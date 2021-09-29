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

use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;
use Mpdf\MpdfException;
use timatanga\Content2Pdf\Exceptions\EngineException;
use timatanga\Content2Pdf\Exceptions\OptionException;

class Renderer
{

    /**
     * @var string
     */
    protected $pageFormat = 'A4';

    /**
     * @var string
     */
    protected $pageOrientation = 'P';

    /**
     * @var string
     */
    protected $config = 'templates.php';

    /**
     * @var array
     */
    protected $templates = [];

    /**
     * @var Mpdf\Mpdf
     */
    protected $renderer;


   /**
     * Create instance 
     *
     * @param string $template 	template sets layout settings, default: none
     * @param array  $config 	custom configs
     */
   	public function __construct( ?string $template, array $config = []  )
   	{
   		// resolve template configurations
        $this->templates = $this->resolveConfig();

        // throw exception when template is required without having a configuration
        if ( is_null($this->templates) && !is_null($template) )
   			throw new OptionException('Can not assign template without predefined configurations: ' . $template);

        // throw exception when template is not found
        if ( !is_null($template) && !isset($this->templates[$template]) )
   			throw new OptionException('Unknown layout template: ' . $template);

   		if ( isset($this->templates[$template]['format']) )
   			$this->pageFormat = $this->templates[$template]['format'];

   		if ( isset($this->templates[$template]['orientation']) )
   			$this->pageOrientation = $this->templates[$template]['orientation'];   		

   		// merge custom config
   		$config = !is_null($template) ? array_merge($this->templates[$template], $config) : $config;

	   	// instantiate class
		$this->renderer = new Mpdf($config);
   	}


    /**
     * Resolve predefined template configurations
     * 
     * @return string|null
     */
    private function resolveConfig()
    {
    	// config file
    	$file = $this->config;

    	// laravel config path
    	$laravelPath = function_exists('config_path') ? config_path($file) : null;

    	// local config path
    	$localPath = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.$file;

		if ( static::fileExists($laravelPath) )
			return require $laravelPath;

		if ( static::fileExists($localPath) )
			return require $localPath;

		return null;
    }


    /**
     * Set Content Meta like title, subject, author, keywords
     * 
     * @param array  $meta
     * @return $this
     */
	public function setMeta( array $meta )
   	{
		if ( isset($meta['title']) )
			$this->renderer->SetTitle( $meta['title'] );

		if ( isset($meta['subject']) )
			$this->renderer->SetSubject( $meta['subject'] );

		if ( isset($meta['author']) )
			$this->renderer->SetAuthor( $meta['author'] );

		if ( isset($meta['keywords']) ) {
			$keywords = is_array($meta['keywords']) ? implode(',', $meta['keywords'] ) : $meta['keywords'];
			$this->renderer->SetKeywords( $keywords );
		}

		return $this;
   	}


	/**
	 * Sets an HTML page header
	 *
	 * @param string  $header 	content of the page header as a string of valid HTML code
	 * @param string  $side     specify whether to set the header for ODD or EVEN pages in a DOUBLE-SIDED document.
	 * @param bool    $force 	use if the header is being set after the new page has been added.
	 */
	public function setHeader( $header = '', $side = 0, $force = false )
	{
		if (! in_array($side, ['0', 'O', 'E']) )
   			throw new OptionException('Unknow header side configuration: ' . $side);

		$this->renderer->SetHTMLHeader($header, $side, $force);

		return $this;
	}


	/**
	 * Sets an HTML page footer
	 *
	 * @param string  $footer 	content of the page footer as a string of valid HTML code
	 * @param string  $side     specify whether to set the header for ODD or EVEN pages in a DOUBLE-SIDED document.
	 */
	public function setFooter( $footer = '', $side = 0 )
	{
		if (! in_array($side, ['0', 'O', 'E']) )
   			throw new OptionException('Unknow footer side configuration: ' . $side);

		$this->renderer->SetHTMLFooter($footer, $side);

		return $this;
	}


    /**
     * Set Stylesheet in Content
     * 
     * @return $this
     */
   	public function setStylesheet( string $stylesheet )
   	{
		$this->renderer->WriteHTML($stylesheet, HTMLParserMode::HEADER_CSS);

		return $this;
   	}


	/**
	 * Sets single CSS Style
	 *
	 * @param string  $key 			sets css attribute 
	 * @param string  $value 		sets css value 
	 */
	public function setStyle( string $key, $value )
	{
		$this->renderer->SetDefaultBodyCSS($key, $value);

		return $this;
	}


	/**
	 * Add a new page
	 *
	 * @return $this
	 */
	public function addPage()
	{
		$this->renderer->AddPage();

		return $this;
	}


	/**
	 * Insert HTML to Content
	 *
	 * @param string  $html 		string of valid HTML code
	 * @param bool    $close 		specify whether all HTML elements are closed. html must be ended true
	 */
	public function insertHtml( $html = null, $close = true)
	{
		try {

			$this->renderer->WriteHTML(utf8_encode($html), HTMLParserMode::DEFAULT_MODE, true, $close);

			return $this;

		} catch( MpdfException $e ) {

			throw new EngineException($e->getMessage());
		}

	}


	/**
	 * Insert Image to Content
	 *
	 * @param string  $image 		image file location or binary
	 * @param bool	  $fullpage 	image size is automatically constrained to current margins
	 * @param bool	  $watermark 	image is used as watermark
	 */
	public function insertImage( $image = null, $fullpage = false, $watermark = false )
	{
		// check if the given image is a binary
		if (! file_exists($image) ) 
   			throw new EngineException('Uknown image location: ' . $image);

		try {

			$top = $this->renderer->{'orig_tMargin'};

			$left = $this->renderer->{'orig_lMargin'};

			$this->renderer->Image($image, $top, $left, 0, 0, '', '', true, !$fullpage, $watermark );

			return $this;

		} catch( MpdfException $e ) {

			throw new EngineException($e->getMessage());
		}
	}


	/**
	 * Finalise the document and return the document as a string
	 *
	 * @return string	 
	 */
	public function stream()
	{
		try {

			return $this->renderer->Output('', 'S');

		} catch( MpdfException $e ) {

			throw new EngineException($e->getMessage());
		}
	}


	/**
	 * Finalise the document and send the file inline to the browser
	 *
	 * @param string  $filename 	$filename is used when one selects the “Save as”
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function inline($filename = 'document.pdf')
	{
		try {

			return $this->renderer->Output($filename, 'I');

		} catch( MpdfException $e ) {

			throw new EngineException($e->getMessage());
		}
	}


	/**
	 * Finalise the document and send to the browser and force a file download
	 *
	 * @param {string} $filename 	the name given by $filename.
	 * @return void
	 */
	public function download($filename = 'document.pdf')
	{
		try {

			return $this->renderer->Output($filename, 'D');

		} catch( MpdfException $e ) {

			throw new EngineException($e->getMessage());
		}
	}


	/**
	 * Finalise the document and save to a local file with the name given by $filename
	 *
	 * @param {string} $filename
	 * @return static
	 */
	public function save($filename = 'document.pdf')
	{
		try {
		
			return $this->renderer->Output($filename, 'F');

		} catch( MpdfException $e ) {

			throw new EngineException($e->getMessage());
		}

	}


	/**
	 * Get underlying renderer
	 *
	 * @return mPdf
	 */
	public function getRenderer()
	{
		return $this->renderer;
	}


	/**
	 * Set underlying mPdf configurations
	 *
	 * @param string  $name
	 * @param mixed  $value
	 * @return void
	 */
	public function __set( string $name, $value = null )
	{
		return $this->renderer->{$name} = $value;
	}


	/**
	 * Get underlying mPdf configurations
	 *
	 * @param string  $option
	 * @return string
	 */
	public function __get( string $option = null )
	{
		if ( is_null($option) )
			return null;

		if (! property_exists($this->renderer, $option))
   			throw new OptionException('Unknown configuration option: ' . $option);

		return $this->renderer->{$option};
	}


    /**
     * Call underlying mPdf methods
     *
     * @param string  $method      method name
     * @param array  $arguments    method arguments
     * @return void
     */
    public function __call( string $method , array $arguments = [] )
    {
        if ( count($arguments) == 1 )
            $arguments = $arguments[0];

        if (! method_exists($this->renderer, $method) )
        	throw new EngineException('Given method is not available: ' .$method);

        return $this->renderer->{$method}($arguments);
    }


    /**
     * Determine if file exists
     *
     * @param string      $file
     * @return bool
     */
    private static function fileExists($file)
    {
        if (!is_file($file) || !file_exists($file))
            return false;

        return true;
    }

}