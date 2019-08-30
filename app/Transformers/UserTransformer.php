<?php

namespace App\Transformers;

use App\Models\User;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    public function transform(User $user)
    {
        $formattedUser = [
            'id'                    => $user->uid,
            'email'                 => $user->email,
            'image'                 => set_image($user->profile_image),
            'createdAt'             => (string) $user->created_at
        ];

        return $formattedUser;
    }
}