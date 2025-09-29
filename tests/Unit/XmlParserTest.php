<?php

namespace Tests\Unit;

use App\Logger\Logger;
use App\Parser\XmlParser;
use PHPUnit\Framework\TestCase;

class XmlParserTest extends TestCase
{
    private XmlParser $xmlParser;
    private Logger $logger;

    protected function setUp(): void
    {
        $this->logger = new Logger(sys_get_temp_dir() . '/test.log');
        $this->xmlParser = new XmlParser($this->logger);
    }

    public function testParseXmlWithValidXml(): void
    {
        $xmlContent = '<?xml version="1.0"?><root><item><name>Test</name><price>10.00</price></item></root>';
        
        $result = $this->xmlParser->parseXml($xmlContent);
        
        $this->assertNotNull($result);
        $this->assertEquals('root', $result->getName());
    }

    public function testParseXmlWithInvalidXml(): void
    {
        $xmlContent = 'invalid xml content';
        
        $result = $this->xmlParser->parseXml($xmlContent);
        
        $this->assertNull($result);
    }

    public function testExtractDataFromXml(): void
    {
        $xmlContent = '<?xml version="1.0"?><root><item><name>Test</name><price>10.00</price></item><item><name>Test2</name><price>20.00</price></item></root>';
        
        $xml = $this->xmlParser->parseXml($xmlContent);
        $data = $this->xmlParser->extractData($xml);
        
        $this->assertCount(2, $data);
        $this->assertEquals('Test', $data[0]['name']);
        $this->assertEquals('10.00', $data[0]['price']);
        $this->assertEquals('Test2', $data[1]['name']);
        $this->assertEquals('20.00', $data[1]['price']);
    }

    public function testGetTableHeaders(): void
    {
        $data = [
            ['name' => 'Test1', 'price' => '10.00'],
            ['name' => 'Test2', 'price' => '20.00', 'category' => 'A']
        ];
        
        $headers = $this->xmlParser->getTableHeaders($data);
        
        $this->assertContains('name', $headers);
        $this->assertContains('price', $headers);
        $this->assertContains('category', $headers);
        $this->assertCount(3, $headers);
    }

    public function testGetTableHeadersWithEmptyData(): void
    {
        $headers = $this->xmlParser->getTableHeaders([]);
        
        $this->assertEmpty($headers);
    }
}
