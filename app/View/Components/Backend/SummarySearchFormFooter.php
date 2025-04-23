<?php

namespace App\View\Components\backend;

use App\View\Components\BackendComponent;

class SummarySearchFormFooter extends BackendComponent
{
    public function __construct(
        public $selectedPaginationLimit,
        public $paginationListOptions = null,
    )
    {
        parent::__construct();

        $paginationList = [
            10 => 10,
            20 => 20,
            50 => 50,
            100 => 100,
        ];

        if ($this->paginationListOptions)
        {
            if (is_string($this->paginationListOptions))
            {
                $paginationList = explode(",", $this->paginationListOptions);
                $paginationList = array_combine($paginationList, $paginationList);
            }
            else if (is_array($this->paginationListOptions))
            {
                $paginationList = $this->paginationListOptions;
            }
        }

        $this->setForView(compact("paginationList"));
        
        $this->view_path = "summary-search-form-footer";
    }
}
