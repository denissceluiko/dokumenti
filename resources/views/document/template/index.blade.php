@extends('layout.app')

@section('title', 'Template list')

@section('content')
    <div>
        <a href="{{ route('template.create') }}">New template</a>
    </div>
    <table>
        <tr>
            <th>Name</th>
            <th>Created</th>
            <th>Updated</th>
        </tr>
        @foreach($templates as $template)
            <tr>
                <td><a href="{{ route('template.show', $template) }}">{{ $template->name }}</a></td>
                <td>{{ $template->created_at->format('d.m.Y. H:i') }}</td>
                <td>{{ $template->updated_at->format('d.m.Y. H:i') }}</td>
            </tr>
        @endforeach
    </table>
@endsection