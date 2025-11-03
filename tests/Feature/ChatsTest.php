<?php

use App\Enums\UserRole;
use App\Filament\App\Pages\Chats;
use App\Models\Supplier;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Livewire\SimpleUserMenu;
use Livewire\Livewire;
use Wirechat\Wirechat\Models\Conversation;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    // Create a test user
    $this->user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'role' => UserRole::Checker,
    ]);

    // Create a supplier and associate with user
    $this->supplier = User::factory()->supplier()->for(Supplier::factory(), 'supplier')->create(['is_active' => true]);

    Filament::setCurrentPanel('app');
});

it('can render chat page', function () {
    // Authenticate as the test user
    actingAs($this->user);

    livewire(Chats::class)
        ->assertOk();
});

it('requires authentication to access chat page', function () {
    // Test without authentication
    $response = $this->get(Chats::getUrl());
    $response->assertRedirect('/login');
});

it('can load wirechat component', function () {
    actingAs($this->user);

    // Test that the Livewire component can be loaded
    livewire('wirechat')
        ->assertSuccessful();
});

it('shows chat page in user menu', function () {
    actingAs($this->user);

    livewire(SimpleUserMenu::class)
        ->assertSee('Messages');
});

it('can access chat panel provider', function () {
    $this->actingAs($this->user);

    // Test that the chat panel is accessible
    $response = $this->get('/app-chats');
    $response->assertSuccessful();
});

// it('can search for users', function () {
//     $this->actingAs($this->user);

//     livewire('wirechat.new.chat')
//         ->assertSee($this->supplier->name);
// });

// it('cannot search for users with inactive suppliers', function () {
//     $this->actingAs($this->user);

//     // Deactivate the supplier
//     $this->otherSupplier->update(['is_active' => false]);

//     livewire
// // });

// it('can start a new chat', function () {
//     $this->actingAs($this->user);

//     $conversation = Conversation::create();

//     livewire('wirechat')
//         ->call('startConversation', $this->otherUser->id)
//         ->assertSuccessful();

//     // Verify conversation exists between users
//     $this->assertDatabaseHas('wirechat_participants', [
//         'user_id' => $this->user->id,
//         'conversation_id' => $conversation->id,
//     ]);

//     $this->assertDatabaseHas('wirechat_participants', [
//         'user_id' => $this->otherUser->id,
//         'conversation_id' => $conversation->id,
//     ]);
// });
