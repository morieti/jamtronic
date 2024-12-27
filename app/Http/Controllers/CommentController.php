<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CommentController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $searchQuery = $request->input('search', '');
        $perPage = (int)$request->input('size', 20);
        $page = (int)$request->input('page', 1);

        $filters = $request->except(['search', 'size', 'page'], []);
        $filters['commentable_type'] = Product::class;
        $filterQuery = $this->arrangeFilters($filters);

        $comments = Comment::search($searchQuery)
            ->when($filterQuery, function ($search, $filterQuery) {
                $search->options['filter'] = $filterQuery;
                $search->raw($filterQuery);
            })
            ->paginate($perPage, 'page', $page);

        $comments = $comments->jsonSerialize();
        unset($comments['data']['totalHits']);

        return response()->json($comments);
    }

    public function index(Request $request, int $productId): JsonResponse
    {
        $comments = Comment::query()
            ->with(['user', 'replies', 'replies.user', 'commentable.title', 'commentable.images'])
            ->where('approved', '=', true)
            ->where('commentable_type', Product::class)
            ->where('commentable_id', $productId)
            ->whereNull('parent_id')
            ->whereRelation('replies', 'approved', '=', true)
            ->get();

        return response()->json($comments);
    }

    public function userIndex(): JsonResponse
    {
        $user = auth()->user();
        $comments = Comment::query()
            ->with(['user', 'replies', 'replies.user', 'commentable.title', 'commentable.images'])
            ->where('user_id', $user->id)
            ->where('commentable_type', Product::class)
            ->whereNull('parent_id')
            ->get();

        return response()->json($comments);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'comment' => 'required|string',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $comment = Comment::create([
            'commentable_id' => $request->input('product_id'),
            'commentable_type' => Product::class,
            'user_id' => $request->user()->id,
            'comment' => $request->input('comment'),
            'parent_id' => $request->input('parent_id'),
        ]);

        return response()->json($comment, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'comment' => 'required|string',
            'approved' => 'required|boolean',
        ]);

        $comment = Comment::query()->findOrFail($id);

        $admin = auth()->guard('admin')->user();
        $user = auth()->user();

        $data = $request->all();
        if (!$admin) {
            if (!$user || $comment->user_id != $user->id) {
                return response()->json(['Not Authorized'], Response::HTTP_FORBIDDEN);
            }
            unset($data['approved']);
        }

        $comment->update($data);
        return response()->json($comment);
    }

    public function destroy(int $id): JsonResponse
    {
        $comment = Comment::findOrFail($id);

        $admin = auth()->guard('admin')->user();
        $user = auth()->user();

        if (!$admin) {
            if (!$user || $comment->user_id != $user->id) {
                return response()->json(['Not Authorized'], Response::HTTP_FORBIDDEN);
            }
        }

        $comment->delete();
        return response()->json([], 204);
    }
}
