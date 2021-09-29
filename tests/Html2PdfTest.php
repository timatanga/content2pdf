<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use timatanga\Content2Pdf\Exceptions\EngineException;
use timatanga\Content2Pdf\Html2Pdf;

class Html2PdfTest extends TestCase
{

    private $pdf;


    protected function set_up()
    {
        $this->pdf = new Html2Pdf();
    }


    public function testPdfOutput()
    {
        $this->set_up();

        $this->pdf->include([
            'body' => '<html><body>
                <h1>Test</h1>
                <p><img src="//localhost/image.jpg"></a></p>
            </body></html>'
        ]);

        $output = $this->pdf->stream();

        $this->assertStringStartsWith('%PDF-', $output);

        $dateRegex = '\(D:\d{14}[+|-|Z]\d{2}\'\d{2}\'\)';

        $this->assertMatchesRegularExpression('/\d+ 0 obj\n<<\n\/Producer \((.*?)\)\n\/CreationDate ' . $dateRegex . '\n\/ModDate ' . $dateRegex . '/', $output);
    }


    public function testPdfAssociatedFilesPath()
    {
        $this->set_up();

        $this->pdf->PDFA = true;
        $this->pdf->PDFAauto = true;

        $this->pdf->SetAssociatedFiles([[
            'name' => 'public_filename.xml',
            'mime' => 'text/xml',
            'description' => 'some description',
            'AFRelationship' => 'Alternative',
            'path' => __DIR__ . '/data/test.xml'
        ]]);

        $this->pdf->insertHtml('<html><body>hello world</body></html>');

        $output = $this->pdf->stream();

        $this->assertStringStartsWith('%PDF-', $output);
        $this->assertMatchesRegularExpression('/\d+ 0 obj\n<<\/F \(public_filename\.xml\)\n\/Desc \(some description\)/', $output);
        $this->assertMatchesRegularExpression('/\/Type \/Filespec\n\/EF <<\n\/F \d+ 0 R\n\/UF \d+ 0 R\n>>\n\/AFRelationship \/Alternative/', $output);
        $this->assertMatchesRegularExpression('/\d+ 0 obj\n<<\/Type \/EmbeddedFile\n\/Subtype \/text#2Fxml\n\/Length \d+\n\/Filter \/FlateDecode\n\/Params \<\<\/ModDate \(D:\d{14}[+|-|Z]\d{2}\'\d{2}\'\)/', $output);
        $this->assertMatchesRegularExpression('/\/AF \d+ 0 R\n\/Names << \/EmbeddedFiles << \/Names \[\(public_filename\.xml\) \d+ 0 R\]/', $output);
    }


    public function testPdfAssociatedFilesContent()
    {
        $this->set_up();

        $this->pdf->PDFA = true;
        $this->pdf->PDFAauto = true;
        $this->pdf->SetAssociatedFiles([[
            'name' => 'public_filename.xml',
            'mime' => 'text/xml',
            'description' => 'some description',
            'AFRelationship' => 'Alternative',
            'content' => '<?xml version="1.0" encoding="UTF-8"?><note><body>Hello World</body></note>'
        ]]);

        $this->pdf->writeHtml('<html><body>hello world</body></html>');

        $output = $this->pdf->stream();

        $this->assertStringStartsWith('%PDF-', $output);
        $this->assertMatchesRegularExpression('/\d+ 0 obj\n<<\/F \(public_filename\.xml\)\n\/Desc \(some description\)/', $output);
        $this->assertMatchesRegularExpression('/\/Type \/Filespec\n\/EF <<\n\/F \d+ 0 R\n\/UF \d+ 0 R\n>>\n\/AFRelationship \/Alternative/', $output);
        $this->assertMatchesRegularExpression('/\d+ 0 obj\n<<\/Type \/EmbeddedFile\n\/Subtype \/text#2Fxml\n\/Length \d+\n\/Filter \/FlateDecode\n\/Params \<\<\/ModDate \(D:\d{14}[+|-|Z]\d{2}\'\d{2}\'\)/', $output);
        $this->assertMatchesRegularExpression('/\/AF \d+ 0 R\n\/Names << \/EmbeddedFiles << \/Names \[\(public_filename\.xml\) \d+ 0 R\]/', $output);
    }

    public function testPdfAdditionalXmpRdf()
    {
        $this->set_up();

        $this->pdf->PDFA = true;
        $this->pdf->PDFAauto = true;
        $this->pdf->SetAdditionalXmpRdf($this->ZugferdXmpRdf());

        $this->pdf->insertHtml('<html><body>hello world</body></html>');

        $output = $this->pdf->stream();

        $this->assertStringStartsWith('%PDF-', $output);
        $this->assertMatchesRegularExpression('/<zf:DocumentFileName>ZUGFeRD-invoice\.xml<\/zf:DocumentFileName>/', $output);
    }


    private function ZugferdXmpRdf()
    {
        $s  = '<rdf:Description rdf:about="" xmlns:zf="urn:ferd:pdfa:CrossIndustryDocument:invoice:1p0#">'."\n";
        $s .= '  <zf:DocumentType>INVOICE</zf:DocumentType>'."\n";
        $s .= '  <zf:DocumentFileName>ZUGFeRD-invoice.xml</zf:DocumentFileName>'."\n";
        $s .= '  <zf:Version>1.0</zf:Version>'."\n";
        $s .= '  <zf:ConformanceLevel>BASIC</zf:ConformanceLevel>'."\n";
        $s .= '</rdf:Description>'."\n";
        return $s;
    }
}
