<?php

namespace App\Services\Access;

use App\Models\User;
use App\Notifications\UserCreatedNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Throwable;

class UserInvitationService
{
    public function send(User $user, User $actor): bool
    {
        $token = Password::broker()->createToken($user);

        try {
            $user->notify(new UserCreatedNotification($token, $actor->name));

            return true;
        } catch (Throwable $exception) {
            Log::error('User invitation email could not be sent.', [
                'user_id' => $user->id,
                'actor_id' => $actor->id,
                'exception' => $exception,
            ]);

            return false;
        }
    }
}
