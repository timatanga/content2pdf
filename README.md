# Content2Pdf
Render PDF documents based on different content is a basic requirement for web applications. 
This package is a wrapper around the amazing mPDF PHP library with some simplified access methods.


## Installation
composer require dbisapps/content2pdf


## Documentation
Please refer to the mPDF documentation https://mpdf.github.io/ for PDF rendering options.


## Configuration
Providing layout templates is an easy way to support web applications for a consistent look and feel of PDF documents.
This packages supports template configurations by a configuration file. Please refer to the mPDF package for supported options.
 
    'default' =>    [
        'mode'                      => 'de_DE',
        'format'                    => 'A4',
        'orientation'               => 'P',
        'margin_left'               => 15,
        'margin_right'              => 15,
        'margin_top'                => 15,
        'margin_bottom'             => 15,
        'margin_header'             => 10,
        'margin_footer'             => 10,
        'default_font_size'         => '11',
        'default_font'              => 'sans-serif',
    ],

    'invoice' => [
    ],


The configuration file is named templates.php and is located within the packages config directory.


## Laravel Configuration
While executing Laravel's vendor:publish command, the config file will be copied as template.php to Laravels config directory. Once the configuration has been published, its values may be accessed like any other configuration file: 

	config('template.default')


## Render HTML Content as PDF
Render HTML content as PDF is as easy as:

	$pdf = new Html2PDF;

    $pdf->include([
        'body' => 
        	'<html><body>
            	<h1>Test</h1>
            	<p><img src="//localhost/image.jpg"></a></p>
        	</body></html>'
    ]);

    $output = pdf->stream();

Above commands includes the given HTML body in the document and streams the PDF as string. 
On top or instead of template configurations, custom options can be passed to the constructor as second argument.
Please refer to the mPdf documentation for further details https://mpdf.github.io/configuration/configuration-v7-x.html

Please take note that configuration options are overriding template settings. 

	$pdf = new Html2PDF('default', ['orientation' => 'L']);


Besides the HTML body which is floating over as many pages as are required, provided header and footer content is printed on every page:

	$pdf = new Html2PDF('default', ['orientation' => 'L']);

    $pdf->include([
        'header' => 
        	'<div>This is a header printed on every page</div>',
        'body' => 
        	'<html><body>
            	<h1>Test</h1>
            	<p><img src="//localhost/image.jpg"></a></p>
        	</body></html>'
        'footer' => 
        	'<footer>This is a footer printed on every page</footer>',
    ]);


A PDF document can be enriched by metadata like title, subject, author and keywords:

	$pdf = new Html2PDF('default', ['orientation' => 'L']);

    $pdf->include([
    	'meta' => [
    		'title' => 'Test Document',
    		'subject' => 'Just for testing purposes',
    		'author' => 'John Doe',
    		'keywords' => ['test', 'html2pdf'],
    	],
        'header' => 
        	'<div>This is a header printed on every page</div>',
        'body' => 
        	'<html><body>
            	<h1>Test</h1>
            	<p><img src="//localhost/image.jpg"></a></p>
        	</body></html>'
        'footer' => 
        	'<footer>This is a footer printed on every page</footer>',
    ]);


Render an HTML page without styles, uugg that sounds like an ugly plan, but wait there is a further option:

	$pdf = new Html2PDF('default', ['orientation' => 'L']);

    $pdf->include([
    	'meta' => [
    		'title' => 'Test Document',
    		'subject' => 'Just for testing purposes',
    		'author' => 'John Doe',
    		'keywords' => ['test', 'html2pdf'],
    	],
        'header' => 
        	'<div>This is a header printed on every page</div>',
        'body' => 
        	'<html><body>
            	<h1>Test</h1>
            	<p><img src="//localhost/image.jpg"></a></p>
        	</body></html>'
        'footer' => 
        	'<footer>This is a footer printed on every page</footer>',
        'stylesheet' => '<include the stylesheet here>'
    ]);



## Render Image as PDF
For sure, images can be included within HTML content and rendered as described above. When you think of images or screenshots you'd like to format in a printable manner there must be a better choice. For this reasing the Image2Pdf come to rescue.

An image size is can be automatically constrained to current page margins or otherwise being croped and split on multiple pages. 
While the image width is always constrained within the page margins, it's height is croped when it doesn't fit on a page.

All configuration options described above are applicable for image contents except "body" which is only valid for HTML content. In place of "body", the key "image" comes into play:

	$pdf = new Image2Pdf('default',);

    $pdf->include([
        'header' => 
        	'<div>This is a header printed on every page</div>',
        'image' => 'path/location of image',
        'footer' => 
        	'<footer>This is a footer printed on every page</footer>',
    ]);



## PDF Output
Streaming PDF content as string is just one of several options. 

	 // finalise the document and return the document as a string
	$pdf->stream();

	// finalise the document and send the file inline to the browser
	$pdf->inline($filename);

	// finalise the document and send to the browser and force a file download
	$pdf->download($filename);

	// finalise the document and save to a local file with the name given by $filename
	$pdf->save($filename = 'document.pdf')



## Accessing mPDF capabilities
Any methods provided by the underlying mPdf package are accessible via magic PHP methods. 
Both classes, Html2Pdf as well as Image2Pdf are passing any call to an unknown property or method to the mPdf class. Therefor you can see these two classes as syntactic sugar on top of mPDF.


