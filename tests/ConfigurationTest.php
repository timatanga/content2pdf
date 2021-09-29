<?php

namespace Tests;

use Mpdf\PageFormat;
use PHPUnit\Framework\TestCase;
use timatanga\Content2Pdf\Renderer;

class ConfigurationTest extends TestCase
{

    public function testDefaultSettings()
    {
        $engine = new Renderer();

        $this->assertSame('1.4', $engine->pdf_version);
        $this->assertSame(2000, $engine->maxTTFFilesize);
        $this->assertFalse($engine->autoPadding);
    }


    public function testOverwrittenSettings()
    {
        $engine = new Renderer([
            'pdf_version' => '1.5',
            'autoPadding' => true,
            'nonexisting_key' => true,
        ]);

        $this->assertSame('1.5', $engine->pdf_version);
        $this->assertTrue($engine->autoPadding);
    }


    public function testFontSettings()
    {
        $engine = new Renderer([
            'fontDir' => [
                __DIR__ . '/ttf',
            ],
            'fontdata' => ['angerthas' => [
                'R' => 'angerthas.ttf',
            ]],
            'default_font' => 'angerthas'
        ]);

        $this->assertArrayHasKey('angerthas', $engine->fontdata);
        $this->assertSame('angerthas', $engine->default_font);
    }


    public function testOrientationSettings()
    {
        $format = 'A4';
        $format_size = PageFormat::getSizeFromName($format);

        // Set format to A4 and orientation to L
        $engine = new Renderer([
            'format' => $format.'-L',
        ]);

        $this->assertSame('L', $engine->DefOrientation);
        $this->assertSame($format_size[0], $engine->fwPt);
        $this->assertSame($format_size[1], $engine->fhPt);

        // Set format to A4 and orientation to P
        $engine = new Renderer([
            'format' => $format.'-P',
        ]);

        $this->assertSame('P', $engine->DefOrientation);
        $this->assertSame($format_size[0], $engine->fwPt);
        $this->assertSame($format_size[1], $engine->fhPt);

        // Set format to A4 and orientation to P
        $engine = new Renderer([
            'format' => $format,
        ]);

        $this->assertSame('P', $engine->DefOrientation);
        $this->assertSame($format_size[0], $engine->fwPt);
        $this->assertSame($format_size[1], $engine->fhPt);

        // Set format to A4 and orientation to L, ignoring "orientation" key
        $engine = new Renderer([
            'format' => $format.'-L',
            'orientation' => 'P',
        ]);

        $this->assertSame('L', $engine->DefOrientation);
        $this->assertSame($format_size[0], $engine->fwPt);
        $this->assertSame($format_size[1], $engine->fhPt);

        // Set format to A4 and orientation to L, ignoring "orientation" key
        $engine = new Renderer([
            'format' => $format.'-P',
            'orientation' => 'L',
        ]);

        $this->assertSame('P', $engine->DefOrientation);
        $this->assertSame($format_size[0], $engine->fwPt);
        $this->assertSame($format_size[1], $engine->fhPt);

        // Set format to A4 and orientation to P
        $engine = new Renderer([
            'format' => $format,
            'orientation' => 'P',
        ]);

        $this->assertSame('P', $engine->DefOrientation);
        $this->assertSame($format_size[0], $engine->fwPt);
        $this->assertSame($format_size[1], $engine->fhPt);

        // Set format to A4 and orientation to L
        $engine = new Renderer([
            'format' => $format,
            'orientation' => 'L',
        ]);

        $this->assertSame('L', $engine->DefOrientation);
        $this->assertSame($format_size[0], $engine->fwPt);
        $this->assertSame($format_size[1], $engine->fhPt);
    }
}
