<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomField;
use App\Http\Requests\StoreCustomFieldRequest;
use App\Http\Requests\UpdateCustomFieldRequest;
use Illuminate\Http\Request;

class CustomFieldController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customFields = CustomField::orderBy("label")->paginate(15);
        return view("admin.custom_fields.index", compact("customFields"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view("admin.custom_fields.create");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomFieldRequest $request)
    {
        CustomField::create($request->validated());

        return redirect()->route("admin.custom-fields.index")
            ->with("success", "Custom field created successfully.");
    }

    /**
     * Display the specified resource.
     * (Optional: Usually not needed for admin management, index/edit is sufficient)
     */
    public function show(CustomField $customField)
    {
        // return view("admin.custom_fields.show", compact("customField"));
        return redirect()->route("admin.custom-fields.edit", $customField);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CustomField $customField)
    {
        return view("admin.custom_fields.edit", compact("customField"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomFieldRequest $request, CustomField $customField)
    {
        // Basic check: Prevent changing type if values exist? (Could add this logic)
        // if ($customField->values()->exists() && $customField->type !== $request->validated()["type"]) {
        //     return back()->withErrors(["type" => "Cannot change the type of a custom field that already has values."])->withInput();
        // }

        $customField->update($request->validated());

        return redirect()->route("admin.custom-fields.index")
            ->with("success", "Custom field updated successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CustomField $customField)
    {
        // Add check: Prevent deletion if values exist?
        if ($customField->values()->exists()) {
            return redirect()->route("admin.custom-fields.index")
                ->with("error", "Cannot delete custom field because it has associated values.");
        }

        try {
            $customField->delete();
            return redirect()->route("admin.custom-fields.index")
                ->with("success", "Custom field deleted successfully.");
        } catch (\Exception $e) {
            // Log error
            return redirect()->route("admin.custom-fields.index")
                ->with("error", "Failed to delete custom field. Error: " . $e->getMessage());
        }
    }
}
