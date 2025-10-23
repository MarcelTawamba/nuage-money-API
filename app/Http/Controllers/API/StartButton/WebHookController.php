<?php

namespace App\Http\Controllers\API\StartButton;

use App\Http\Controllers\Controller;
use App\Services\StartButton\CollectionService;
use App\Services\StartButton\ConversionService;
use App\Services\StartButton\DisputeService;
use App\Services\StartButton\TopupService;
use App\Services\StartButton\TransferService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WebHookController extends Controller
{
    public function __construct(
        private CollectionService $collectionService,
        private TransferService $transferService,
        private TopupService $topupService,
        private ConversionService $conversionService,
        private DisputeService $disputeService
    ) {
    }

    public function handleWebhook(Request $request)
    {
        Log::channel("slack")->info("StartButtonWebHookController called", [
            "Req" => $request->all()
        ]);

        $secret = env('STARTBUTTON_SECRET_KEY');
        $requestBody = $request->getContent();
        $signature = $request->header('x-startbutton-signature');

        if (!$this->isValidSignature($secret, $requestBody, $signature)) {
            Log::warning('Invalid StartButton webhook signature received.');
            return response()->json(['error' => 'Invalid signature'], Response::HTTP_FORBIDDEN);
        }

        $result = json_decode($requestBody);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($result->event)) {
            Log::error('Invalid JSON received from StartButton webhook.', ['body' => $requestBody]);
            return response()->json(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        Log::channel("slack")->info("StartButtonWebHookController Data is OK and received", [
            "Data" => $result
        ]);

        switch ($result->event) {
            case 'collection.verified':
            case 'collection.completed':
                $this->collectionService->handle($result->data);
                break;
            case 'transfer.pending':
            case 'transfer.successful':
            case 'transfer.failed':
            case 'transfer.reversed':
                $this->transferService->handle($result->data);
                break;
            case 'topup.successful':
                $this->topupService->handle($result->data);
                break;
            case 'conversion.successful':
            case 'conversion.pending':
            case 'conversion.failed':
                $this->conversionService->handle($result->data);
                break;
            case 'dispute.created':
            case 'dispute.processed':
            case 'dispute.declined':
                $this->disputeService->handle($result->data);
                break;
            default:
                Log::warning('Unhandled StartButton webhook event received.', ['event' => $result->event]);
                break;
        }

        return response()->json([], Response::HTTP_OK);
    }

    private function isValidSignature($secret, $data, $signature): bool
    {
        if (empty($secret) || empty($signature)) {
            return false;
        }
        $calculatedSignature = hash_hmac('sha512', $data, $secret);
        return hash_equals($calculatedSignature, $signature);
    }
}
