<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\FileUtility;
use App\Helpers\ImageUtility;
use App\Models\Company;
use App\Models\State;
use Exception;
use Illuminate\Http\Request;

class CompanyController extends BackendController
{
    
    public function __construct()
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function index() 
    {
        $company = Company::first();

        $state_list = State::getList('id','name');
        // dd($state_list);
        
        $this->setForView(compact("state_list", 'company'));

        return $this->view('edit');
    }

    // public function getPageTitle()
    // {
    //     return "Company";
    // }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Company $company)
    {
    
        $validatedData = $request->validate([
            'name' => 'required|max:150',
            'logo' => 'nullable|image|mimes:jpg,png,jpeg',
            'logo_for_pdf' => 'nullable|image|mimes:jpg,png,jpeg',
            'address' => 'required|max:200',
            'state_id' => 'nullable|integer',
            'city_id' => 'nullable|integer',
            'phone_number' => 'required|max:150',
            'email' => 'nullable|max:150',
            'website' => 'nullable|max:150',
            'gst_number' => 'nullable|max:150',
            'bank_name' => 'nullable|max:150',
            'account_name' => 'nullable|max:150',
            'ifsc_code' => 'nullable|max:150',
            'account_number' => 'nullable|max:150',
            'terms' => 'nullable'
        ]);
        
        try
        { 
            try
            {
                if (isset($_FILES['logo']['name']) && $_FILES['logo']['name'])
                {
                    $fileUtility = new FileUtility(1024 * 512, ["jpg", "png", "jpeg"]);

                    if ($fileUtility->uploadFile($_FILES['logo'], $company->getFilePath()))
                    {
                        $file = $fileUtility->path . $fileUtility->file;

                        $imgUtility = new ImageUtility($file, $file);

                        $imgUtility->correctOrientation();

                        list($width, $height) = getimagesize($file);
                        if (!$width || !$height)
                        {
                            throw new Exception("Invalid Image");
                        }

                        if ($width < 50)
                        {
                            throw new Exception("Width should be more than 50px");
                        }

                        if ($height < 30)
                        {
                            throw new Exception("Height should be more than 30px");
                        }

                        if ($width > 250 || $height > 100)
                        {
                            $imgUtility->resize(250, 100);
                        }

                        $validatedData['logo'] = $file;
                    }
                    else
                    {
                        throw new Exception(implode(", " , $fileUtility->errors));
                    }
                }
            }
            catch(Exception $ex)
            {
                throw new Exception("Logo : " . $ex->getMessage());
            }

            try
            {
                if (isset($_FILES['logo_for_pdf']['name']) && $_FILES['logo_for_pdf']['name'])
                {
                    $fileUtility = new FileUtility(1024 * 300, ["jpg", "png", "jpeg"]);

                    if ($fileUtility->uploadFile($_FILES['logo_for_pdf'], $company->getFilePath()))
                    {
                        $file = $fileUtility->path . $fileUtility->file;

                        $imgUtility = new ImageUtility($file, $file);

                        $imgUtility->correctOrientation();

                        list($width, $height) = getimagesize($file);
                        if (!$width || !$height)
                        {
                            throw new Exception("Invalid Image");
                        }

                        if ($width < 50)
                        {
                            throw new Exception("Width should be more than 50px");
                        }

                        if ($height < 20)
                        {
                            throw new Exception("Height should be more than 20px");
                        }

                        if ($width > 400 || $height > 150)
                        {
                            $imgUtility->resize(400, 150);
                        }

                        $validatedData['logo_for_pdf'] = $file;
                    }
                    else
                    {
                        throw new Exception(implode(", " , $fileUtility->errors));
                    }
                }
            }
            catch(Exception $ex)
            {
                throw new Exception("Logo For PDF And Print : " . $ex->getMessage());
            }

            // dd($validatedData);
            $company->update($validatedData);

            return  back()->with('success', 'Company Updated');
        }
        catch(Exception $ex)
        {
            // dd($ex->getMessage());
            return back()->withInput()->with("fail", $ex->getMessage());
        }
        
    }
}
