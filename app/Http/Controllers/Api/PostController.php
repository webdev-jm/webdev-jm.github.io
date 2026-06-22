<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use Illuminate\Http\JsonResponse;

class PostController extends Controller
{
    public function store(StorePostRequest $request): JsonResponse
    {
        $post = $request->user()->posts()->create($request->validated());

        return response()->json($post, 201);
    }

    public function update(UpdatePostRequest $request, Post $post): JsonResponse
    {
        $original = $request->validated('original');
        $updated  = $request->validated('updated');
        $server   = $post->only(['title', 'body']);

        $mergeableFields = ['title', 'body'];
        $toApply         = [];
        $conflicts       = [];

        foreach ($mergeableFields as $field) {
            $clientChanged = array_key_exists($field, $updated)
                && $updated[$field] !== ($original[$field] ?? null);
            $serverChanged = $server[$field] !== ($original[$field] ?? null);

            if ($clientChanged && $serverChanged) {
                $conflicts[$field] = [
                    'original' => $original[$field] ?? null,
                    'client'   => $updated[$field],
                    'server'   => $server[$field],
                ];
            } elseif ($clientChanged) {
                $toApply[$field] = $updated[$field];
            }
        }

        if (!empty($conflicts)) {
            return response()->json([
                'message'   => 'Partial conflict: some fields could not be auto-merged.',
                'conflicts' => $conflicts,
                'merged'    => array_merge($server, $toApply),
            ], 409);
        }

        if (!empty($toApply)) {
            $post->update($toApply);
        }

        return response()->json($post->fresh());
    }
}
