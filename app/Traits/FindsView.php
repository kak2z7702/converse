<?php

namespace App\Traits;

trait FindsView
{
    /**
     * Finds view path in current theme.
     * 
     * @param $name View name.
     * @return string
     */
    public function findView($name)
    {
        return config('app.theme', 'light') . '.' . $name;
    }
}