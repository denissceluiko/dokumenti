@extends('layout.app')

@section('title', $template->name)

@section('content')
<h1>{{ $template->name }}</h1>
<h5>{{ $template->naming }}</h5>
<div><a href="{{ route('template.edit', $template) }}">edit</a></div>
<div>
    {{ Form::open(['route' => ['template.resolve', $template]]) }}
    {{ Form::submit('Reresolve') }}
    {{ Form::close() }}
</div>
{{ Form::open(['route' => ['template.compile', $template]]) }}
<table>
<tr>
    <td colspan="2"><b>Bindings</b></td>
</tr>
@foreach($template->bindings['bindings'] as $field)
<tr>
<td>{{ Form::label($field, $field) }}</td>
<td>{{ Form::text($field) }}</td>
</tr>
@endforeach
<tr>
    <td colspan="2"><b>Rows</b></td>
</tr>
@foreach($template->bindings['rows'] as $row => $fields)
    <tr>
        <td><i>{{ $row }}</i></td>
        <td>{{ Form::textarea($row) }}</td>
    </tr>
@endforeach
</table>
{{ Form::submit('Submit') }}
{{ Form::close() }}
<a href="{{ route('template.download', $template) }}">Download template</a>
<a href="{{ route('template.excel', $template) }}">Download excel</a>
<h1>Upload batch</h1>
{{ Form::open(['route' => ['template.batch', $template], 'files' => true]) }}
{{ Form::file('template_batch_import_file') }}
{{ Form::submit('Submit') }}
{{ Form::close() }}
@endsection