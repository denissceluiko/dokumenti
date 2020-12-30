@foreach($documents as $document)
<div><a href="{{ route('document.download', $document) }}">{{ $document->name }}</a></div>
@endforeach