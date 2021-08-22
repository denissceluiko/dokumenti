@extends('layout.app')

@section('title', $document->template->name)

@section('content')
<h1><a href="{{ route('template.show', $document->template) }}">{{ $document->template->name }}</a>, seeded by <a href="{{ route('document.show', $document) }}">{{ $document->name }}</a></h1>
<h5>{{ $document->template->naming }}</h5>

{{ Form::open(['route' => ['template.compile', $document->template]]) }}
<table>
<tr>
    <td colspan="2"><b>Bindings</b></td>
</tr>
@foreach($document->template->bindings['bindings'] as $field)
<tr>
<td>{{ Form::label($field, $field) }}</td>
<td>{{ Form::text($field, $document->bindings['bindings'][$field]) }}</td>
</tr>
@endforeach
<tr>
    <td colspan="2"><b>Rows</b></td>
</tr>
@foreach($document->template->bindings['rows'] as $row => $fields)
    <tr>
        <td><i>{{ $row }}</i></td>
        <td>{{ Form::textarea($row, json_encode($document->bindings['rows'][$row], JSON_PRETTY_PRINT)) }}</td>
    </tr>
@endforeach
</table>
{{ Form::submit('Submit') }}
{{ Form::close() }}

@endsection