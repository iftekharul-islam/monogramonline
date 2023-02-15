<?php 

namespace Monogram;

use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Log;

class Crawler
{

    public static function getJSON ($id_catalog)
    {
      
      // $id_catalog = $request->get('id_catalog');
      // $store_name = $request->get('store_name');
      
      $url = 'https://www.monogramonline.com/' . $id_catalog . '.html';
      Log::info($url);
      
      $client = new Client();
      $client->setClient(new GuzzleClient(array(
          // DISABLE SSL CERTIFICATE CHECK
          'verify' => false,
      )));
      
      try {
        $crawler = $client->request('GET', $url);
      } catch (\Exception $e) {
        Log::error('crawl error: ' . $e->getMessage() . ' url: ' . $url);
        return $e->getMessage();
      }
      
      try {
        $price = $crawler->filter('div.price-box')->first()->text();
        
        $values = $crawler->filter('div.product-options')->each(function ($node) {
          
          $labels = $node->filter('label')->each(function ($nodeSubcategory) {
            return trim(str_replace('*', '', $nodeSubcategory->text()));
          });
          
          $i = 0;
          
          $values = $node->filter('.input-box')->each(function ($subNode) use (&$i, $labels) {
             
              if (strpos($subNode->html(), 'type="text"')) {
                
                $values = $subNode->filter('input')->each(function ($subSubNode) use ($i, $labels) { 
                  return [
                      'type' => 'text',
                      'max' => $subSubNode->attr('maxlength'),
                      'label' => $labels[$i],
                      'short' => $i,
                      'note' => ''
                    ];
                });
                
                $values = $values[0];
                
              } else {
                
                $options = $subNode->filter('option')->each(function ($subSubNode) {
                  
                  $text = str_replace('--', '', $subSubNode->text());
                  
                  if (!strpos($text, '+$')) {
                    $text = trim($text);
                  } else {
                    $text = trim(substr($text, 0, strpos($text, '+$')));
                  }

                  return [
                      'price' => $subSubNode->attr('price') != null ? $subSubNode->attr('price') : 0,
                      'text' => $text,
                      'value' => $text
                    ]; 
                });
                
                
                $values = [
                    'type' => 'select',
                    'label' => $labels[$i],
                    'options' => $options,
                    'short' => $i,
                    'note' => ''
                  ];
              }
              
              $i++;

              return $values;
           });
           return $values;
        });
        
      } catch (\Exception $e) {
        
        Log::error('Crawler: ' . $e->getMessage());
        return FALSE;
      }
      
      if (count($values) == 0) {
        return FALSE;
      }
      
      $json = json_encode([$id_catalog => $values[0], 'price' => trim(str_replace('$', '', $price))]);
      return $json;
    }
}
