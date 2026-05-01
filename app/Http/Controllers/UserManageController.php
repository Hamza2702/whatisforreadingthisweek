<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserManageController extends Controller
{

    // only authenticated user
    private function authorizeUser(User $user): void
    {
        if (Auth::id() !== $user->id) {
            abort(403, 'You are not authorised to manage this profile.');
        }
    }

    // manage page
    public function edit(User $user)
    {
        $this->authorizeUser($user);

        return view('user.manage', compact('user'));
    }


    // Update field
    public function updateField(Request $request, User $user)
    {
        $this->authorizeUser($user);

        $field = $request->input('field');

        // editable fields
        $allowed = ['email', 'password', 'phone'];

        if (!in_array($field, $allowed, true)) {
            return response()->json([
                'success' => false,
                'message' => 'This field cannot be edited.',
            ], 422);
        }

        try {
            switch ($field) {
                // EMAIL
                case 'email':
                    $data = $request->validate([
                        'value' => [
                            'required',
                            'email',
                            'max:255',
                            Rule::unique('users', 'email')->ignore($user->id),
                        ],
                    ]);
                    $user->email = $data['value'];
                    $user->email_verified_at = null;
                    $user->save();

                    return response()->json([
                        'success' => true,
                        'message' => 'Email updated successfully.',
                        'value'   => $user->email,
                    ]);

                // PHONE
                case 'phone':
                    $data = $request->validate([
                        'value' => [
                            'required',
                            'string',
                            'max:20',
                            // spaces, dashes, (), 07xxx, +447xxx, 01xxx, 02xxx, 03xxx
                            'regex:/^(?:(?:\+44\s?|0)(?:1\d{8,9}|2\d{9}|3\d{9}|7\d{9}))$/',
                        ],
                    ], [
                        'value.regex' => 'Please enter a valid UK phone number (e.g. 07123 456789 or +44 7123 456789).',
                    ]);

                    $normalised = preg_replace('/[\s\-\(\)]/', '', $data['value']);

                    $user->phone = $normalised;
                    $user->save();

                    return response()->json([
                        'success' => true,
                        'message' => 'Phone number updated successfully.',
                        'value'   => $user->phone,
                    ]);

                // PASSWORD
                case 'password':
                    $request->validate([
                        'value' => [
                            'required',
                            'confirmed',
                            Password::min(8)
                                ->mixedCase()
                                ->numbers()
                                ->symbols(),
                        ],
                    ]);
                    $user->password = Hash::make($request->input('value'));
                    $user->save();

                    return response()->json([
                        'success' => true,
                        'message' => 'Password updated successfully.',
                    ]);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors'  => $e->errors(),
            ], 422);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unable to update.',
        ], 400);
    }
}