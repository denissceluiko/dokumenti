<?php

namespace App\Imports;

use App\Jobs\CompileTemplateBatch;
use App\Models\Document\Template;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TemplateBatchImport implements ToCollection, WithHeadingRow, WithCalculatedFormulas
{
    protected $template;

    public function __construct(Template $template)
    {
        $this->template = $template;
    }

    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        $collection->each(function($row) {
            CompileTemplateBatch::dispatch($this->template, $row->toArray());
        });
    }
}
