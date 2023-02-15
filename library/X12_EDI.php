<?php
namespace Monogram;

class X12_EDI
{  
      /**
      * Parse an EDI document. Data will be returned as an array of instances of
      * EDI\Document. Document should contain exactly one ISA/IEA envelope.
      */
      public static function parse ($res, $segment_terminator = NULL, $element_separator = NULL, $subelement_separator = NULL) {
        
          $string = '';
          $segments = array();
          
          if (!$res) {
              throw new \Exception('No resource or string passed to parse()');
          }
          
          if (is_resource($res)) {
            
              $res = $data;
              $meta = stream_get_meta_data($res);
              if (!$meta['seekable']) {
                  throw new \Exception('Stream is not seekable');
              }
               
              throw new \Exception('Not implemented!');            
              
          } else {
              
              $data = file_get_contents($res);
              
              if ($segment_terminator == NULL) {
                $segment_terminator = substr($data, 105, 1);
                $raw_segments = explode($segment_terminator, $data);
              } else if ($segment_terminator == 'newline') {
                $raw_segments = explode(PHP_EOL, $data); 
              } else {
                $raw_segments = explode($segment_terminator, $data); 
              }
              
              if ($element_separator == NULL) {
                $element_separator = substr($data, 3, 1);
              }
              
              if ($subelement_separator == NULL) {
                $subelement_separator = substr($data, 104, 1);
              }
          }
          
          $results = array();
          $result = array();          
          $prefix = '';
          $suffix = ''; 
          
          foreach ($raw_segments as $segment) {
              $segment = str_replace("\n", '', $segment); 
              $elements = explode($element_separator, $segment);
              $identifier =  strtoupper($elements[0]);
              unset($elements[0]);
              $count = 1;
              
              
              switch ($identifier) {
                case 'ST':
                  $results[] = $result;
                  $result = array();
                  $prefix = '';
                  $suffix = '';
                  break;
                case 'REF':
                  $prefix = $elements[1] . '-';
                  $suffix = '';
                  break;
                case 'N1':
                  $prefix = $elements[1] . '-';
                  $suffix = '';
                  break;
                case 'N9':
                  $prefix = '';
                  $suffix = $elements[2];
                  break;
                case 'PO1':
                  $prefix = '';
                  $suffix = sprintf("%02d",$elements[1])  . '-';
                  break;
                case 'CTT':
                  $prefix = '';
                  $suffix = '';
                  break;
                case 'PER':
                  $prefix = $elements[1] . '-';
                  $suffix = '';
                  break;
                case 'MSG':
                  $prefix = '';
                  $suffix = isset($elements[3]) ? sprintf("%02d",$elements[3])  . '-' : '';
                  break;
              }
              
              foreach ($elements as $element) {
                
                while (isset($result[$prefix . $identifier . $suffix . sprintf("%02d", $count)])) {
                  $prefix = '*' . $prefix;
                }
                $result[$prefix . $identifier . $suffix . sprintf("%02d", $count)] = str_replace('~','',$element);
                $count++;
              }
              
              switch ($identifier) {
                case 'MSG':
                  $prefix = '';
                  $suffix = '';
                  break;
                case 'SE':
                  $prefix = '';
                  $suffix = '';
                  break;
                case 'PER':
                  $prefix = '';
                  $suffix = '';
                  break;
              }
                                   
          }
          $results[] = $result;
          return $results;
      }


}

?>