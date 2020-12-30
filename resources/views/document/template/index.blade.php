@foreach($templates as $template)
<div><a href="{{ route('template.show', $template) }}">{{ $template->name }}</a></div>
@endforeach