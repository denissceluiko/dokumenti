@foreach($documents as $document)
<div>
    <a href="{{ route('document.show', $document) }}">{{ $document->name }}</a>
    <a href="{{ route('document.download', [$document]) }}">download</a>
</div>
@endforeach