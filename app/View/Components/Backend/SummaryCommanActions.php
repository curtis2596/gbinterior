<?php

namespace App\View\Components\Backend;

use App\View\Components\BackendComponent;

class SummaryCommanActions extends BackendComponent
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        public $id,
        public $routePrefix,
        public $buttons = ""
    )
    {
        parent::__construct();

        if (!$this->buttons)
        {
            $buttonList = ["edit", "delete"];
        }
        else
        {
            if (is_string($this->buttons))
            {
                $buttonList = explode(",", $this->buttons);
            }
            else if (is_array($this->buttons))
            {
                $buttonList = $this->buttons;
            }
        }

        $this->setForView(compact("buttonList"));
        
        $this->view_path = "summary-comman-actions";
    }
}
