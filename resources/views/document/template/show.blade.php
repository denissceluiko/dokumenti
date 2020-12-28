<h1>{{ $template->name }}</h1>
{{ Form::open(['route' => ['template.compile', $template], 'files' => true]) }}
@foreach($template->bindings as $field)
<div>
{{ Form::label($field, $field) }}
{{ Form::text($field) }}
</div>
@endforeach
{{ Form::submit('Submit') }}
{{ Form::close() }}