<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;

use App\Http\Requests\CompanyAddRequest;
use App\Http\Requests\CompanyEditRequest;

use App\Http\Traits\SettingTrait;

class CompanyController extends Controller
{
    use SettingTrait;

    public function index(Request $request) {

        $search = trim($request->get('search'));
        
        $companies = Company::orderBy('created_at', 'DESC')
            ->when(!empty($search), function($query) use($search) {
                $query->where('name', 'like', '%'.$search.'%');
            })
            ->paginate($this->getDataPerPage())
            ->appends(request()->query());

        return view('pages.companies.index')->with([
            'search' => $search,
            'companies' => $companies
        ]);
    }

    public function create() {
        return view('pages.companies.create');
    }

    public function store(CompanyAddRequest $request) {

        $company = new Company([
            'name' => $request->name
        ]);
        $company->save();

        // logs
        activity('created')
            ->performedOn($company)
            ->log(':causer.name has created company :subject.name');

        return redirect()->route('company.index')->with([
            'message_success' => __('adminlte::companies.company_create_success')
        ]);
    }

    public function show($id) {
        $company = Company::findOrFail(decrypt($id));

        return view('pages.companies.show')->with([
            'company' => $company
        ]);
    }

    public function edit($id) {
        $company = Company::findOrFail(decrypt($id));

        return view('pages.companies.edit')->with([
            'company' => $company
        ]);
    }

    public function update(CompanyEditRequest $request, $id) {
        $company = Company::findOrFail(decrypt($id));

        $changes_arr['old'] = $company->getOriginal();

        $company->update([
            'name' => $request->name
        ]);
        $company->save();

        $changes_arr['changes'] = $company->getChanges();

        // logs
        activity('updated')
            ->performedOn($company)
            ->withProperties($changes_arr)
            ->log(':causer.name has updated company :subject.name');

        return back()->with([
            'message_success' => __('adminlte::companies.company_update_success')
        ]);
    }
}
