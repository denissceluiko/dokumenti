@extends('layout.app')

@section('title', $template->name.' edit')

@section('content')
{{ Form::model($template, ['route' => ['template.update', $template], 'files' => true, 'method' => 'put']) }}
{{ Form::label('name', 'Name') }}
{{ Form::text('name') }}
{{ Form::label('naming', 'Naming') }}
{{ Form::text('naming') }}
{{ Form::file('template_file') }}
{{ Form::submit('Submit') }}
{{ Form::close() }}
@endsection