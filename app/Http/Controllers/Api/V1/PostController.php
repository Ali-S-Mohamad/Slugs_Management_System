<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;

class PostController extends Controller
{
    protected PostService $service;

    public function __construct(PostService $service)
    {
        $this->service = $service;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $publishedOnly = request()->boolean('published_only', false);

        $posts = $this->service->listPosts([
            'published_only' => $publishedOnly,
        ]);

        return response()->json($posts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request)
    {
        // dd($request->validated());
        $post = $this->service->createPost($request->validated());
        return response()->json(['status' => 'success', 'data' => $post], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return response()->json($post);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
        // dd($request->validated());
        $updated = $this->service->updatePost($post, $request->validated());
        return response()->json(['status' => 'success', 'data' => $updated]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post): JsonResponse
    {
        // Perform soft delete
        $this->service->deletePost($post);

        // Return the soft-deleted post as confirmation
        return $this->success(
            $post,
            'Post deleted successfully.',
            200
        );
    }

    /**
     * Restore a soft-deleted post.
     * @param int $id
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        // Attempt to restore via service; returns Post|null
        $post = $this->service->restore($id);

        if ($post) {
            return $this->success(
                $post,
                'Post restored successfully.',
                200
            );
        }

        return $this->error(
            'Post not found or not trashed.',
            [],   // no validation errors
            404
        );
    }

    /**
     * Permanently delete a post.
     *
     * We first fetch the trashed post, then force-delete it,
     * and return the pre-deletion model in the response.
     * @param int $id
     * @return JsonResponse
     */
    public function forceDelete(int $id): JsonResponse
    {
        // Fetch the trashed post
        $post = $this->service->getTrashedPost($id);

        if (! $post) {
            return $this->error(
                'Post not found Or it is not trashed yet.',
                [],
                404
            );
        }

        // Permanently delete
        $this->service->forceDelete($id);

        return $this->success(
            $post,
            'Post permanently deleted.',
            200
        );
    }

    /**
     * GET /posts/trashed
     * List all soft-deleted posts.
     * @return JsonResponse
     */
    public function trashed(): JsonResponse
    {
        $trashed = $this->service->listTrashed();
        if(count($trashed)> 0){
            return $this->success($trashed, 'Trashed posts retrieved.', 200);
        }
        return $this->error(
            'No trashed posts.',
            [],
            404
        );
    }

    /**
     * POST /posts/restore-all
     * Restore *all* soft-deleted posts.
     * @return JsonResponse
     */
    public function restoreAll(): JsonResponse
    {
        $count = $this->service->restoreAll();

        if ($count > 0) {
            return $this->success(
                ['restored_count' => $count],
                'All trashed posts have been restored.',
                200
            );
        }

        return $this->error(
            'No trashed posts to restore.',
            [],
            404
        );
    }
}

