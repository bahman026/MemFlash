<?php

declare(strict_types=1);

use App\Enums\UserStatusEnum;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lets only the admin into the admin panel, in any environment', function (): void {
    // Without the FilamentUser contract, Filament let everyone in when APP_ENV was
    // local and nobody -- the admin included -- anywhere else.
    config(['app.env' => 'production']);

    $admin = User::factory()->create(['email' => config('app.admin_email')]);
    $user = User::factory()->create();
    $panel = Filament::getPanel('admin');

    expect($admin->canAccessPanel($panel))->toBeTrue()
        ->and($user->canAccessPanel($panel))->toBeFalse();
});

it('keeps a blocked admin out of the admin panel', function (): void {
    $admin = User::factory()->create(['email' => config('app.admin_email'), 'status' => UserStatusEnum::BLOCK]);

    expect($admin->canAccessPanel(Filament::getPanel('admin')))->toBeFalse();
});

it('saves a block made in the admin user form', function (): void {
    // status was not fillable, so update($data) silently dropped it.
    $user = User::factory()->create();

    $user->update(['status' => UserStatusEnum::BLOCK]);

    expect($user->fresh()->status)->toBe(UserStatusEnum::BLOCK);
});

it('signs a blocked user out on their next request', function (): void {
    $user = User::factory()->create(['status' => UserStatusEnum::BLOCK]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('login.page'))
        ->assertSessionHas('error', 'Your account has been blocked.');

    $this->assertGuest();
});

it('starts a fresh session on logout', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->withSession(['kept' => 'from before']);

    $this->post(route('logout'))->assertRedirect(route('welcome'));

    $this->assertGuest();
    expect(session('kept'))->toBeNull();
});
