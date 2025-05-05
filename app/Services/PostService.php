<?php
namespace App\Services;

use App\Models\Post;
use Illuminate\Pagination\LengthAwarePaginator;

class PostService
{
    /**
     * List posts with optional filters
     *
     * @param  array  $filters
     * @return LengthAwarePaginator
     */
    public function listPosts(array $filters = [])
    {
        $query = Post::query();

        if (!empty($filters['published_only'])) {
            $query->where('is_published', true);
        }

        return $query->paginate(10);
    }

    /**
     * Create a new post
     * @param array $data
     * @return Post
     */
    public function createPost(array $data): Post
    {
        return Post::create($data);
    }

    /**
     * Update an existing post
     * @param \App\Models\Post $post
     * @param array $data
     * @return Post
     */
    public function updatePost(Post $post, array $data): Post
    {
        $post->update($data);
        return $post;
    }

    /**
     * Delete a post SoftDelete
     * @param \App\Models\Post $post
     * @return void
     */
    public function deletePost(Post $post): void
    {
        $post->delete();
    }

    /**
     * Restore a post
     * @param int $id
     * @return mixed|\Illuminate\Database\Eloquent\Builder<Post>|null
     */
    public function restore(int $id): ?Post
    {
        $post = Post::withTrashed()->find($id);
        if ($post && $post->trashed()) {
            $post->restore();
            return $post;
        }
        return null;
    }

    /**
     * Fetch a soft-deleted post by ID.
     * @param int $id
     * @return mixed|\Illuminate\Database\Eloquent\Builder<Post>|null
     */
    public function getTrashedPost(int $id): ?Post
    {
        return Post::withTrashed()->find($id);
    }

    /**
     * Permanently delete a soft-deleted post.
     *
     * @param  int  $id
     * @return bool
     */
    public function forceDelete(int $id): bool
    {
        $post = $this->getTrashedPost($id);
        return $post ? $post->forceDelete() : false;
    }
}
