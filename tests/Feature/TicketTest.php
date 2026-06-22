<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();

    Permission::create(['name' => 'ticket access', 'module' => 'Tickets', 'description' => '']);
    Permission::create(['name' => 'ticket responder', 'module' => 'Tickets', 'description' => '']);

    $userRole = Role::create(['name' => 'user']);
    $userRole->givePermissionTo('ticket access');

    $responderRole = Role::create(['name' => 'responder']);
    $responderRole->givePermissionTo(['ticket access', 'ticket responder']);

    $this->regularUser = User::factory()->create();
    $this->regularUser->assignRole('user');

    $this->responder = User::factory()->create();
    $this->responder->assignRole('responder');
});

// --- Access control ---

it('redirects guests from tickets index', function () {
    $this->get(route('tickets.index'))->assertRedirect(route('login'));
});

it('denies users without any ticket permission', function () {
    $noPermUser = User::factory()->create();
    $this->actingAs($noPermUser)->get(route('tickets.index'))->assertStatus(403);
});

it('allows users with ticket access to view the tickets index', function () {
    $this->actingAs($this->regularUser)->get(route('tickets.index'))->assertOk();
});

it('allows responders to view the tickets index', function () {
    $this->actingAs($this->responder)->get(route('tickets.index'))->assertOk();
});

// --- Submitting tickets ---

it('regular user can submit a ticket', function () {
    $this->actingAs($this->regularUser)
        ->post(route('tickets.store'), [
            'title' => 'Login page is broken',
            'description' => 'Cannot log in since yesterday.',
            'priority' => 'high',
            'category' => 'bug',
        ])
        ->assertRedirect();

    expect(Ticket::where('title', 'Login page is broken')->exists())->toBeTrue();
});

it('ticket store validates required fields', function () {
    $this->actingAs($this->regularUser)
        ->post(route('tickets.store'), [])
        ->assertSessionHasErrors(['title', 'description', 'priority', 'category']);
});

// --- Index scoping ---

it('regular user sees only their own tickets', function () {
    $own = Ticket::factory()->create(['user_id' => $this->regularUser->id]);
    $other = Ticket::factory()->create(['user_id' => $this->responder->id]);

    $this->actingAs($this->regularUser)
        ->get(route('tickets.index'))
        ->assertSee($own->title)
        ->assertDontSee($other->title);
});

it('responder sees all tickets', function () {
    $ticket1 = Ticket::factory()->create(['user_id' => $this->regularUser->id]);
    $ticket2 = Ticket::factory()->create(['user_id' => $this->responder->id]);

    $this->actingAs($this->responder)
        ->get(route('tickets.index'))
        ->assertSee($ticket1->title)
        ->assertSee($ticket2->title);
});

// --- Show / access gate ---

it('user can view their own ticket', function () {
    $ticket = Ticket::factory()->create(['user_id' => $this->regularUser->id]);

    $this->actingAs($this->regularUser)
        ->get(route('tickets.show', encrypt($ticket->id)))
        ->assertOk()
        ->assertSee($ticket->title);
});

it('user cannot view another users ticket', function () {
    $other = Ticket::factory()->create(['user_id' => $this->responder->id]);

    $this->actingAs($this->regularUser)
        ->get(route('tickets.show', encrypt($other->id)))
        ->assertStatus(403);
});

it('responder can view any ticket', function () {
    $ticket = Ticket::factory()->create(['user_id' => $this->regularUser->id]);

    $this->actingAs($this->responder)
        ->get(route('tickets.show', encrypt($ticket->id)))
        ->assertOk();
});

// --- Edit / Update (responder only) ---

it('regular user cannot access the edit page', function () {
    $ticket = Ticket::factory()->create(['user_id' => $this->regularUser->id]);

    $this->actingAs($this->regularUser)
        ->get(route('tickets.edit', encrypt($ticket->id)))
        ->assertStatus(403);
});

it('responder can update a ticket without changing status', function () {
    $ticket = Ticket::factory()->open()->create(['user_id' => $this->regularUser->id]);

    $this->actingAs($this->responder)
        ->put(route('tickets.update', encrypt($ticket->id)), [
            'title' => 'Updated title',
            'description' => $ticket->description,
            'priority' => $ticket->priority->value,
            'category' => $ticket->category->value,
            'assigned_to' => null,
        ])
        ->assertRedirect();

    expect($ticket->fresh()->title)->toBe('Updated title');
    expect($ticket->fresh()->status->value)->toBe('open');
});

// --- Status updates ---

it('responder can update ticket status to in_progress via status route', function () {
    $ticket = Ticket::factory()->open()->create(['user_id' => $this->regularUser->id]);

    $this->actingAs($this->responder)
        ->patch(route('tickets.status.update', encrypt($ticket->id)), ['status' => 'in_progress'])
        ->assertRedirect();

    expect($ticket->fresh()->status->value)->toBe('in_progress');
});

it('responder can update ticket status to resolved', function () {
    $ticket = Ticket::factory()->open()->create(['user_id' => $this->regularUser->id]);

    $this->actingAs($this->responder)
        ->patch(route('tickets.status.update', encrypt($ticket->id)), ['status' => 'resolved'])
        ->assertRedirect();

    expect($ticket->fresh()->status->value)->toBe('resolved');
});

it('regular user cannot update ticket status to open in_progress or resolved', function () {
    $ticket = Ticket::factory()->open()->create(['user_id' => $this->regularUser->id]);

    foreach (['in_progress', 'resolved'] as $status) {
        $this->actingAs($this->regularUser)
            ->patch(route('tickets.status.update', encrypt($ticket->id)), ['status' => $status])
            ->assertStatus(403);
    }
});

it('ticket creator can close their own ticket', function () {
    $ticket = Ticket::factory()->open()->create(['user_id' => $this->regularUser->id]);

    $this->actingAs($this->regularUser)
        ->patch(route('tickets.status.update', encrypt($ticket->id)), ['status' => 'closed'])
        ->assertRedirect();

    expect($ticket->fresh()->status->value)->toBe('closed');
});

it('regular user cannot close another users ticket', function () {
    $otherUser = User::factory()->create();
    $otherUser->givePermissionTo('ticket access');
    $ticket = Ticket::factory()->open()->create(['user_id' => $otherUser->id]);

    $this->actingAs($this->regularUser)
        ->patch(route('tickets.status.update', encrypt($ticket->id)), ['status' => 'closed'])
        ->assertStatus(403);
});

it('responder can close any ticket', function () {
    $ticket = Ticket::factory()->open()->create(['user_id' => $this->regularUser->id]);

    $this->actingAs($this->responder)
        ->patch(route('tickets.status.update', encrypt($ticket->id)), ['status' => 'closed'])
        ->assertRedirect();

    expect($ticket->fresh()->status->value)->toBe('closed');
});

it('status route rejects invalid status values', function () {
    $ticket = Ticket::factory()->open()->create(['user_id' => $this->regularUser->id]);

    $this->actingAs($this->responder)
        ->patch(route('tickets.status.update', encrypt($ticket->id)), ['status' => 'invalid'])
        ->assertSessionHasErrors('status');
});

// --- Comments ---

it('user can add a comment to their own open ticket', function () {
    $ticket = Ticket::factory()->open()->create(['user_id' => $this->regularUser->id]);

    $this->actingAs($this->regularUser)
        ->post(route('tickets.comment.store', encrypt($ticket->id)), ['body' => 'Still happening.'])
        ->assertRedirect();

    expect(TicketComment::where('ticket_id', $ticket->id)->where('body', 'Still happening.')->exists())->toBeTrue();
});

it('adding a comment to an open ticket sets status to in_progress', function () {
    $ticket = Ticket::factory()->open()->create(['user_id' => $this->regularUser->id]);

    $this->actingAs($this->regularUser)
        ->post(route('tickets.comment.store', encrypt($ticket->id)), ['body' => 'Update.'])
        ->assertRedirect();

    expect($ticket->fresh()->status->value)->toBe('in_progress');
});

it('adding a comment to an in_progress ticket does not change status', function () {
    $ticket = Ticket::factory()->create(['user_id' => $this->regularUser->id, 'status' => 'in_progress']);

    $this->actingAs($this->regularUser)
        ->post(route('tickets.comment.store', encrypt($ticket->id)), ['body' => 'Still working.'])
        ->assertRedirect();

    expect($ticket->fresh()->status->value)->toBe('in_progress');
});

it('user cannot comment on another users ticket', function () {
    $other = Ticket::factory()->open()->create(['user_id' => $this->responder->id]);

    $this->actingAs($this->regularUser)
        ->post(route('tickets.comment.store', encrypt($other->id)), ['body' => 'Hello'])
        ->assertStatus(403);
});

it('responder can delete any comment', function () {
    $ticket = Ticket::factory()->open()->create(['user_id' => $this->regularUser->id]);
    $comment = TicketComment::factory()->create(['ticket_id' => $ticket->id, 'user_id' => $this->regularUser->id]);

    $this->actingAs($this->responder)
        ->delete(route('tickets.comment.destroy', [encrypt($ticket->id), encrypt($comment->id)]))
        ->assertRedirect();

    expect(TicketComment::find($comment->id))->toBeNull();
});

it('user cannot delete another users comment', function () {
    $ticket = Ticket::factory()->open()->create(['user_id' => $this->regularUser->id]);
    $comment = TicketComment::factory()->create(['ticket_id' => $ticket->id, 'user_id' => $this->responder->id]);

    $this->actingAs($this->regularUser)
        ->delete(route('tickets.comment.destroy', [encrypt($ticket->id), encrypt($comment->id)]))
        ->assertStatus(403);
});

// --- Assignee update ---

it('responder can update ticket assignee', function () {
    $ticket = Ticket::factory()->open()->create(['user_id' => $this->regularUser->id]);

    $this->actingAs($this->responder)
        ->patch(route('tickets.assignee.update', encrypt($ticket->id)), ['assigned_to' => $this->responder->id])
        ->assertRedirect();

    expect($ticket->fresh()->assigned_to)->toBe($this->responder->id);
});

it('regular user cannot update ticket assignee', function () {
    $ticket = Ticket::factory()->open()->create(['user_id' => $this->regularUser->id]);

    $this->actingAs($this->regularUser)
        ->patch(route('tickets.assignee.update', encrypt($ticket->id)), ['assigned_to' => $this->responder->id])
        ->assertStatus(403);
});

// --- File attachments ---

it('ticket creator can attach a file', function () {
    Storage::fake('public');
    $ticket = Ticket::factory()->open()->create(['user_id' => $this->regularUser->id]);
    $file = UploadedFile::fake()->create('document.pdf', 100);

    $this->actingAs($this->regularUser)
        ->post(route('tickets.attachment.store', encrypt($ticket->id)), ['attachment' => $file])
        ->assertRedirect();

    expect(TicketAttachment::where('ticket_id', $ticket->id)->exists())->toBeTrue();
});

it('responder can attach a file to any ticket', function () {
    Storage::fake('public');
    $ticket = Ticket::factory()->open()->create(['user_id' => $this->regularUser->id]);
    $file = UploadedFile::fake()->create('screenshot.png', 200);

    $this->actingAs($this->responder)
        ->post(route('tickets.attachment.store', encrypt($ticket->id)), ['attachment' => $file])
        ->assertRedirect();

    expect(TicketAttachment::where('ticket_id', $ticket->id)->exists())->toBeTrue();
});

it('user cannot attach file to another users ticket', function () {
    Storage::fake('public');
    $ticket = Ticket::factory()->open()->create(['user_id' => $this->responder->id]);
    $file = UploadedFile::fake()->create('file.pdf', 100);

    $this->actingAs($this->regularUser)
        ->post(route('tickets.attachment.store', encrypt($ticket->id)), ['attachment' => $file])
        ->assertStatus(403);
});

it('attachment uploader can delete their own attachment', function () {
    Storage::fake('public');
    $ticket = Ticket::factory()->open()->create(['user_id' => $this->regularUser->id]);
    $file = UploadedFile::fake()->create('doc.pdf', 100);
    $path = $file->store('ticket-attachments', 'public');
    $attachment = TicketAttachment::factory()->create([
        'ticket_id' => $ticket->id,
        'user_id' => $this->regularUser->id,
        'path' => $path,
    ]);

    $this->actingAs($this->regularUser)
        ->delete(route('tickets.attachment.destroy', [encrypt($ticket->id), encrypt($attachment->id)]))
        ->assertRedirect();

    expect(TicketAttachment::find($attachment->id))->toBeNull();
});
