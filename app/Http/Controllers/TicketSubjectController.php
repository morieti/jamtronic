<?php

namespace App\Http\Controllers;

use App\Models\TicketSubject;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TicketSubjectController extends Controller
{
    public function index(): JsonResponse
    {
        $subjects = TicketSubject::query()->orderBy('sort')->get();
        return response()->json($subjects);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'sort' => 'nullable|integer',
        ]);

        $subject = TicketSubject::create($request->all());
        return response()->json($subject, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'sort' => 'nullable|integer',
        ]);

        $subject = TicketSubject::findOrFail($id);
        $subject->update($request->all());

        return response()->json($subject);
    }

    public function destroy(int $id): JsonResponse
    {
        /** @var TicketSubject $subject */
        $subject = TicketSubject::findOrFail($id);
        $subject->delete();

        return response()->json(null, 204);

    }
}
