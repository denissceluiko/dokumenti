@extends('layout.app')

@section('title', $document->template->name)

@section('content')
<h1><a href="{{ route('document.show', $document) }}">{{ $document->name }}</a></h1>
<h4>Template: {{ $document->template->name }}</h4>
<h5>{{ $document->template->naming }}</h5>

{{ Form::open(['route' => ['document.update', $document], 'method' => 'patch']) }}
<table>
    <tr>
        <td>{{ Form::label('name', 'Filename') }}</td>
        <td>{{ Form::text('name', $document->name) }}</td>
    </tr>
<tr>
    <td colspan="2"><b>Bindings</b></td>
</tr>
@foreach($document->template->bindings['bindings'] as $field)
<tr>
<td>{{ Form::label($field, $field) }}</td>
<td>{{ Form::text($field, $document->bindings['bindings'][$field] ?? '') }}</td>
</tr>
@endforeach
<tr>
    <td colspan="2"><b>Rows</b></td>
</tr>
@foreach($document->template->bindings['rows'] as $row => $fields)
    <tr>
        <td><i>{{ $row }}</i></td>
        <td>{{ Form::textarea($row, json_encode($document->bindings['rows'][$row] ?? [array_combine($fields, $fields)], JSON_PRETTY_PRINT)) }}</td>
    </tr>
@endforeach
<tr>
    <td colspan="2"><b>Blocks</b></td>
</tr>
@foreach($document->template->bindings['blocks'] as $block => $values)
    <tr>
        <td><i>{{ $block }}</i></td>
        <td>{{ Form::textarea($block, json_encode($document->bindings['blocks'][$block] ?? [array_combine($values, $values)], JSON_PRETTY_PRINT)) }}</td>
    </tr>
@endforeach
</table>
{{ Form::submit('Update') }}
{{ Form::close() }}

@endsection