<?php

use App\Models\ServiceRequest;
use App\Models\User;

test('a user who can see a request can comment on it', function () {
    $request = ServiceRequest::factory()->create();

    $this->actingAs($request->requestor)
        ->post("/requests/{$request->id}/comments", ['body' => 'Any update?'])
        ->assertRedirect("/requests/{$request->id}");

    $comment = $request->comments()->first();
    expect($comment->comment)->toBe('Any update?');
    expect($comment->author_id)->toBe($request->requestor_id);
    expect($comment->is_resolution)->toBeFalse();
});

test('a user who cannot see a request cannot comment on it', function () {
    $request = ServiceRequest::factory()->create();
    $stranger = User::factory()->create();

    $this->actingAs($stranger)
        ->post("/requests/{$request->id}/comments", ['body' => 'Not mine'])
        ->assertForbidden();

    expect($request->comments()->count())->toBe(0);
});

test('an empty comment is rejected', function () {
    $request = ServiceRequest::factory()->create();

    $this->actingAs($request->requestor)
        ->post("/requests/{$request->id}/comments", ['body' => ''])
        ->assertSessionHasErrors(['body' => 'Enter a comment.']);
});