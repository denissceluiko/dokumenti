<?php

namespace App\Rules;

use App\Models\Document\Template;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\UploadedFile;

class TemplateUnique implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  UploadedFile  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $hash = sha1_file($value->getRealPath());
        return !Template::hash($hash)->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('The validation error message.');
    }
}
