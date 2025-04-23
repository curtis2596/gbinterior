<?php

namespace App\View\Components\Backend;

use App\View\Components\BackendComponent;

class IndexTableInfo extends BackendComponent
{
    public function __construct(
        public $record,
        public $userList
    )
    {
        parent::__construct();
        
        $this->view_path = "index_table_info";
    }
}
