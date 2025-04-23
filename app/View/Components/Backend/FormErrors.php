<?php

namespace App\View\Components\Backend;

use App\View\Components\BackendComponent;
use Illuminate\View\Component;

class FormErrors extends BackendComponent
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->view_path = 'form-errors';
    }
}
