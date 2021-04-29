<?php

namespace SturentsTest\Validation\Rules;

use SturentsTest\Models\User;
use Respect\Validation\Rules\AbstractRule;

class EmailAvailable extends AbstractRule
{
    
    public function validate($input)
    {
        return ! User::where('email', $input)->exists();
    }
}
