<?php

namespace App\View\Components\backend;

use App\View\Components\BackendComponent;
use Illuminate\View\Component;

class FormCommonFooterButtons extends BackendComponent
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        public $buttons = null
    )
    {
        parent::__construct();

        $buttonList = ["submit", "submit_and_redirect_to_summary", "reset"];

        if ($buttons)
        {
            if (is_string($buttons))
            {
                $buttonList = explode(",", $buttons);
            }
            else if (is_array($buttons))
            {
                $buttonList = $buttons;
            }
        }

        $this->setForView(compact("buttonList"));

        $this->view_path = "form-common-footer-buttons";
    }
}
