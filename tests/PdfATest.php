<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use timatanga\Content2Pdf\Renderer;

class PdfATest extends TestCase
{

    /**
     * @var \Mpdf\Mpdf
     */
    private $pdf;


    protected function set_up()
    {
        $this->pdf = new Renderer(null, ['PDFA' => true, 'PDFAauto' => true]);
        $this->pdf->insertHtml('<html><body>PDFA Test</body></html>');
    }


    public function testOriginalPDFA_1B()
    {
        $this->set_up();

        $output = $this->pdf->stream();
        $output = preg_replace('/rdf:about="uuid:[\w-]+"/', 'rdf:about="uuid:fake-uuid"', $output);

        $expected = '   <rdf:Description rdf:about="uuid:fake-uuid" xmlns:pdfaid="http://www.aiim.org/pdfa/ns/id/" >' . "\n";
        $expected .= '    <pdfaid:part>1</pdfaid:part>' . "\n";
        $expected .= '    <pdfaid:conformance>B</pdfaid:conformance>' . "\n";
        $expected .= '    <pdfaid:amd>2005</pdfaid:amd>' . "\n";
        $expected .= '   </rdf:Description>' . "\n";

        $this->assertStringContainsString($expected, $output);
    }


    public function testPDFA_Version_Fail()
    {
        $this->set_up();

        $this->pdf->PDFAversion = '11';
        try {
            $this->pdf->stream();
        } catch (\Exception $e) {
            $this->assertSame('PDFA version (11) is not valid. (Use: 1-B, 3-B, etc.)', $e->getMessage());
        }
    }


    public function testOriginalPDFA_3B()
    {
        $this->set_up();

        $this->pdf->PDFAversion = '3-B';

        $output = $this->pdf->stream();
        $output = preg_replace('/rdf:about="uuid:[\w-]+"/', 'rdf:about="uuid:fake-uuid"', $output);

        $expected = '   <rdf:Description rdf:about="uuid:fake-uuid" xmlns:pdfaid="http://www.aiim.org/pdfa/ns/id/" >' . "\n";
        $expected .= '    <pdfaid:part>3</pdfaid:part>' . "\n";
        $expected .= '    <pdfaid:conformance>B</pdfaid:conformance>' . "\n";
        $expected .= '   </rdf:Description>' . "\n";

        $this->assertStringContainsString($expected, $output);
    }
}
