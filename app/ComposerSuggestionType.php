<?php

namespace App;

enum ComposerSuggestionType: string
{
    case Hashtag = 'hashtag';
    case Mention = 'mention';
}
