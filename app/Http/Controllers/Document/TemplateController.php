<?php

namespace App\Http\Controllers\Document;

use App\Http\Controllers\Controller;
use App\Models\Document\Template;
use App\Rules\TemplateUnique;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function create()
    {
        return view('document.template.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:templates,name',
            'template_file' => ['required', 'file', new TemplateUnique],
        ]);

        $template = Template::createFromUpload($request);

        return redirect()->route('document.template.show', $template);
    }

    public function show(Template $template)
    {
        return view('document.template.show', compact('template'));
    }

    public function compile(Template $template, Request $request)
    {
        $this->validate($request, [

        ]);

        return response()->download($template->compile($request->all()), 'document.docx');
    }

    public function fill($path)
    {
        $fields = Template::resolve($path);
        return view('document.template.fill', compact('fields', 'path'));
    }
}
