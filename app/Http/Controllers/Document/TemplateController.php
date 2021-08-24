<?php

namespace App\Http\Controllers\Document;

use App\Exports\TemplateExport;
use App\Http\Controllers\Controller;
use App\Models\Document\Template;
use App\Rules\TemplateUnique;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class TemplateController extends Controller
{
    public function index()
    {
        $templates = Template::all();
        return view('document.template.index', compact('templates'));
    }

    public function create()
    {
        return view('document.template.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:templates,name',
            'naming' => 'required',
            'template_file' => ['required', 'file', new TemplateUnique],
        ]);

        $template = Template::createFromUpload($request);

        return redirect()->route('template.show', $template);
    }

    public function edit(Template $template)
    {
        return view('document.template.edit', compact('template'));
    }

    public function update(Template $template, Request $request)
    {
        $this->validate($request, [
            'name' => "required|unique:templates,name,{$template->id}",
            'naming' => 'required',
            'template_file' => ['nullable', 'file', new TemplateUnique],
        ]);

        $template->updateFromUpload($request);

        return redirect()->route('template.show', $template);
    }

    public function show(Template $template)
    {
        $template->load('documents');
        return view('document.template.show', compact('template'));
    }

    public function compile(Template $template, Request $request)
    {
        $this->validate($request, []);

        $document = $template->compile($request->all());
        return redirect()->route('document.show', $document);
    }

    public function batch(Template $template, Request $request)
    {
        $this->validate($request, [
            'template_batch_import_file' => 'required|file',
        ]);

        $template->batch($request->file('template_batch_import_file'));
        return redirect()->route('document.index');
    }

    public function resolve(Template $template)
    {
        $template->resolveAgain();
        return back();
    }

    public function download(Template $template)
    {
        return response()->download($template->getStoragePath(), $template->name.'.docx');
    }

    public function excel(Template $template)
    {
        return Excel::download(new TemplateExport($template), $template->name.'.xlsx');
    }

    public function fill($path)
    {
        $fields = Template::resolve($path);
        return view('document.template.fill', compact('fields', 'path'));
    }
}
