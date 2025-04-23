<?php

namespace App\View\Components\Backend;

use App\View\Components\BackendComponent;

class ActiveDeactive extends BackendComponent
{
    public function __construct(
        public $isActive,
        public $routePrefix,
        public $id,
    )
    {
        parent::__construct();
        
        $this->view_path = "active_deactive";
    }
}
