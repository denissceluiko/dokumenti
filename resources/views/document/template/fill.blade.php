{{ Form::open(['route' => 'template.compile', 'files' => true]) }}
{{ Form::hidden('template_path', $path) }}
@foreach($fields as $field)
<div>
{{ Form::label($field, $field) }}
{{ Form::text($field) }}
</div>
@endforeach
{{ Form::submit('Submit') }}
{{ Form::close() }}