<?php

namespace App\View\Components\Inputs;

use Illuminate\View\Component;

class FileField extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        public String $name,
        public String $label,      
        public $mandatory = false,        
    )
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.inputs.file-field');
    }
}
