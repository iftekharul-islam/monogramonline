<?php


namespace App\Http\Controllers;


use App\ShipmentManifest;
use App\ShipmentManifestBatch;
use EasyPost\Batch;
use EasyPost\EasyPost;
use EasyPost\Error;
use EasyPost\ScanForm;
use EasyPost\Shipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class USPSManifestController extends Controller
{
    protected $devKey = "EZTKcf12d106bf264af5b027250ce8bcc958Nic9Pmid2L28FaPQgBG5pw";
    protected $prodKey = "EZAKcf12d106bf264af5b027250ce8bcc958jg55lbapz7Z4MNQuc9Z9DA";

    public function indexAction()
    {
        return view('usps_manifest.index');
    }

    public function listShipmentsAction(Request $request)
    {
        $result = ShipmentManifest::where('ship_from', $request->query('location'))
            ->where('batched', 0)
            ->get();

        return new JsonResponse(['success' => true, 'shipments' => $result->toArray()]);
    }

    public function listShipmentBatchScanFormsAction(Request $request)
    {
        $result = ShipmentManifestBatch::all();
        return new JsonResponse(['success' => true, 'batches' => $result->toArray()]);
    }

    public function getZplLabelAction(Request $request)
    {
        EasyPost::setApiKey($this->prodKey);
        $shipment = Shipment::retrieve($request->query('ship_id'));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $shipment->postage_label->label_zpl_url);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true); // Videos are needed to transfered in binary
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $fileResponse = curl_exec($ch);
        curl_close($ch);

        if (substr($fileResponse, 0, 7) == '<Error>') {
            $doc = simplexml_load_string($label);
            return 'EasyPost Label Error: ' . $doc->Code . ' - ' . $doc->Message;
        } else {
            $fp = fopen(public_path('assets/images/shipping_label/') . $request->query('invoice_number') . '.zpl', 'w');
            fwrite($fp, $fileResponse);
            fclose($fp);
        }

        return new JsonResponse(['success' => true]);
    }

    public function createManifestAction(Request $request)
    {
        $shipmentIds = json_decode($request->get('shipments'), true);

        EasyPost::setApiKey($this->prodKey);

        $shipments = [];

        foreach ($shipmentIds as $shipmentId) {
            $shipments [$shipmentId] = Shipment::retrieve($shipmentId);
        }
        $shipments = array_values($shipments);
        try {
            ShipmentManifest::whereIn('ship_id', $shipmentIds)->update(['batched' => 1]);
            $batchObject = Batch::create(array('shipments' => $shipments));

            $scanForm = new ShipmentManifestBatch();
            $scanForm->ship_from = $request->get('location');
            $scanForm->batch_id = $batchObject->id;
            $scanForm->batch_status = $batchObject->state;
            $scanForm->num_shipments = $batchObject->num_shipments;
            $scanForm->batch_object = $batchObject->__toJSON();
            $scanForm->save();

            // request a scan form
//            try {
                $batchObject->create_scan_form();
                while (empty($batchObject->scan_form)) {
                    sleep(3);
                    $batchObject->refresh();
                }
//            } catch (Error $e) {
//                return new JsonResponse(
//                    [
//                        'success' => false, 'msg' => $e->getMessage()
//                    ]
//                );
//            }

            $scanFormObject = ScanForm::retrieve($batchObject->scan_form->id);

            $scanForm->sf_id = $scanFormObject->id;
            $scanForm->form_url = $scanFormObject->form_url;
            $scanForm->sf_status = $scanFormObject->status;
            $scanForm->sf_object = $scanFormObject->__toJSON();
            $scanForm->save();

        } catch (Error $e) {
            return new JsonResponse(
                [
                    'success' => false, 'msg' => $e->getMessage()
                ]
            );
        }


        return new JsonResponse(
            [
                'success' => true, 'msg' => sprintf('Scan Form %s for Batch %s', $scanForm->sf_id, $scanForm->batch_id)
            ]
        );
    }
}