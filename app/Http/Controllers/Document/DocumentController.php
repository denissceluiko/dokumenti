<?php

namespace App\Http\Controllers\Document;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function index()
    {
        $documents = Document::with('template')->get();
        return view('document.document.index', compact('documents'));
    }

    public function show(Document $document)
    {
        return view('document.document.show', compact('document'));
    }

    public function clone(Document $document)
    {
        $document->load('template');
        return view('document.document.clone', compact('document'));
    }

    public function edit(Document $document)
    {
        return view('document.document.edit', compact('document'));
    }

    public function update(Request $request, Document $document)
    {
        $bindings = $document->template->getBindings($request->all());

        $updated = $document->update([
            'name' => $request->name,
            'bindings' => $bindings
        ]);

        if ($updated) {
            $document->regenerate();
        }

        return redirect()->route('document.show', $document);
    }

    public function destroy(Document $document)
    {
        $document->delete();
        return back();
    }

    public function download(Document $document)
    {
        return response()->download($document->getFullPath(), $document->name);
    }
}
