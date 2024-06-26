<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TicketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tickets = Ticket::query()
            ->with('subject')
            ->where('user_id', $request->user()->id)
            ->get();

        return response()->json($tickets);
    }

    public function show(int $id): JsonResponse
    {
        $ticket = Ticket::query()
            ->with(['subject', 'comments'])
            ->where('user_id', auth()->user()->id)
            ->orderBy('comments.id')
            ->findOrFail($id);

        return response()->json($ticket);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'ticket_subject_id' => 'required|exists:ticket_subjects,id',
            'description' => 'required|string',
            'file' => 'nullable|file',
        ]);

        $ticket = Ticket::create([
            'user_id' => $request->user()->id,
            'title' => $request->input('title'),
            'ticket_subject_id' => $request->input('ticket_subject_id'),
            'description' => $request->input('description'),
            'status' => Ticket::STATUS_OPEN,
        ]);

        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('tickets', 'public');
            $filePath = "storage/tickets/" . basename($filePath);
            $ticket->update(['file' => $filePath]);
        }

        return response()->json($ticket, 201);
    }

    public function adminRespond(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'comment' => 'required|string',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        /** @var Ticket $ticket */
        $ticket = Ticket::findOrFail($id);
        $ticket->comments()->create([
            'user_id' => null,
            'comment' => $request->input('comment'),
            'parent_id' => $request->input('parent_id'),
        ]);
        $ticket->update(['status' => Ticket::STATUS_RESPONDED]);

        return response()->json($ticket);
    }

    public function userRespond(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'comment' => 'required|string',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        /** @var Ticket $ticket */
        $ticket = Ticket::findOrFail($id);
        $ticket->comments()->create([
            'user_id' => $request->user()->id,
            'comment' => $request->input('comment'),
            'parent_id' => $request->input('parent_id'),
        ]);
        $ticket->update(['status' => Ticket::STATUS_PENDING]);

        return response()->json($ticket);
    }

    public function adminIndex(Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'nullable|in:open,responded,pending,closed',
        ]);

        $tickets = Ticket::query()->with('subject');

        $status = $request->input('status', '');
        if ($status) {
            $tickets = $tickets->where('status', $status);
        }

        $tickets = $tickets->get();
        return response()->json($tickets);
    }
}
