@extends('layout.app')

@section('title', 'Document list')

@section('content')
    <table>
        <tr>
            <th>Name</th>
            <th>Download</th>
            <th>Template</th>
            <th>Created</th>
            <th>Delete</th>
        </tr>
        @foreach($documents as $document)
            <tr>
                <td><a href="{{ route('document.show', $document) }}">{{ $document->name }}</a></td>
                <td><a href="{{ route('document.download', [$document]) }}">download</a></td>
                <td><a href="{{ route('template.show', [$document->template]) }}">{{ $document->template->name }}</a></td>
                <td>{{ $document->created_at->format('d.m.Y. H:i') }}</td>
                <td>{{ Form::open(['route' => ['document.destroy', $document], 'method' => 'delete']) }}{{ Form::submit('Delete') }}{{ Form::close() }}</td>
            </tr>
        @endforeach
    </table>
@endsection