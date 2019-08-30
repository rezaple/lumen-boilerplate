<?php

namespace App\Transformers;

use App\Models\User;
use League\Fractal\TransformerAbstract;

class ExampleTransformer extends TransformerAbstract
{
    public function transform(User $user)
    {
        $result = [
            'id'                    => $user->uid,
            'username'              => $user->username,
            'email'                 => $user->email,
        ];

        return $result;
    }
}