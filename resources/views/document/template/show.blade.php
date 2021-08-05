<h1>{{ $template->name }}</h1>
<h5>{{ $template->naming }}</h5>
{{ Form::open(['route' => ['template.compile', $template]]) }}
<table>
@foreach($template->bindings as $field)
<tr>
<td>{{ Form::label($field, $field) }}</td>
<td>{{ Form::text($field) }}</td>
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