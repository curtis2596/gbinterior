<?php

namespace App\View\Components\Backend;

use App\View\Components\BackendComponent;

class SummaryDeleteButton extends BackendComponent
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        public String $url
    )
    {
        parent::__construct();
        
        $this->view_path = "summary-delete-button";
    }
}
