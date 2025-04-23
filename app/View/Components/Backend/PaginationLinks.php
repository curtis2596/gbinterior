<?php

namespace App\View\Components\Backend;

use App\View\Components\BackendComponent;

class PaginationLinks extends BackendComponent
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        public $records,
        public $withInfo = true
    )
    {
        parent::__construct();

        $this->view_path = "pagination-links";
    }
}
