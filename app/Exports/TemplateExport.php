<?php

namespace App\Exports;

use App\Models\Document\Template;
use Maatwebsite\Excel\Concerns\FromCollection;

class TemplateExport implements FromCollection
{
    protected $template;

    public function __construct(Template $template)
    {
        $this->template = $template;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return collect([$this->template->bindings['bindings']]);
    }
}
