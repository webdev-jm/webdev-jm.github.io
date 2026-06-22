<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SyncBatchRequest;
use App\Models\SyncRequest;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    public function batch(SyncBatchRequest $request, Kernel $kernel): JsonResponse
    {
        $user = $request->user();
        $bearerToken = $request->bearerToken();
        $results = [];

        foreach ($request->validated('items') as $item) {
            $existing = SyncRequest::where('user_id', $user->id)
                ->where('client_id', $item['client_id'])
                ->where('status', 'applied')
                ->first();

            if ($existing) {
                $results[] = [
                    'client_id' => $item['client_id'],
                    'status' => 'applied',
                    'data' => $existing->server_response,
                ];

                continue;
            }

            $syncRecord = SyncRequest::firstOrCreate(
                ['user_id' => $user->id, 'client_id' => $item['client_id']],
                [
                    'method' => $item['method'],
                    'url' => $item['url'],
                    'payload' => $item['payload'],
                    'client_timestamp' => $item['client_timestamp'],
                    'status' => 'pending',
                ]
            );

            $result = $this->dispatchItem($item, $bearerToken, $kernel);

            $syncRecord->update([
                'status' => $result['status'],
                'server_response' => $result['data'] ?? null,
            ]);

            $results[] = array_merge(['client_id' => $item['client_id']], $result);
        }

        return response()->json(['results' => $results]);
    }

    public function status(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $counts = SyncRequest::where('user_id', $userId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return response()->json([
            'pending' => (int) ($counts['pending'] ?? 0),
            'applied' => (int) ($counts['applied'] ?? 0),
            'conflict' => (int) ($counts['conflict'] ?? 0),
            'rejected' => (int) ($counts['rejected'] ?? 0),
        ]);
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function dispatchItem(array $item, ?string $bearerToken, Kernel $kernel): array
    {
        try {
            $internalRequest = Request::create(
                $item['url'],
                $item['method'],
                $item['payload'],
                [],
                [],
                ['HTTP_ACCEPT' => 'application/json']
            );

            if ($bearerToken) {
                $internalRequest->headers->set('Authorization', 'Bearer '.$bearerToken);
            }

            $response = $kernel->handle($internalRequest);
            $statusCode = $response->getStatusCode();
            $responseData = json_decode($response->getContent(), true);

            if ($statusCode >= 200 && $statusCode < 300) {
                return ['status' => 'applied', 'data' => $responseData];
            }

            if ($statusCode === 409) {
                return ['status' => 'conflict', 'reason' => $responseData['message'] ?? 'Conflict'];
            }

            return ['status' => 'rejected', 'reason' => $responseData['message'] ?? "HTTP {$statusCode}"];
        } catch (\Throwable $e) {
            return ['status' => 'rejected', 'reason' => $e->getMessage()];
        }
    }
}
