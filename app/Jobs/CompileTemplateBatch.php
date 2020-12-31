<?php

namespace App\Jobs;

use App\Models\Document\Template;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CompileTemplateBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $template;
    protected $row;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Template $template, array $row)
    {
        $this->template = $template;
        $this->row = $row;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->template->compile($this->row);
    }
}
