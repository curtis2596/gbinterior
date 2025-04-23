<?php

namespace App\View\Components\Backend;

use App\View\Components\BackendComponent;

class YesNoLabel extends BackendComponent
{
    public function __construct(
        public String $value
    )
    {
        parent::__construct();
        
        $this->view_path = "yes-no-label";
    }
}
