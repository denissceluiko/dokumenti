@extends('layout.app')

@section('title', $document->name)

@section('content')
    <h1>{{ $document->name }}</h1>
    <a href="{{ route('document.download', $document) }}">Download</a>
    <a href="{{ route('template.show', $document->template_id) }}">Template</a>
    <a href="{{ route('document.clone', [$document]) }}">Clone</a>
    <table>
    @foreach($document->bindings['bindings'] as $binding => $value)
        <tr>
            <td>{{ $binding }}</td>
            <td>{{ $value }}</td>
        </tr>
    @endforeach
    @foreach($document->bindings['rows'] as $row => $value)
        <tr>
            <td>{{ $row }}</td>
            <td><pre>{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre></td>
        </tr>
    @endforeach
    </table>
@endsection