<?php

namespace App\Filament\Resources\ArticleRevisions\Pages;

use App\Filament\Resources\ArticleRevisions\ArticleRevisionResource;
use Filament\Resources\Pages\ListRecords;

class ListArticleRevisions extends ListRecords
{
    protected static string $resource = ArticleRevisionResource::class;
}
