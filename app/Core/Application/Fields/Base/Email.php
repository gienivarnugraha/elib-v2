<?php

namespace App\Core\Application\Fields\Base;

class Email extends Text
{
    /**
     * Input type
     */
    public string $inputType = 'email';

    /**
     * Boot the field
     *
     * @return void
     */
    public function boot()
    {
        $this->rules(['email', 'nullable'])
            // ->tapIndexColumn(function ($column) {
            //     $column->useComponent('table-data-email');
            // })
            ->provideSampleValueUsing(fn () => uniqid().'@example.com')
            ->icon('bx-envelope', 'prepend-inner');
    }
}
