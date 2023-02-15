<?php

namespace App\Http\Other;

class Maker {

    public $event;
    public $key;
    public $values;

    function __construct($event, $key)
    {
        $this->event = $event;
        $this->key = $key;
        $this->values = array();
    }

    function setValues($values = array())
    {
        $this->values = $values;
    }

    public function getValues() : array {
        return $this->values;
    }

    function trigger()
    {
        $url = 'https://maker.ifttt.com/trigger/'.$this->event.'/json/with/key/'.$this->key;
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($this->getValues()));
        curl_setopt($curl, CURLOPT_HTTPHEADER,
        [
           "Content-Type: application/json"
        ]);

       // $response = curl_exec($curl);

     //   curl_close($curl);

       //  dd($response);
    }

}