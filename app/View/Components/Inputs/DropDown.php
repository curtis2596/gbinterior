<?php

namespace App\View\Components\Inputs;

use Illuminate\View\Component;

class DropDown extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        public String $name,
        public String $label,        
        public $value = "",
        public array $list = [],
        public $mandatory = false,  
        public $is_disabled = false,  
        public $pleaseSelect = true,
        public $errorName = ""
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
        return view('components.inputs.drop-down');
    }
}
