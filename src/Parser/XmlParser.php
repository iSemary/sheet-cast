<?php

namespace App\Parser;

use App\Logger\Logger;
use SimpleXMLElement;

class XmlParser
{
    private Logger $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function parseXml(string $xmlContent): ?SimpleXMLElement
    {
        try {
            $this->logger->info('Parsing XML content', ['size' => strlen($xmlContent)]);
            
            $xml = @simplexml_load_string($xmlContent);
            
            if ($xml === false) {
                throw new \Exception('Failed to parse XML content');
            }
            
            $this->logger->info('XML parsed successfully', [
                'root_element' => $xml->getName(),
                'children_count' => count($xml->children())
            ]);
            
            return $xml;
            
        } catch (\Exception $e) {
            $this->logger->error('XML parsing failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function extractData(SimpleXMLElement $xml): array
    {
        $data = [];
        
        try {
            $this->logger->info('Extracting data from XML');
            
            foreach ($xml->children() as $child) {
                $data[] = $this->extractElementData($child);
            }
            
            $this->logger->info('Data extraction completed', ['records_count' => count($data)]);
            
        } catch (\Exception $e) {
            $this->logger->error('Data extraction failed', ['error' => $e->getMessage()]);
        }
        
        return $data;
    }

    private function extractElementData(SimpleXMLElement $element): array
    {
        $data = [];
        
        foreach ($element->children() as $child) {
            $data[$child->getName()] = (string) $child;
        }
        
        foreach ($element->attributes() as $name => $value) {
            $data['attr_' . $name] = (string) $value;
        }
        
        return $data;
    }

    public function getTableHeaders(array $data): array
    {
        if (empty($data)) {
            return [];
        }
        
        $headers = [];
        foreach ($data as $row) {
            $headers = array_merge($headers, array_keys($row));
        }
        
        return array_unique($headers);
    }

    public function getDataSummary(array $data): array
    {
        if (empty($data)) {
            return [
                'total_records' => 0,
                'fields' => [],
                'sample_record' => []
            ];
        }

        $headers = $this->getTableHeaders($data);
        
        return [
            'total_records' => count($data),
            'fields' => $headers,
            'sample_record' => $data[0] ?? []
        ];
    }
}
