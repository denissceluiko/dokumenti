<?php

namespace App\Http\Controllers\Document;

use App\Http\Controllers\Controller;
use App\Models\Document;

class DocumentController extends Controller
{
    public function index()
    {
        $documents = Document::all();
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
