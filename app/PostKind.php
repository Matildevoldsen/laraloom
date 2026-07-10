<?php

namespace App;

enum PostKind: string
{
    case Note = 'note';
    case Article = 'article';
    case Package = 'package';
    case Project = 'project';
    case Video = 'video';
    case Podcast = 'podcast';
    case Event = 'event';
    case Social = 'social';
}
