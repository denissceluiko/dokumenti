@extends('layout.app')

@section('title', 'Template list')

@section('content')
    <table>
        <tr>
            <th>Name</th>
            <th>Created</th>
        </tr>
        @foreach($templates as $template)
            <tr>
                <td><a href="{{ route('template.show', $template) }}">{{ $template->name }}</a></td>
                <td>{{ $template->created_at->format('d.m.Y. H:i') }}</td>
            </tr>
        @endforeach
    </table>
@endsection