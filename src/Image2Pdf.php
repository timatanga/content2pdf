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

use Mpdf\PageFormat;
use timatanga\Content2Pdf\Renderer;
use timatanga\Content2Pdf\Exceptions\ContentException;

class Image2Pdf extends Renderer
{
	
    /**
     * Create instance 
     *
     * @param array  $config 	custom configs
     * @param string $template 	template sets layout settings, default: none
     */
   	public function __construct( ?string $template, array $config = []  )
   	{
   		parent::__construct($template, $config);
   	}


    /**
     * Include Image as Content
     *
     * @param  array   $attributes
     * @return $this           
     */
    public function include( array $attributes = [] )
	{
		if (! isset($attributes['image']) || empty($attributes['image']))
			throw new ContentException('Invalid content, an Image is required');

		if (! is_array($attributes['image']) )
			throw new ContentException('Invalid content, Image properties must be provides as array');

		$this->includeMeta($attributes);

		$this->includeHeader($attributes);

		$this->includeFooter($attributes);

		$this->includeStylesheet($attributes);

		$this->includeImage($attributes);

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
     * Include Image
     *
     * @param  array   $attributes
     * @return $this           
     */
	private function includeImage( array $attributes )
	{
		if (! isset($attributes['image']) || empty($attributes['image']) )
			throw new ContentException('Invalid content, Image must be provided');

		if (! $image = getimagesize($attributes['image']) )
			throw new ContentException('Invalid content, could not properly identify image');

        // apply white background
        $this->setStyle('background-color', '#ffffff');

        // slice image into page ration
        $slices = $this->sliceImage( $attributes['image'] );

        // add page with image per slice
        foreach ($slices as $slice) {

        	// add sliced image on new page
            $this->addPage()->insertImage($slice);

            // delete sliced image
            unlink($slice);
        }

        return $this;
	}


    /**
     * Slice image 
     *
     * @param  string  $imgPath
     * @return array           
     */
    private function sliceImage( $imgPath )
    {
        // get page size depending on page format
        [$pageWidth, $pageHeight] = PageFormat::getSizeFromName($this->pageFormat);

        // get image size
        [$imgWidth, $imgHeight] = getimagesize($imgPath);

        $slices = [];

        // cropped image hight
        $h = round($pageHeight/$pageWidth * $imgWidth) > $imgHeight ? $imgHeight : round($pageHeight/$pageWidth * $imgWidth);

        // starting y position
        $y = 0; 

        // flag for processing loop
        $process = true;

        do {
            // adjust image height for last slice
            if ( $y + $h > $imgHeight ) $h = $imgHeight - $y;                        

            // take image slice
            $slices[] = $this->cropImage($image, 0, $y, $imgWidth, $h);

            // evalute end of processing
            if ( $y + $h >= $imgHeight ) $process = false;

            // shift y position
            $y = $y + $h;

        } while ($process);

        return $slices;

    }


    /**
     * Crop image to given size
     * 
     * @param image/png  $imgPath   png image
     * @param int  $x         x position
     * @param int  $y         y position
     * @param int  $width     image width
     * @param int  $height    image height
     * @return image/png
     */
    private function cropImage( $imgPath, $x = 0, $y = 0, $width = 0, $height = 0 )
    {
        // transform image as gd resource
        $img = imagecreatefrompng($imgPath);

        $i = imagecrop($img, ['x' => $x, 'y' => $y, 'width' => $width, 'height' => $height]);
        if ( $i == FALSE ) throw new ContentException('Failed to resize image');

        imagedestroy($img);

        $croppedImg = $image . '-' . $y;

        imagepng($i, $croppedImg);

        return $croppedImg;
    }

}