<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\GuestCheckout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class IdentityController extends Controller
{
    private function user(User $u): array
    {
        return ['id' => $u->id, 'email' => $u->email, 'display_name' => $u->display_name];
    }

    public function csrf(Request $r)
    {
        return response()->json(['data' => ['csrf_token' => csrf_token()]]);
    }

    public function register(Request $r)
    {
        $r->merge(['email' => mb_strtolower(trim((string) $r->input('email')))]);
        $data = $r->validate(['email' => 'required|email|max:254|unique:users,email', 'display_name' => 'required|string|max:80', 'claim_order_id' => 'sometimes|uuid', 'password' => ['required', 'confirmed', Password::min(12), 'max:128']]);
        $user = DB::transaction(function () use ($r, $data) {
            $user = User::create(['email' => $data['email'], 'display_name' => $data['display_name'], 'password' => Hash::make($data['password'])]);
            if (isset($data['claim_order_id'])) {
                app(GuestCheckout::class)->claim($r, $data['claim_order_id'], $user->id, $user->email);
            }

return $user;
        }, 3);
        Auth::login($user);
        $r->session()->regenerate();
        $r->session()->put('auth_version', $user->auth_version ?? 1);

        return response()->json(['data' => $this->user($user)], 201);
    }

    public function login(Request $r)
    {
        $data = $r->validate(['email' => 'required|email', 'password' => 'required|string|max:128', 'claim_order_id' => 'sometimes|uuid']);
        $user = User::where('email', mb_strtolower(trim($data['email'])))->first();
        abort_unless($user && Hash::check($data['password'], $user->password), 422, 'Invalid email or password.');
        if (isset($data['claim_order_id'])) {
            app(GuestCheckout::class)->claim($r, $data['claim_order_id'], $user->id, $user->email);
        }
        Auth::login($user);
        $r->session()->regenerate();
        $r->session()->put('auth_version', $r->user()->auth_version);

        return response()->json(['data' => $this->user($r->user())]);
    }

    public function logout(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();

        return response()->noContent();
    }

    public function me(Request $r)
    {
        return response()->json(['data' => $this->user($r->user())]);
    }

    public function update(Request $r)
    {
        $data = $r->validate(['display_name' => 'required|string|max:80', 'email' => 'prohibited']);
        $r->user()->update($data);

        return $this->me($r);
    }

    public function password(Request $r)
    {
        $data = $r->validate(['current_password' => 'required|string', 'password' => ['required', 'confirmed', Password::min(12), 'max:128']]);
        DB::transaction(function () use ($r, $data) {
            $user = User::whereKey($r->user()->id)->lockForUpdate()->first();
            abort_unless(Hash::check($data['current_password'], $user->password), 422, 'Current password is incorrect.');
            $user->update(['password' => Hash::make($data['password']), 'auth_version' => $user->auth_version + 1]);
            DB::table('sessions')->where('user_id', $user->id)->delete();
        });

        return $this->logout($r);
    }
}
