{{ Form::open(['route' => 'template.store', 'files' => true]) }}
{{ Form::label('name', 'Name') }}
{{ Form::text('name') }}
{{ Form::label('naming', 'Naming') }}
{{ Form::text('naming') }}
{{ Form::file('template_file') }}
{{ Form::submit('Submit') }}
{{ Form::close() }}