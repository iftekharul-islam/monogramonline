<?php 

namespace KeithBrink\Walmart\Order;

use KeithBrink\Walmart\WalmartCore;
use Carbon\Carbon;

class Modify extends WalmartCore {

    public function acknowledge($order_id)
    {
        $this->setMethod('POST');
        $this->setEndpoint('v3/orders/'.$order_id.'/acknowledge');
        $this->sendRequest();

        $response = $this->getResponse();

        return $response;
    }

    public function cancelLine($order_id, $line_number = 1)
    {
        $xml = '
            <?xml version="1.0" encoding="UTF-8"?>
            <orderCancellation xmlns:ns2="http://walmart.com/mp/v3/orders" xmlns:ns3="http://walmart.com/">
                <orderLines>
                    <orderLine>
                        <lineNumber>'.$line_number.'</lineNumber>
                        <orderLineStatuses>
                            <orderLineStatus>
                                <status>Cancelled</status>
                                <cancellationReason>CANCEL_BY_SELLER</cancellationReason>
                                <statusQuantity>
                                    <unitOfMeasurement>EACH</unitOfMeasurement>
                                    <amount>1</amount>
                                </statusQuantity>
                            </orderLineStatus>
                        </orderLineStatuses>
                    </orderLine>
                </orderLines>
            </orderCancellation>
        ';

        $this->setMethod('POST');
        $this->setEndpoint('v3/orders/'.$order_id.'/cancel');
        $this->setPostXmlData($xml);
        $this->sendRequest();

        $response = $this->getResponse();

        return $response;
    }

    public function shipItem($order_id, Carbon $ship_date, $carrier_name, $tracking_number, $tracking_url = '', $line_number = 1, $quantity = 1, $method = 'Standard') {
        
	$xml = 
	 '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
            <ns2:orderShipment xmlns:ns2="http://walmart.com/mp/v3/orders" xmlns:ns3="http://walmart.com/">
                <ns2:orderLines>
                    <ns2:orderLine>
                        <ns2:lineNumber>'.$line_number.'</ns2:lineNumber>
                        <ns2:orderLineStatuses>
                            <ns2:orderLineStatus>
                                <ns2:status>Shipped</ns2:status>
                                <ns2:statusQuantity>
                                    <ns2:unitOfMeasurement>Each</ns2:unitOfMeasurement>
                                    <ns2:amount>' . $quantity . '</ns2:amount>
                                </ns2:statusQuantity>
                                <ns2:trackingInfo>
                                    <ns2:shipDateTime>'.substr($ship_date->toIso8601String(),0,19).'.000Z</ns2:shipDateTime>
                                    <ns2:carrierName>
                                        <ns2:carrier>'.$carrier_name.'</ns2:carrier>
                                    </ns2:carrierName>
                                    <ns2:methodCode>'.$method.'</ns2:methodCode>
                                    <ns2:trackingNumber>'.$tracking_number.'</ns2:trackingNumber>
                                </ns2:trackingInfo>
                            </ns2:orderLineStatus>
                        </ns2:orderLineStatuses>
                    </ns2:orderLine>
                </ns2:orderLines>
            </ns2:orderShipment>';

        $this->setMethod('POST');
        $this->setEndpoint('v3/orders/'.$order_id.'/shipping');
        $this->setPostXmlData($xml);
        $this->sendRequest();

        $response = $this->getResponse();

        return $response;
    }
}
