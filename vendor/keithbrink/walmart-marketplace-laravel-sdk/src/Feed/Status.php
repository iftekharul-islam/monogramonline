<?php 

namespace KeithBrink\Walmart\Feed;

use KeithBrink\Walmart\WalmartCore;

class Status extends WalmartCore {

    public function all()
    {
        $this->setMethod('GET');
        $this->setEndpoint('v3/feeds');
        $this->sendRequest();

        $response = $this->getResponse();

        return $response;
    }

    public function one($feed_id)
    {
        $this->setMethod('GET');
        $this->setEndpoint('v3/feeds');
        $this->setGetData([
            'feedId' => $feed_id
        ]);
        $this->sendRequest();

        $response = $this->getResponse();

        return $response;
    }

    public function oneWithItems($feed_id)
    {
        $this->setMethod('GET');
        $this->setEndpoint('v3/feeds/'.$feed_id);
        $this->sendRequest();

        $response = $this->getResponse();

        return $response;
    }

}
