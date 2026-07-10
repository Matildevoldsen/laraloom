<?php

namespace App;

enum SourceMethod: string
{
    case Rss = 'rss';
    case Api = 'api';
    case Oembed = 'oembed';
    case TavilyMetadata = 'tavily_metadata';
    case Community = 'community';
}
