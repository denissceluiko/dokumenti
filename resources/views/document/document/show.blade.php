@extends('layout.app')

@section('title', $document->name)

@section('content')
<h1>{{ $document->name }}</h1>
<a href="{{ route('document.download', $document) }}">Download</a>
@endsection