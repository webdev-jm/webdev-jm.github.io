<?php

namespace App\Http\Controllers;

use App\Enums\TicketStatus;
use App\Http\Requests\TicketCommentRequest;
use App\Http\Requests\TicketStoreRequest;
use App\Http\Requests\TicketUpdateRequest;
use App\Http\Traits\SettingTrait;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketComment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TicketController extends Controller
{
    use SettingTrait;

    public function index(): View
    {
        $query = Ticket::with(['user', 'assignee'])
            ->orderBy('created_at', 'DESC');

        if (! auth()->user()->can('ticket responder')) {
            $query->where('user_id', auth()->id());
        }

        $tickets = $query->paginate($this->getDataPerPage());

        return view('pages.tickets.index', compact('tickets'));
    }

    public function create(): View
    {
        return view('pages.tickets.create');
    }

    public function store(TicketStoreRequest $request): RedirectResponse
    {
        $ticket = Ticket::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
            'category' => $request->category,
        ]);

        activity('created')
            ->performedOn($ticket)
            ->log(':causer.name submitted ticket ['.$ticket->title.']');

        return redirect()->route('tickets.show', encrypt($ticket->id))
            ->with('message_success', 'Ticket submitted successfully.');
    }

    public function show(string $id): View
    {
        $ticket = Ticket::with(['user', 'assignee', 'comments.user', 'attachments.user'])
            ->findOrFail(decrypt($id));

        abort_unless(
            auth()->id() === $ticket->user_id || auth()->user()->can('ticket responder'),
            403
        );

        $responders = auth()->user()->can('ticket responder')
            ? User::select('id', 'name')->orderBy('name')->get()
            : collect();

        return view('pages.tickets.show', compact('ticket', 'responders'));
    }

    public function edit(string $id): View
    {
        $ticket = Ticket::findOrFail(decrypt($id));

        $responders = User::select('id', 'name')->orderBy('name')->get();

        return view('pages.tickets.edit', compact('ticket', 'responders'));
    }

    public function update(TicketUpdateRequest $request, string $id): RedirectResponse
    {
        $ticket = Ticket::findOrFail(decrypt($id));

        $ticket->update($request->validated());

        activity('updated')
            ->performedOn($ticket)
            ->log(':causer.name updated ticket ['.$ticket->title.']');

        return redirect()->route('tickets.show', encrypt($ticket->id))
            ->with('message_success', 'Ticket updated successfully.');
    }

    public function storeComment(TicketCommentRequest $request, string $id): RedirectResponse
    {
        $ticket = Ticket::findOrFail(decrypt($id));

        abort_unless(
            auth()->id() === $ticket->user_id || auth()->user()->can('ticket responder'),
            403
        );

        TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'body' => $request->body,
        ]);

        if ($ticket->status->value === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        return redirect()->route('tickets.show', encrypt($ticket->id))
            ->with('message_success', 'Comment added.');
    }

    public function updateStatus(Request $request, string $id): RedirectResponse
    {
        $ticket = Ticket::findOrFail(decrypt($id));

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', array_column(TicketStatus::cases(), 'value'))],
        ]);

        $isResponder = auth()->user()->can('ticket responder');
        $isCreator = auth()->id() === $ticket->user_id;

        if ($validated['status'] === 'closed') {
            abort_unless($isCreator || $isResponder, 403);
        } else {
            abort_unless($isResponder, 403);
        }

        $oldStatus = $ticket->status->label();
        $ticket->update(['status' => $validated['status']]);
        $newStatus = $ticket->fresh()->status->label();

        activity('updated')
            ->performedOn($ticket)
            ->log(':causer.name changed ticket ['.$ticket->title.'] status from '.$oldStatus.' to '.$newStatus);

        return redirect()->route('tickets.show', encrypt($ticket->id))
            ->with('message_success', 'Ticket status updated to '.$newStatus.'.');
    }

    public function updateAssignee(Request $request, string $id): RedirectResponse
    {
        $ticket = Ticket::findOrFail(decrypt($id));

        abort_unless(auth()->user()->can('ticket responder'), 403);

        $validated = $request->validate([
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        $ticket->update(['assigned_to' => $validated['assigned_to'] ?: null]);

        activity('updated')
            ->performedOn($ticket)
            ->log(':causer.name updated assignee on ticket ['.$ticket->title.']');

        return redirect()->route('tickets.show', encrypt($ticket->id))
            ->with('message_success', 'Assignee updated.');
    }

    public function storeAttachment(Request $request, string $id): RedirectResponse
    {
        $ticket = Ticket::findOrFail(decrypt($id));

        abort_unless(
            auth()->id() === $ticket->user_id || auth()->user()->can('ticket responder'),
            403
        );

        $request->validate([
            'attachment' => ['required', 'file', 'max:10240'],
        ]);

        $file = $request->file('attachment');
        $path = $file->store('ticket-attachments', 'public');

        TicketAttachment::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);

        activity('updated')
            ->performedOn($ticket)
            ->log(':causer.name attached a file to ticket ['.$ticket->title.']');

        return redirect()->route('tickets.show', encrypt($ticket->id))
            ->with('message_success', 'File attached successfully.');
    }

    public function destroyAttachment(string $id, string $attachmentId): RedirectResponse
    {
        $ticket = Ticket::findOrFail(decrypt($id));
        $attachment = TicketAttachment::findOrFail(decrypt($attachmentId));

        abort_unless(
            auth()->id() === $attachment->user_id || auth()->user()->can('ticket responder'),
            403
        );

        Storage::disk('public')->delete($attachment->path);
        $attachment->delete();

        return redirect()->route('tickets.show', encrypt($ticket->id))
            ->with('message_success', 'Attachment deleted.');
    }

    public function destroyComment(string $id, string $commentId): RedirectResponse
    {
        $ticket = Ticket::findOrFail(decrypt($id));
        $comment = TicketComment::findOrFail(decrypt($commentId));

        abort_unless(
            auth()->id() === $comment->user_id || auth()->user()->can('ticket responder'),
            403
        );

        $comment->delete();

        return redirect()->route('tickets.show', encrypt($ticket->id))
            ->with('message_success', 'Comment deleted.');
    }
}
