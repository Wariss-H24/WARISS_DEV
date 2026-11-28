<?php

namespace App\Console\Commands;

use App\Models\Blog\Post;
use App\Models\Lab\Experiment;
use App\Models\Pages\Page;
use App\Models\Portfolio\CaseStudy;
use App\Models\Portfolio\Project;
use App\Models\Services\Service;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Publie les contenus programmés dont la date de publication est atteinte (cycle éditorial commun).
 * Planifiée chaque minute. Un contenu programmé n'est de toute façon jamais visible avant sa date :
 * la tâche aligne seulement son statut affiché dans l'administration.
 */
#[Signature('content:publish-scheduled')]
#[Description('Publie les contenus programmés dont la date de publication est atteinte')]
class PublishScheduledContent extends Command
{
    /**
     * Contenus soumis au cycle éditorial (trait HasPublicationStatus).
     *
     * @var list<class-string<Page|Project|CaseStudy|Service|Experiment|Post>>
     */
    private const CONTENTS = [Page::class, Project::class, CaseStudy::class, Service::class, Experiment::class, Post::class];

    public function handle(): int
    {
        $total = 0;

        foreach (self::CONTENTS as $contentClass) {
            $total += $contentClass::publishDueScheduled();
        }

        if ($total > 0) {
            $this->info("{$total} contenu(s) publié(s).");
        }

        return self::SUCCESS;
    }
}
