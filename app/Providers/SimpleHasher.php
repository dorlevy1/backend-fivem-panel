<?php

namespace App\Providers;

use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Hashing\AbstractHasher;

class SimpleHasher extends AbstractHasher implements Hasher
{

    public function make($value, array $options = [])
    {
        $salt = substr(uniqid(rand()), -6);

        return md5(md5($value) . $salt);
    }

    public function check($value, $hashedValue, array $options = [])
    {
        return md5(md5($value) . $options['salt'] ?? '') === $hashedValue;
    }

    public function needsRehash($hashedValue, array $options = [])
    {
        // Your needsRehash implementation here
    }
}