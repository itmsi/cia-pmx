<?php

namespace App\Validation;

class BoardRules
{
    // public function custom_rule(): bool
    // {
    //     return true;
    // }
    public function rules(): array
    {
        return [
            'name' => [
                'rules'  => 'required|min_length[3]|max_length[100]',
                'errors' => [
                    'required'   => 'Board name is required',
                    'min_length' => 'Board name must be at least 3 characters'
                ]
            ]
        ];
    }
}
